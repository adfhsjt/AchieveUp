<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Aras;
use App\Services\Electre;
use App\Services\Entrophy;
use App\Models\Mahasiswa;
use App\Http\Resources\MahasiswaResource;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    protected $aras;
    protected $electre;
    protected $entrophy;

    public function __construct(Aras $aras, Electre $electre, Entrophy $entrophy)
    {
        $this->aras = $aras;
        $this->electre = $electre;
        $this->entrophy = $entrophy;
    }

    /**
     * GET /api/landing
     * Public landing data: list mahasiswa + ranking (Aras/Electre)
     */
    public function index(Request $request)
    {
        // ambil semua mahasiswa (sama seperti web)
        $mahasiswa = Mahasiswa::all();

        // sesuaikan layanan mana yang ingin ditampilkan (di web kamu pakai $this->aras->getRanking())
        $rankElectre = $this->aras->getRanking();

        return response()->json([
            'success' => true,
            'data' => [
                'mahasiswa' => MahasiswaResource::collection($mahasiswa),
                'rankElectre' => $rankElectre,
            ],
        ]);
    }
}