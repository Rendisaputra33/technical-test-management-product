<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Repositories\Interfaces\LocationRepositoryInterface;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log as FacadesLog;

class LocationController extends BaseApiController
{
    private LocationRepositoryInterface $locationRepository;

    public function __construct(LocationRepositoryInterface $locationRepository)
    {
        $this->locationRepository = $locationRepository;
    }

    /**
     * Display a listing of locations
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {

            $perPage = $request->integer('per_page', 15);
            $locations = $this->locationRepository->getPaginated($perPage, ['productLocations.product']);

            $metadata = [
                'total_locations' => $locations->total(),
                'current_page' => $locations->currentPage(),
                'last_page' => $locations->lastPage(),
                'next_page_url' => $locations->nextPageUrl(),
                'prev_page_url' => $locations->previousPageUrl(),
                'per_page' => $locations->perPage(),
            ];

            return $this->successResponse($locations->items(), 'Locations retrieved successfully', metadata: $metadata ?? null);
        } catch (\Exception $e) {
            FacadesLog::debug('Failed to retrieve locations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->errorResponse('Failed to retrieve locations', 500);
        }
    }

    /**
     * Store a newly created location
     *
     * @param StoreLocationRequest $request
     * @return JsonResponse
     */
    public function store(StoreLocationRequest $request): JsonResponse
    {
        try {
            $location = $this->locationRepository->create($request->validated());
            return $this->createdResponse($location, 'Location created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create location', 500);
        }
    }

    /**
     * Display the specified location
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $location = $this->locationRepository->findById($id, ['productLocations.product']);

            if (!$location) {
                return $this->notFoundResponse('Location not found');
            }

            return $this->successResponse($location, 'Location retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve location', 500);
        }
    }

    /**
     * Update the specified location
     *
     * @param UpdateLocationRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateLocationRequest $request, string $id): JsonResponse
    {
        try {
            $location = $this->locationRepository->update($id, $request->validated());

            if (!$location) {
                return $this->notFoundResponse('Location not found');
            }

            return $this->successResponse($location, 'Location updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update location', 500);
        }
    }

    /**
     * Remove the specified location
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->locationRepository->delete($id);

            if (!$deleted) {
                return $this->notFoundResponse('Location not found');
            }

            return $this->noContentResponse('Location deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete location', 500);
        }
    }
}
