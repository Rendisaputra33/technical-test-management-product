<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreStockRequest;
use App\Http\Requests\UpdateStockRequest;
use App\Repositories\Interfaces\ProductLocationRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockController extends BaseApiController
{
    private ProductLocationRepositoryInterface $stockRepository;

    public function __construct(ProductLocationRepositoryInterface $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }

    /**
     * Display a listing of stock
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->integer('per_page', 15);
            $stocks = $this->stockRepository->getPaginated($perPage, ['product', 'location']);

            $metadata = [
                'total_stocks' => $stocks->total(),
                'current_page' => $stocks->currentPage(),
                'last_page' => $stocks->lastPage(),
                'next_page_url' => $stocks->nextPageUrl(),
                'prev_page_url' => $stocks->previousPageUrl(),
                'per_page' => $stocks->perPage(),
            ];

            return $this->successResponse($stocks->items(), 'Stock data retrieved successfully', metadata: $metadata ?? null);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve stock data', 500);
        }
    }

    /**
     * Store or update stock
     *
     * @param StoreStockRequest $request
     * @return JsonResponse
     */
    public function store(StoreStockRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $stock = $this->stockRepository->updateStock(
                $validated['product_id'],
                $validated['location_id'],
                $validated['stok']
            );

            return $this->createdResponse($stock, 'Stock updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update stock', 500);
        }
    }

    /**
     * Display the specified stock
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $stock = $this->stockRepository->findById($id, ['product', 'location']);

            if (!$stock) {
                return $this->notFoundResponse('Stock data not found');
            }

            return $this->successResponse($stock, 'Stock data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve stock data', 500);
        }
    }

    /**
     * Update the specified stock
     *
     * @param UpdateStockRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateStockRequest $request, int $id): JsonResponse
    {
        try {
            $stock = $this->stockRepository->update($id, $request->validated());

            if (!$stock) {
                return $this->notFoundResponse('Stock data not found');
            }

            return $this->successResponse($stock, 'Stock updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update stock', 500);
        }
    }

    /**
     * Remove the specified stock
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->stockRepository->delete($id);

            if (!$deleted) {
                return $this->notFoundResponse('Stock data not found');
            }

            return $this->noContentResponse('Stock data deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete stock data', 500);
        }
    }

    /**
     * Get stock for a specific product
     *
     * @param string $productId
     * @return JsonResponse
     */
    public function getProductStock(string $productId): JsonResponse
    {
        try {
            $stocks = $this->stockRepository->getProductStock($productId);
            return $this->successResponse($stocks, 'Product stock retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve product stock', 500);
        }
    }

    /**
     * Get stock for a specific location
     *
     * @param string $locationId
     * @return JsonResponse
     */
    public function getLocationStock(string $locationId): JsonResponse
    {
        try {
            $stocks = $this->stockRepository->getLocationStock($locationId);
            return $this->successResponse($stocks, 'Location stock retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve location stock', 500);
        }
    }

    /**
     * Get stock by product and location
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getStock(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'location_id' => 'required|exists:locations,id',
            ]);

            $stock = $this->stockRepository->getStock(
                $request->get('product_id'),
                $request->get('location_id')
            );

            if (!$stock) {
                return $this->notFoundResponse('Stock data not found');
            }

            return $this->successResponse($stock, 'Stock retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve stock', 500);
        }
    }
}
