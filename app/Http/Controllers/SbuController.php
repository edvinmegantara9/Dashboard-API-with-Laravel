<?php

namespace App\Http\Controllers;

use App\Models\Sbu;
use Illuminate\Http\Request;

class SbuController extends Controller
{
    public function get(Request $request)
    {
        $row = $request->input('row');
        $keyword = $request->input('keyword');
        $sortby = $request->input('sortby');
        $sorttype = $request->input('sorttype');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {

            $sbus = Sbu::orderBy('sbus.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('sbus.nama_pjbu', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('nama_badan_usaha', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('alamat', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('kecamatan', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('bentuk', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('asosiasi', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('sub_klasifikasi_kbli', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('kualifikasi_kbli', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('tanggal_terbit', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($sbus) {
                $response = [
                    'status' => 200,
                    'message' => 'sbu data has been retrieved',
                    'data' => $sbus
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving sbu data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'nama_pjbu' => 'required',
            'nama_badan_usaha' => 'required',
            'alamat' => 'required',
            'kecamatan' => 'required',
            'bentuk' => 'required',
            'asosiasi' => 'required',
            'sub_klasifikasi_kbli' => 'required',
            'kualifikasi_kbli' => 'required',
            'tanggal_terbit' => 'required',
        ]);

        try {
            $sbus = Sbu::create(
                [
                    'nama_pjbu' => $request->input('nama_pjbu'),
                    'nama_badan_usaha' => $request->input('nama_badan_usaha'),
                    'alamat' => $request->input('alamat'),
                    'kecamatan' => $request->input('kecamatan'),
                    'bentuk' => $request->input('bentuk'),
                    'asosiasi' => $request->input('asosiasi'),
                    'sub_klasifikasi_kbli' => $request->input('sub_klasifikasi_kbli'),
                    'kualifikasi_kbli' => $request->input('kualifikasi_kbli'),
                    'tanggal_terbit' => $request->input('tanggal_terbit'),
                ]
            );

            if ($sbus) {
                $response = [
                    'status' => 201,
                    'message' => 'sbu data has been created',
                    'data' => $sbus
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating sbu data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama_pjbu' => 'required',
            'nama_badan_usaha' => 'required',
            'alamat' => 'required',
            'kecamatan' => 'required',
            'bentuk' => 'required',
            'asosiasi' => 'required',
            'sub_klasifikasi_kbli' => 'required',
            'kualifikasi_kbli' => 'required',
            'tanggal_terbit' => 'required',
        ]);

        try {
            $sbus = Sbu::find($id);

            if ($sbus) {
                $sbus->nama_pjbu = $request->input('nama_pjbu');
                $sbus->nama_badan_usaha = $request->input('nama_badan_usaha');
                $sbus->kecamatan = $request->input('kecamatan');
                $sbus->bentuk = $request->input('bentuk');
                $sbus->asosiasi = $request->input('asosiasi');
                $sbus->sub_klasifikasi_kbli = $request->input('sub_klasifikasi_kbli');
                $sbus->kualifikasi_kbli = $request->input('kualifikasi_kbli');
                $sbus->tanggal_terbit = $request->input('tanggal_terbit');
                $sbus->save();

                $response = [
                    'status' => 200,
                    'message' => 'sbu data has been updated',
                    'data' => $sbus
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'sbu data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating sbu data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $sbus = Sbu::findOrFail($id);

            if (!$sbus->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'sbu data not found',
                ];
                return response()->json($response, 404);
            }
            $response = [
                'status' => 200,
                'message' => 'sbu data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating sbu data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
