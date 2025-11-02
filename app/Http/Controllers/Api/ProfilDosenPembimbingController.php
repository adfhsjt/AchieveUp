<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfilDosenPembimbingRequest;
use App\Http\Resources\ProfilDosenPembimbingResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilDosenPembimbingController extends Controller
{
    /**
     * GET /api/dosen_pembimbing/profil
     * Return auth dosen pembimbing profile
     */
    public function show(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => new ProfilDosenPembimbingResource($user),
        ]);
    }

    /**
     * PUT /api/dosen_pembimbing/profil/{id}
     * Update profile (multipart/form-data for foto)
     */
    public function update(UpdateProfilDosenPembimbingRequest $request, $id)
    {
        $user = $request->user();

        // ensure the authenticated dosen updates own profile (or adjust if admin can edit)
        if (! $user || (int) $user->id !== (int) $id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        $user->nama = $validated['nama'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];

        if ($request->hasFile('foto')) {
            // delete previous foto from public disk
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $user->foto = $request->file('foto')->store('foto_dosen_pembimbing', 'public');
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => new ProfilDosenPembimbingResource($user),
        ]);
    }
}