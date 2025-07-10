<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreMutationRequest;
use App\Http\Requests\UpdateMutationRequest;
use App\Repositories\Interfaces\MutationRepositoryInterface;
use App\Repositories\Interfaces\ProductLocationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MutationController extends BaseApiController
{
    private MutationRepositoryInterface $mutationRepository;
    private ProductLocationRepositoryInterface $stockRepository;

    public function __construct(
        MutationRepositoryInterface $mutationRepository,
        ProductLocationRepositoryInterface $stockRepository
    ) {
        $this->mutationRepository = $mutationRepository;
        $this->stockRepository = $stockRepository;
    }

    /**
     * Display a listing of mutations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'start_date' => $request->get('start_date'),
                'end_date' => $request->get('end_date'),
                'product_id' => $request->get('product_id'),
                'location_id' => $request->get('location_id'),
            ];

            $type = $request->get('type');
            if ($type && in_array($type, ['masuk', 'keluar'])) {
                $mutations = $this->mutationRepository->getByType($type, array_filter($filters));
            } else {
                $perPage = $request->integer('per_page', 15);
                $mutations = $this->mutationRepository->getPaginated($perPage, ['productLocation.product', 'productLocation.location']);
            }

            return $this->successResponse($mutations, 'Mutations retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve mutations', 500);
        }
    }

    /**
     * Store a newly created mutation
     *
     * @param StoreMutationRequest $request
     * @return JsonResponse
     */
    public function store(StoreMutationRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $user = $request->user();

            $validated = $request->validated();
            $validated = [...$validated, 'user_id' => $user->id];

            $mutation = $this->mutationRepository->create($validated);
            $productLocation = $this->stockRepository->findById($validated['product_location_id']);

            if (!$productLocation) {
                DB::rollBack();
                return $this->notFoundResponse('Product location not found');
            }

            $currentStock = $productLocation->stok;
            $newStock = $validated['jenis_mutasi'] === 'masuk'
                ? $currentStock + $validated['jumlah']
                : $currentStock - $validated['jumlah'];

            if ($validated['jenis_mutasi'] === 'keluar' && $newStock < 0) {
                DB::rollBack();
                return $this->errorResponse('Insufficient stock for this operation', 400);
            }

            $this->stockRepository->update($validated['product_location_id'], ['stok' => $newStock]);

            DB::commit();

            $mutation->load(['productLocation.product', 'productLocation.location']);
            return $this->createdResponse($mutation, 'Mutation created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create mutation', 500);
        }
    }

    /**
     * Display the specified mutation
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $mutation = $this->mutationRepository->findById($id, ['productLocation.product', 'productLocation.location']);

            if (!$mutation) {
                return $this->notFoundResponse('Mutation not found');
            }

            return $this->successResponse($mutation, 'Mutation retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve mutation', 500);
        }
    }

    /**
     * Update the specified mutation
     *
     * @param UpdateMutationRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateMutationRequest $request, int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $mutation = $this->mutationRepository->findById($id, ['productLocation']);

            if (!$mutation) {
                DB::rollBack();
                return $this->notFoundResponse('Mutation not found');
            }

            $validated = $request->validated();

            $oldAmount = $mutation->jumlah;
            $oldType = $mutation->jenis_mutasi;
            $newAmount = $validated['jumlah'] ?? $oldAmount;
            $newType = $validated['jenis_mutasi'] ?? $oldType;

            $currentStock = $mutation->productLocation->stok;

            $adjustedStock = $oldType === 'masuk'
                ? $currentStock - $oldAmount
                : $currentStock + $oldAmount;

            $finalStock = $newType === 'masuk'
                ? $adjustedStock + $newAmount
                : $adjustedStock - $newAmount;

            if ($finalStock < 0) {
                DB::rollBack();
                return $this->errorResponse('Operation would result in negative stock', 400);
            }
            $mutation = $this->mutationRepository->update($id, $validated);
            $this->stockRepository->update($mutation->product_location_id, ['stok' => $finalStock]);

            DB::commit();

            $mutation->load(['productLocation.product', 'productLocation.location']);
            return $this->successResponse($mutation, 'Mutation updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to update mutation', 500);
        }
    }

    /**
     * Remove the specified mutation
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        DB::beginTransaction();

        try {
            $mutation = $this->mutationRepository->findById($id, ['productLocation']);

            if (!$mutation) {
                DB::rollBack();
                return $this->notFoundResponse('Mutation not found');
            }

            $currentStock = $mutation->productLocation->stok;
            $adjustedStock = $mutation->jenis_mutasi === 'masuk'
                ? $currentStock - $mutation->jumlah
                : $currentStock + $mutation->jumlah;

            if ($adjustedStock < 0) {
                DB::rollBack();
                return $this->errorResponse('Cannot delete mutation: would result in negative stock', 400);
            }

            $this->stockRepository->update($mutation->product_location_id, ['stok' => $adjustedStock]);
            $this->mutationRepository->delete($id);

            DB::commit();

            return $this->noContentResponse('Mutation deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to delete mutation', 500);
        }
    }

    /**
     * Get mutations by user
     *
     * @param Request $request
     * @param int $userId
     * @return JsonResponse
     */
    public function getByUser(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = $request->integer('per_page', 15);
            $mutations = $this->mutationRepository->getByUser($user->id, $perPage);

            $metadata = [
                'total_mutations' => $mutations->total(),
                'current_page' => $mutations->currentPage(),
                'last_page' => $mutations->lastPage(),
                'next_page_url' => $mutations->nextPageUrl(),
                'prev_page_url' => $mutations->previousPageUrl(),
                'per_page' => $mutations->perPage(),
            ];

            return $this->successResponse($mutations->items(), 'User mutations retrieved successfully', metadata: $metadata);
        } catch (\Exception $e) {
            Log::debug('Error retrieving user mutations', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);
            return $this->errorResponse('Failed to retrieve user mutations', 500);
        }
    }

    /**
     * Get mutations by product
     *
     * @param Request $request
     * @param int $productId
     * @return JsonResponse
     */
    public function getByProduct(Request $request, string $productId): JsonResponse
    {
        try {
            $perPage = $request->integer('per_page', 15);
            $mutations = $this->mutationRepository->getByProduct($productId, $perPage);

            $metadata = [
                'product_id' => $productId,
                'total_mutations' => $mutations->total(),
                'current_page' => $mutations->currentPage(),
                'last_page' => $mutations->lastPage(),
                'next_page_url' => $mutations->nextPageUrl(),
                'prev_page_url' => $mutations->previousPageUrl(),
                'per_page' => $mutations->perPage(),
            ];

            return $this->successResponse($mutations->items(), 'Product mutations retrieved successfully', metadata: $metadata);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve product mutations', 500);
        }
    }
}
