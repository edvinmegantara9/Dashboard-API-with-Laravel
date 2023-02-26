<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use App\Models\Restorant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function get()
    {
        $items = [];
        try {

            $namaresto = Restorant::select('name', 'id')->get();
            $donasi = Donasi::select('restorant_id')->get();

            $resto = [];
            foreach ($namaresto as $nama) {
                // foreach ($donasi as $bantuan) {
                //     $data = Donasi::where('restorant_id', $bantuan->restorant_id)->groupBy('restorant_id')->sum('jumlah_donasi');
                // }
                $data = Donasi::where('restorant_id', $nama->id)->groupBy('restorant_id')->sum('jumlah_donasi');
                $resto[$nama->name] = $data;
            }
            $items[] = $resto;

            if ($items) {
                $response = [
                    'status' => 200,
                    'message' => 'Populasi data has been retrieved',
                    'data' => $items
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving quarry data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
