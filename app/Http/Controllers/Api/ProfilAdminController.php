<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfilAdminResource;
use App\Http\Requests\UpdateProfilAdminRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilAdminController extends Controller
{
    /**
     * GET /api/admin/profil
     * return authenticated admin profile
     */
    public function show(Request $request)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => new ProfilAdminResource($user),
        ]);
    }

    /**
     * PUT /api/admin/profil/{id}
     * update profile (multipart/form-data for foto)
     */
    public function update(UpdateProfilAdminRequest $request, $id)
    {
        $user = $request->user();

        // authorize: ensure user exists and is updating own profile (or is admin)
        if (! $user || (int) $user->id !== (int) $id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $validated = $request->validated();

        $user->nama = $validated['nama'];
        $user->username = $validated['username'];
        $user->email = $validated['email'];

        if ($request->hasFile('foto')) {
            // delete old foto if exists
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $user->foto = $request->file('foto')->store('foto_admin', 'public');
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data' => new ProfilAdminResource($user),
        ]);
    }
}