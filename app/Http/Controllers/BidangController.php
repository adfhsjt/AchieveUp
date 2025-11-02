<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bidang;
use Illuminate\Http\Request;
use App\Http\Resources\BidangResource;
use App\Http\Requests\StoreBidangRequest;
use App\Http\Requests\UpdateBidangRequest;

class BidangController extends Controller
{
    // GET /api/admin/bidang
    public function index(Request $request)
    {
        $bidangs = Bidang::latest()->get();
        return response()->json([
            'success' => true,
            'data' => BidangResource::collection($bidangs),
        ]);
    }

    // GET /api/admin/bidang/{id}
    public function show($id)
    {
        $bidang = Bidang::find($id);
        if (! $bidang) {
            return response()->json([
                'success' => false,
                'message' => 'Bidang tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new BidangResource($bidang),
        ]);
    }

    // GET /api/admin/bidang/getall  (mirip web getall)
    public function getall(Request $request)
    {
        $bidangs = Bidang::all();
        $data = $bidangs->map(function ($bidang) {
            return [
                'id' => $bidang->id,
                'kode' => $bidang->kode,
                'nama' => $bidang->nama,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    // POST /api/admin/bidang
    public function store(StoreBidangRequest $request)
    {
        $validated = $request->validated();

        try {
            $bidang = Bidang::create($validated);
            return response()->json([
                'success' => true,
                'message' => 'Bidang berhasil ditambahkan.',
                'data' => new BidangResource($bidang),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan Bidang: ' . $e->getMessage(),
            ], 500);
        }
    }

    // PUT /api/admin/bidang/{id}
    public function update(UpdateBidangRequest $request, $id)
    {
        $bidang = Bidang::find($id);
        if (! $bidang) {
            return response()->json([
                'success' => false,
                'message' => 'Bidang tidak ditemukan.'
            ], 404);
        }

        $validated = $request->validated();

        try {
            $bidang->update($validated);
            return response()->json([
                'success' => true,
                'message' => 'Bidang berhasil diperbarui.',
                'data' => new BidangResource($bidang),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui Bidang: ' . $e->getMessage(),
            ], 500);
        }
    }

    // DELETE /api/admin/bidang/{id}
    public function destroy($id)
    {
        try {
            $bidang = Bidang::findOrFail($id);

            if ($bidang->lomba()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bidang tidak dapat dihapus karena memiliki data lomba.'
                ], 400);
            }

            $bidang->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bidang berhasil dihapus.'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bidang tidak ditemukan.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Bidang: ' . $e->getMessage()
            ], 500);
        }
    }
}