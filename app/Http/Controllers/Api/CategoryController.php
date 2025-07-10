<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    private CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Display a listing of categories
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->integer('per_page', 15);
            $categories = $this->categoryRepository->getPaginated($perPage);

            $metadata = [
                'total_categories' => $categories->total(),
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'next_page_url' => $categories->nextPageUrl(),
                'prev_page_url' => $categories->previousPageUrl(),
                'per_page' => $categories->perPage(),
            ];

            return $this->successResponse($categories->items(), 'Categories retrieved successfully', metadata: $metadata ?? null);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve categories', 500);
        }
    }

    /**
     * Store a newly created category
     *
     * @param StoreCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->categoryRepository->create($request->validated());
            return $this->createdResponse($category, 'Category created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create category', 500);
        }
    }

    /**
     * Display the specified category
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->findById($id, ['products']);

            if (!$category) {
                return $this->notFoundResponse('Category not found');
            }

            return $this->successResponse($category, 'Category retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve category', 500);
        }
    }

    /**
     * Update the specified category
     *
     * @param UpdateCategoryRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateCategoryRequest $request, string $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->update($id, $request->validated());

            if (!$category) {
                return $this->notFoundResponse('Category not found');
            }

            return $this->successResponse($category, 'Category updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update category', 500);
        }
    }

    /**
     * Remove the specified category
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->categoryRepository->delete($id);

            if (!$deleted) {
                return $this->notFoundResponse('Category not found');
            }

            return $this->noContentResponse('Category deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete category', 500);
        }
    }
}
