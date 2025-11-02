<?php

namespace App\Http\Controllers;

use App\Models\Bidang;
use Illuminate\Http\Request;

class BidangController extends Controller
{
    public function index()
    {
        $breadcrumb = (object)[
            'title' => 'Manajemen Bidang',
            'list' => ['Home', 'Manajemen Bidang']
        ];

        $page = (object)[
            'title' => 'Daftar Bidang'
        ];

        $activeMenu = 'bidang';

        return view('admin.bidang.index', compact('breadcrumb', 'page', 'activeMenu'));
    }

    public function getBidang($id){
        $bidang = Bidang::find($id);
        return $bidang;
    }

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

        return response()->json($data);
    }

    public function create()
    {
        $breadcrumb = (object)[
            'title' => 'Tambah Bidang',
            'list' => ['Home', 'Bidang', 'Tambah Bidang']
        ];

        $page = (object)[
            'title' => 'Tambah Bidang'
        ];

        $activeMenu = 'bidang';

        return view('admin.bidang.create', compact('breadcrumb', 'page', 'activeMenu'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'kode' => 'required|string|max:10|unique:bidang,kode',
            'nama' => 'required|string|max:255|unique:bidang,nama',
        ], [
            'kode.unique' => 'Kode sudah digunakan.',
            'nama.unique' => 'Nama sudah digunakan.',
        ]);

        try {
            Bidang::create($request->all());
            return redirect()->route('admin.bidang.index')->with('success', 'Bidang berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan Bidang: ' . $e->getMessage());
        }
    }
    public function edit($id)
    {
        $bidang = Bidang::findOrFail($id);

        $breadcrumb = (object)[
            'title' => 'Edit Bidang',
            'list' => ['Home', 'Bidang', 'Edit Bidang']
        ];

        $page = (object)[
            'title' => 'Edit Bidang'
        ];

        $activeMenu = 'bidang';

        return view('admin.bidang.edit', compact('breadcrumb', 'page', 'activeMenu', 'bidang'));
    }
    public function update(Request $request, $id)
    {
        $bidang = Bidang::findOrFail($id);

        $request->validate([
            'kode' => 'required|string|max:10|unique:bidang,kode,' . $bidang->id,
            'nama' => 'required|string|max:255|unique:bidang,nama,' . $bidang->id,
        ], [
            'kode.unique' => 'Kode sudah digunakan.',
            'nama.unique' => 'Nama sudah digunakan.',
        ]);

        try {
            $bidang->update($request->all());
            return redirect()->route('admin.bidang.index')->with('success', 'Bidang berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui Bidang: ' . $e->getMessage());
        }
    }
    public function destroy($id)
    {
        try {
            $bidang = Bidang::findOrFail($id);

            if ($bidang->lomba()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bidang tidak dapat dihapus karena memiliki data mahasiswa.'
                ], 400);
            }

            $bidang->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bidang berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus Bidang: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $bidang = $this->getBidang($id);
        $breadcrumb = (object) [
            'title' => 'Detail Bidang',
            'list' => ['Home', 'Bidang', 'Detail']
        ];

        $page = (object) [
            'title' => 'Detail data Bidang'
        ];

        $activeMenu = 'bidang';

        return view('admin.bidang.detail', compact(
            'breadcrumb',
            'page',
            'activeMenu',
            'bidang'
        ));
    }
}
