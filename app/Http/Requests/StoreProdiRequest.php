<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdiRequest;
use App\Http\Requests\UpdateProdiRequest;
use App\Http\Resources\ProdiResource;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;

class ProdiController extends Controller
{
    // GET /api/admin/prodi
    public function index(Request $request)
    {
        $prodis = ProgramStudi::orderBy('id', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => ProdiResource::collection($prodis),
        ]);
    }

    // GET /api/admin/prodi/getall (compatibility with previous getall)
    public function getall(Request $request)
    {
        $prodis = ProgramStudi::all()->map(function ($prodi) {
            return [
                'id' => $prodi->id,
                'kode' => $prodi->kode,
                'nama' => $prodi->nama,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $prodis,
        ]);
    }

    // POST /api/admin/prodi
    public function store(StoreProdiRequest $request)
    {
        $validated = $request->validated();

        $prodi = ProgramStudi::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Program Studi berhasil ditambahkan.',
            'data' => new ProdiResource($prodi),
        ], 201);
    }

    // GET /api/admin/prodi/{id}
    public function show($id)
    {
        $prodi = ProgramStudi::with('mahasiswa')->find($id);
        if (! $prodi) {
            return response()->json(['success' => false, 'message' => 'Program Studi tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => new ProdiResource($prodi)]);
    }

    // PUT /api/admin/prodi/{id}
    public function update(UpdateProdiRequest $request, $id)
    {
        $prodi = ProgramStudi::find($id);
        if (! $prodi) {
            return response()->json(['success' => false, 'message' => 'Program Studi tidak ditemukan.'], 404);
        }

        $validated = $request->validated();

        $prodi->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Program Studi berhasil diperbarui.',
            'data' => new ProdiResource($prodi),
        ]);
    }

    // DELETE /api/admin/prodi/{id}
    public function destroy($id)
    {
        try {
            $prodi = ProgramStudi::findOrFail($id);

            if ($prodi->mahasiswa()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Program Studi tidak dapat dihapus karena memiliki data mahasiswa.',
                ], 400);
            }

            $prodi->delete();

            return response()->json([
                'success' => true,
                'message' => 'Program Studi berhasil dihapus.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Program Studi tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Program Studi: ' . $e->getMessage(),
            ], 500);
        }
    }
}