<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePeriodeRequest;
use App\Http\Requests\UpdatePeriodeRequest;
use App\Http\Resources\PeriodeResource;
use App\Models\Periode;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    // GET /api/admin/periode
    public function index(Request $request)
    {
        $periodes = Periode::orderBy('id', 'asc')->get();
        return response()->json([
            'success' => true,
            'data' => PeriodeResource::collection($periodes),
        ]);
    }

    // GET /api/admin/periode/getall
    public function getall(Request $request)
    {
        $periodes = Periode::all()->map(function ($periode) {
            return [
                'id' => $periode->id,
                'kode' => $periode->kode,
                'nama' => $periode->nama,
                'is_active' => (bool) $periode->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $periodes,
        ]);
    }

    // POST /api/admin/periode
    public function store(StorePeriodeRequest $request)
    {
        $validated = $request->validated();

        // Nonaktifkan semua periode aktif sebelumnya
        Periode::where('is_active', true)->update(['is_active' => false]);

        $periode = Periode::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Periode berhasil ditambahkan.',
            'data' => new PeriodeResource($periode),
        ], 201);
    }

    // GET /api/admin/periode/{id}
    public function show($id)
    {
        $periode = Periode::find($id);
        if (! $periode) {
            return response()->json(['success' => false, 'message' => 'Periode tidak ditemukan.'], 404);
        }

        return response()->json(['success' => true, 'data' => new PeriodeResource($periode)]);
    }

    // PUT /api/admin/periode/{id}
    public function update(UpdatePeriodeRequest $request, $id)
    {
        $periode = Periode::find($id);
        if (! $periode) {
            return response()->json(['success' => false, 'message' => 'Periode tidak ditemukan.'], 404);
        }

        $validated = $request->validated();

        $periode->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Periode berhasil diperbarui.',
            'data' => new PeriodeResource($periode),
        ]);
    }

    // DELETE /api/admin/periode/{id}
    public function destroy($id)
    {
        try {
            $periode = Periode::findOrFail($id);
            $periode->delete();

            return response()->json([
                'success' => true,
                'message' => 'Periode berhasil dihapus.',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Periode tidak ditemukan.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus Periode: ' . $e->getMessage()], 500);
        }
    }

    // PUT /api/admin/periode/{id}/activate
    public function activate($id)
    {
        // Nonaktifkan semua periode
        Periode::query()->update(['is_active' => false]);

        $periode = Periode::find($id);
        if (! $periode) {
            return response()->json(['success' => false, 'message' => 'Periode tidak ditemukan.'], 404);
        }

        $periode->is_active = true;
        $periode->save();

        return response()->json(['success' => true, 'message' => 'Periode berhasil diaktifkan.', 'data' => new PeriodeResource($periode)]);
    }
}