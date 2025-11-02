<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMahasiswaRequest;
use App\Http\Requests\UpdateMahasiswaRequest;
use App\Http\Requests\StoreDosenRequest;
use App\Http\Requests\UpdateDosenRequest;
use App\Http\Resources\MahasiswaAdminResource;
use App\Http\Resources\DosenAdminResource;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // GET /api/admin/users?type=mahasiswa|dosen
    public function index(Request $request)
    {
        $type = $request->query('type');

        if ($type === 'mahasiswa') {
            $mahasiswas = Mahasiswa::with('programStudi')->orderBy('id','asc')->get();
            return response()->json(['success' => true, 'data' => MahasiswaAdminResource::collection($mahasiswas)]);
        }

        if ($type === 'dosen') {
            $dosens = Dosen::orderBy('id','asc')->get();
            return response()->json(['success' => true, 'data' => DosenAdminResource::collection($dosens)]);
        }

        // summary / meta
        $countMahasiswa = Mahasiswa::count();
        $countDosen = Dosen::count();

        return response()->json([
            'success' => true,
            'data' => [
                'counts' => [
                    'mahasiswa' => $countMahasiswa,
                    'dosen' => $countDosen,
                ]
            ],
        ]);
    }

    // GET /api/admin/users/mahasiswa
    public function getMahasiswaData()
    {
        $mahasiswas = Mahasiswa::with('programStudi')->get();
        return response()->json(['success' => true, 'data' => MahasiswaAdminResource::collection($mahasiswas)]);
    }

    // GET /api/admin/users/dosen
    public function getDosenData()
    {
        $dosens = Dosen::all();
        return response()->json(['success' => true, 'data' => DosenAdminResource::collection($dosens)]);
    }

    // POST /api/admin/users (body must include 'type' => 'mahasiswa'|'dosen')
    public function store(Request $request)
    {
        $type = $request->input('type');

        if ($type === 'mahasiswa') {
            $validated = app(StoreMahasiswaRequest::class)->validateResolved() ?? $request->validate([]);
            // but better: use the FormRequest
            $req = app(StoreMahasiswaRequest::class);
            $validated = $req->validated();

            $fotoPath = null;
            if ($req->hasFile('foto')) {
                $fotoPath = $req->file('foto')->store('foto_mahasiswa', 'public');
            }

            $mahasiswa = Mahasiswa::create([
                'nim' => $validated['nim'],
                'nama' => $validated['nama'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'program_studi_id' => $validated['program_studi_id'],
                'foto' => $fotoPath,
            ]);

            return response()->json(['success' => true, 'message' => 'Mahasiswa berhasil dibuat.', 'data' => new MahasiswaAdminResource($mahasiswa)], 201);
        }

        if ($type === 'dosen') {
            $req = app(StoreDosenRequest::class);
            $validated = $req->validated();

            $fotoPath = null;
            if ($req->hasFile('foto')) {
                $fotoPath = $req->file('foto')->store('foto_dosen', 'public');
            }

            $dosen = Dosen::create([
                'nidn' => $validated['nidn'],
                'username' => $validated['username'],
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'foto' => $fotoPath,
                'role' => $validated['role'],
            ]);

            return response()->json(['success' => true, 'message' => 'Dosen berhasil dibuat.', 'data' => new DosenAdminResource($dosen)], 201);
        }

        return response()->json(['success' => false, 'message' => 'Tipe user tidak valid.'], 400);
    }

    // PUT /api/admin/users/mahasiswa/{id}
    public function updateMahasiswa(UpdateMahasiswaRequest $request, $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            if ($mahasiswa->foto) {
                Storage::disk('public')->delete($mahasiswa->foto);
            }
            $mahasiswa->foto = $request->file('foto')->store('foto_mahasiswa', 'public');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $mahasiswa->update($validated);

        return response()->json(['success' => true, 'message' => 'Mahasiswa berhasil diperbarui.', 'data' => new MahasiswaAdminResource($mahasiswa)]);
    }

    // PUT /api/admin/users/dosen/{id}
    public function updateDosen(UpdateDosenRequest $request, $id)
    {
        $dosen = Dosen::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('foto')) {
            if ($dosen->foto) {
                Storage::disk('public')->delete($dosen->foto);
            }
            $dosen->foto = $request->file('foto')->store('foto_dosen', 'public');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $dosen->update($validated);

        return response()->json(['success' => true, 'message' => 'Dosen berhasil diperbarui.', 'data' => new DosenAdminResource($dosen)]);
    }

    // DELETE /api/admin/users/mahasiswa/{id}
    public function destroyMahasiswa($id)
    {
        $mahasiswa = Mahasiswa::find($id);
        if (! $mahasiswa) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        // delete foto from storage if exist
        if ($mahasiswa->foto) {
            Storage::disk('public')->delete($mahasiswa->foto);
        }

        $mahasiswa->delete();

        return response()->json(['success' => true, 'message' => 'Mahasiswa berhasil dihapus.']);
    }

    // DELETE /api/admin/users/dosen/{id}
    public function destroyDosen($id)
    {
        $dosen = Dosen::find($id);
        if (! $dosen) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan.'], 404);
        }

        if ($dosen->foto) {
            Storage::disk('public')->delete($dosen->foto);
        }

        $dosen->delete();

        return response()->json(['success' => true, 'message' => 'Dosen berhasil dihapus.']);
    }

    // helper: return program studi list for create form
    // GET /api/admin/users/prodi
    public function prodiList()
    {
        return response()->json(['success' => true, 'data' => ProgramStudi::all()]);
    }
}