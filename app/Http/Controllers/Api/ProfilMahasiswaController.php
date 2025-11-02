<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfilMahasiswaRequest;
use App\Http\Resources\ProfilMahasiswaResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilMahasiswaController extends Controller
{
    /**
     * GET /api/mahasiswa/profil
     * Return the authenticated mahasiswa profile
     */
    public function show(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => new ProfilMahasiswaResource($user),
        ]);
    }

    /**
     * PUT /api/mahasiswa/profil/{id}
     * Update profile (multipart/form-data for foto)
     */
    public function update(UpdateProfilMahasiswaRequest $request, $id)
    {
        $user = $request->user();

        // only allow the authenticated mahasiswa to update their own profile
        if (! $user || (int) $user->id !== (int) $id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        $user->nama = $validated['nama'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];

        if ($request->hasFile('foto')) {
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $user->foto = $request->file('foto')->store('foto_mahasiswa', 'public');
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => new ProfilMahasiswaResource($user),
        ]);
    }
}