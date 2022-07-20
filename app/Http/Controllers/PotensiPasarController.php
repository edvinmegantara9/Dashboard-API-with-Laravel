<?php

namespace App\Http\Controllers;

use App\Models\PotensiPasar;
use Illuminate\Http\Request;

class PotensiPasarController extends Controller
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

            $potensi_pasars = PotensiPasar::orderBy('potensi_pasars.' . $sortby, $sorttype)
                ->with('user', 'role')
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('potensi_pasars.sumber_dana', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('nilai_kontrak', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('tahun_anggaran', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('nilai_pekerjaan', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('jenis_pekerjaan', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($potensi_pasars) {
                $response = [
                    'status' => 200,
                    'message' => 'potensi pasar data has been retrieved',
                    'data' => $potensi_pasars
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving potensi pasar data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'opd_id' => 'required',
            'jenis_pekerjaan' => 'required',
            'sumber_dana' => 'required',
            'nilai_pekerjaan' => 'required',
            'tahun_anggaran' => 'required',
        ]);

        try {
            $potensi_pasars = PotensiPasar::create(
                [
                    'user_id' => $request->input('user_id'),
                    'opd_id' => $request->input('opd_id'),
                    'jenis_pekerjaan' => $request->input('jenis_pekerjaan'),
                    'sumber_dana' => $request->input('sumber_dana'),
                    'nilai_pekerjaan' => $request->input('nilai_pekerjaan'),
                    'tahun_anggaran' => $request->input('tahun_anggaran'),
                ]
            );

            if ($potensi_pasars) {
                $response = [
                    'status' => 201,
                    'message' => 'potensi pasar data has been created',
                    'data' => $potensi_pasars
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating potensi pasar data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'user_id' => 'required',
            'opd_id' => 'required',
            'jenis_pekerjaan' => 'required',
            'sumber_dana' => 'required',
            'nilai_pekerjaan' => 'required',
            'tahun_anggaran' => 'required',
        ]);

        try {
            $potensi_pasars = PotensiPasar::find($id);

            if ($potensi_pasars) {
                $potensi_pasars->user_id = $request->input('user_id');
                $potensi_pasars->opd_id = $request->input('opd_id');
                $potensi_pasars->jenis_pekerjaan = $request->input('jenis_pekerjaan');
                $potensi_pasars->sumber_dana = $request->input('sumber_dana');
                $potensi_pasars->nilai_pekerjaan = $request->input('nilai_pekerjaan');
                $potensi_pasars->tahun_anggaran = $request->input('tahun_anggaran');
                $potensi_pasars->save();

                $response = [
                    'status' => 200,
                    'message' => 'potensi pasar data has been updated',
                    'data' => $potensi_pasars
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'potensi pasar data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating potensi pasar data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $potensi_pasars = PotensiPasar::findOrFail($id);

            if (!$potensi_pasars->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'potensi pasar data not found',
                ];
                return response()->json($response, 404);
            }
            $response = [
                'status' => 200,
                'message' => 'potensi pasar data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating potensi pasar data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
