<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    private ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'category_id' => $request->get('category_id'),
                'search' => $request->get('search'),
            ];

            $withStock = $request->boolean('with_stock', false);

            if ($withStock) {
                $products = $this->productRepository->getProductsWithStock(array_filter($filters));
            } else {
                $perPage = $request->integer('per_page', 15);
                $products = $this->productRepository->getPaginated($perPage, ['category']);

                $metadata = [
                    'total_products' => $products->total(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'next_page_url' => $products->nextPageUrl(),
                    'prev_page_url' => $products->previousPageUrl(),
                    'per_page' => $products->perPage(),
                ];
            }

            return $this->successResponse($products->items(), 'Products retrieved successfully', metadata: $metadata ?? null);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve products', 500);
        }
    }

    /**
     * Store a newly created product
     *
     * @param StoreProductRequest $request
     * @return JsonResponse
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productRepository->create($request->validated());
            $product->load(['category']);

            return $this->createdResponse($product, 'Product created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create product', 500);
        }
    }

    /**
     * Display the specified product
     *
     * @param string $id
     * @return JsonResponse
     */
    public function show(string $id): JsonResponse
    {
        try {
            $product = $this->productRepository->findById($id, ['category', 'productLocations.location']);

            if (!$product) {
                return $this->notFoundResponse('Product not found');
            }

            return $this->successResponse($product, 'Product retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve product', 500);
        }
    }

    /**
     * Update the specified product
     *
     * @param UpdateProductRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->productRepository->update($id, $request->validated());

            if (!$product) {
                return $this->notFoundResponse('Product not found');
            }

            $product->load(['category']);
            return $this->successResponse($product, 'Product updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update product', 500);
        }
    }

    /**
     * Remove the specified product
     *
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->productRepository->delete($id);

            if (!$deleted) {
                return $this->notFoundResponse('Product not found');
            }

            return $this->noContentResponse('Product deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete product', 500);
        }
    }

    /**
     * Search products
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $search = $request->get('q');

            if (!$search) {
                return $this->errorResponse('Search query is required', 400);
            }

            $products = $this->productRepository->searchProducts($search);
            return $this->successResponse($products, 'Products found');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to search products', 500);
        }
    }

    /**
     * Get products by category
     *
     * @param int $categoryId
     * @return JsonResponse
     */
    public function getByCategory(int $categoryId): JsonResponse
    {
        try {
            $products = $this->productRepository->getByCategory($categoryId);
            return $this->successResponse($products, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve products by category', 500);
        }
    }
}
