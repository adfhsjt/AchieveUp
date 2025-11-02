<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Aras;
use App\Services\Electre;
use App\Services\Entrophy;
use Illuminate\Http\Request;

class DashboardAdminController extends Controller
{
    protected $entrophy;
    protected $electre;
    protected $aras;

    public function __construct(Entrophy $entrophy, Electre $electre, Aras $aras)
    {
        $this->entrophy = $entrophy;
        $this->electre = $electre;
        $this->aras = $aras;
    }

    /**
     * GET /api/admin/dashboard
     * returns top-level dashboard data (rankings)
     */
    public function index(Request $request)
    {
        $rankingElectre = $this->electre->getRanking();
        $rankingAras = $this->aras->getRanking();

        return response()->json([
            'success' => true,
            'data' => [
                'rankingElectre' => $rankingElectre,
                'rankingAras' => $rankingAras,
            ],
        ]);
    }

    /**
     * GET /api/admin/dashboard/entropy
     * returns all intermediate arrays/values produced by Entrophy service
     */
    public function entropy(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'getSampleData' => $this->entrophy->getSampleData(),
                'getScoreLomba' => $this->entrophy->getScoreLomba(),
                'getDataAlternatif' => $this->entrophy->getDataAlternatif(),
                'getNormalisasi' => $this->entrophy->getNormalisasi(),
                'getMaxMin' => $this->entrophy->getMaxMin(),
                'getTotalKriteria' => $this->entrophy->getTotalKriteria(),
                'getNilaiProporsional' => $this->entrophy->getNilaiProporsional(),
                'getNilaiLn' => $this->entrophy->getNilaiLn(),
                'getNilaiProporsionalKaliLn' => $this->entrophy->getNilaiProporsionalKaliLn(),
                'getTotalPLn' => $this->entrophy->getTotalPLn(),
                'getNilaiEj' => $this->entrophy->getNilaiEj(),
                'getNilaiEntrophy' => $this->entrophy->getNilaiEntrophy(),
                'getNilaiDispersi' => $this->entrophy->getNilaiDispersi(),
                'getTotalNilaiDispersi' => $this->entrophy->getTotalNilaiDispersi(),
                'getBobotKriteria' => $this->entrophy->getBobotKriteria(),
            ],
        ]);
    }

    /**
     * GET /api/admin/dashboard/electre
     */
    public function electre(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'getDataAlternatif' => $this->entrophy->getDataAlternatif(),
                'getPenyebut' => $this->electre->getPenyebut(),
                'getMatriksNormalisasiTerbobot' => $this->electre->getMatriksNormalisasiTerbobot(),
                'getBobotKriteria' => $this->entrophy->getBobotKriteria(),
                'getHasilPembobotanMatriks' => $this->electre->getHasilPembobotanMatriks(),
                'getNilaiCorcondace' => $this->electre->getNilaiCorcondace(),
                'getCorcondace' => $this->electre->getCorcondace(),
                'getNilaiC' => $this->electre->getNilaiC(),
                'getTresholdC' => $this->electre->getTresholdC(),
                'getMatriksDominanC' => $this->electre->getMatriksDominanC(),
                'getNilaiDiscordance' => $this->electre->getNilaiDiscordance(),
                'getDiscordance' => $this->electre->getDiscordance(),
                'getNilaiD' => $this->electre->getNilaiD(),
                'getTresholdD' => $this->electre->getTresholdD(),
                'getMatriksDominanD' => $this->electre->getMatriksDominanD(),
                'getAgregatDominanMatriks' => $this->electre->getAgregatDominanMatriks(),
                'getRanking' => $this->electre->getRanking(),
            ],
        ]);
    }

    /**
     * GET /api/admin/dashboard/aras
     */
    public function aras(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'getBobotKriteria' => $this->entrophy->getBobotKriteria(),
                'getDataAlternatif' => $this->entrophy->getDataAlternatif(),
                'getAlternatif' => $this->aras->getAlternatif(),
                'getDataBaru' => $this->aras->getDataBaru(),
                'getTotalKriteria' => $this->aras->getTotalKriteria(),
                'getNormalisasi' => $this->aras->getNormalisasi(),
                'getNilaiUtilitas' => $this->aras->getNilaiUtilitas(),
                'getRanking' => $this->aras->getRanking(),
            ],
        ]);
    }

    /**
     * GET /api/admin/dashboard/test
     * returns a compact debug object (non-dd)
     */
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'entrophy_sample' => method_exists($this->entrophy, 'getAllFunction') ? $this->entrophy->getAllFunction() : null,
                'electre_sample' => method_exists($this->electre, 'getAllFunction') ? $this->electre->getAllFunction() : null,
                'aras_sample' => method_exists($this->aras, 'getAllFunction') ? $this->aras->getAllFunction() : null,
                'rankingElectre' => $this->electre->getRanking(),
                'rankingAras' => $this->aras->getRanking(),
            ],
        ]);
    }
}