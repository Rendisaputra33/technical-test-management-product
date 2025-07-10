<?php

namespace App\Repositories;

use App\Models\Mutation;
use App\Repositories\Interfaces\MutationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MutationRepository extends BaseRepository implements MutationRepositoryInterface
{
    public function __construct(Mutation $model)
    {
        parent::__construct($model);
    }

    public function getByType(string $type, array $filters = [])
    {
        $query = $this->model->where('jenis_mutasi', $type)
            ->with(['productLocation.product', 'productLocation.location']);

        if (isset($filters['start_date'])) {
            $query->where('tanggal', '>=', $filters['start_date']);
        }

        if (isset($filters['end_date'])) {
            $query->where('tanggal', '<=', $filters['end_date']);
        }

        if (isset($filters['product_id'])) {
            $query->whereHas('productLocation', function ($q) use ($filters) {
                $q->where('product_id', $filters['product_id']);
            });
        }

        if (isset($filters['location_id'])) {
            $query->whereHas('productLocation', function ($q) use ($filters) {
                $q->where('location_id', $filters['location_id']);
            });
        }

        return $query->orderBy('tanggal', 'desc')->get();
    }

    public function getByProductLocation(int $productLocationId)
    {
        return $this->model->where('product_location_id', $productLocationId)
            ->with(['productLocation.product', 'productLocation.location'])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate)
    {
        return $this->model->whereBetween('tanggal', [$startDate, $endDate])
            ->with(['productLocation.product', 'productLocation.location'])
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function getStockReport(array $filters = [])
    {
        $query = DB::table('product_locations as pl')
            ->join('products as p', 'pl.product_id', '=', 'p.id')
            ->join('locations as l', 'pl.location_id', '=', 'l.id')
            ->leftJoin('categories as c', 'p.kategori', '=', 'c.id')
            ->select([
                'p.id as product_id',
                'p.kode_produk',
                'p.nama_produk',
                'c.nama_kategori',
                'l.id as location_id',
                'l.kode_lokasi',
                'l.nama_lokasi',
                'pl.stok',
                DB::raw('COALESCE(SUM(CASE WHEN m.jenis_mutasi = "masuk" THEN m.jumlah ELSE 0 END), 0) as total_masuk'),
                DB::raw('COALESCE(SUM(CASE WHEN m.jenis_mutasi = "keluar" THEN m.jumlah ELSE 0 END), 0) as total_keluar')
            ])
            ->leftJoin('mutations as m', 'pl.id', '=', 'm.product_location_id');

        if (isset($filters['product_id'])) {
            $query->where('p.id', $filters['product_id']);
        }

        if (isset($filters['location_id'])) {
            $query->where('l.id', $filters['location_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('c.id', $filters['category_id']);
        }

        if (isset($filters['start_date']) && isset($filters['end_date'])) {
            $query->whereBetween('m.tanggal', [$filters['start_date'], $filters['end_date']]);
        }

        return $query->groupBy([
            'p.id',
            'p.kode_produk',
            'p.nama_produk',
            'c.nama_kategori',
            'l.id',
            'l.kode_lokasi',
            'l.nama_lokasi',
            'pl.stok'
        ])->get();
    }

    public function getByUser(int $userId, int $perPage = 15)
    {
        return $this->model->where('user_id', $userId)
            ->with(['productLocation:id,stok,product_id,location_id', 'productLocation.product:id,nama_produk,satuan,deskripsi', 'productLocation.location:id,nama_lokasi,kode_lokasi'])
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage);
    }

    public function getByProduct(string $productId, int $perPage = 15)
    {
        return $this->model->whereHas('productLocation', function ($query) use ($productId) {
            $query->where('product_id', $productId);
        })
            ->with(['productLocation:id,stok,product_id,location_id', 'productLocation.product:id,nama_produk,satuan,deskripsi', 'productLocation.location:id,nama_lokasi,kode_lokasi'])
            ->orderBy('tanggal', 'desc')
            ->paginate($perPage);
    }
}
