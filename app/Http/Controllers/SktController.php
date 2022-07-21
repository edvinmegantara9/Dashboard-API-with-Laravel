<?php

namespace App\Http\Controllers;

use App\Models\Skt;
use Illuminate\Http\Request;

class SktController extends Controller
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

            $skts = Skt::orderBy('skts.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('skts.nama', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('alamat', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('id_sub_bagian', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('deskripsi', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('id_kualifikasi_profesi', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('asosiasi', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('tgl_cetak_sertifikat', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('provinsi_domisili', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('kabupaten', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('provinsi_registrasi', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($skts) {
                $response = [
                    'status' => 200,
                    'message' => 'skt data has been retrieved',
                    'data' => $skts
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving skt data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'nama' => 'required',
            'alamat' => 'required',
            'id_sub_bagian' => 'required',
            'deskripsi' => 'required',
            'id_kualifikasi_profesi' => 'required',
            'asosiasi' => 'required',
            'tgl_cetak_sertifikat' => 'required',
            'provinsi_domisili' => 'required',
            'kabupaten' => 'required',
            'provinsi_registrasi' => 'required',
        ]);

        try {
            $skts = Skt::create(
                [
                    'nama' => $request->input('nama'),
                    'alamat' => $request->input('alamat'),
                    'id_sub_bagian' => $request->input('id_sub_bagian'),
                    'deskripsi' => $request->input('deskripsi'),
                    'id_kualifikasi_profesi' => $request->input('id_kualifikasi_profesi'),
                    'asosiasi' => $request->input('asosiasi'),
                    'tgl_cetak_sertifikat' => $request->input('tgl_cetak_sertifikat'),
                    'provinsi_domisili' => $request->input('provinsi_domisili'),
                    'kabupaten' => $request->input('kabupaten'),
                    'provinsi_registrasi' => $request->input('provinsi_registrasi'),
                ]
            );

            if ($skts) {
                $response = [
                    'status' => 201,
                    'message' => 'skt data has been created',
                    'data' => $skts
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating skt data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'nama' => 'required',
            'alamat' => 'required',
            'id_sub_bagian' => 'required',
            'deskripsi' => 'required',
            'id_kualifikasi_profesi' => 'required',
            'asosiasi' => 'required',
            'tgl_cetak_sertifikat' => 'required',
            'provinsi_domisili' => 'required',
            'kabupaten' => 'required',
            'provinsi_registrasi' => 'required',
        ]);

        try {
            $skts = Skt::find($id);

            if ($skts) {
                $skts->nama = $request->input('nama');
                $skts->alamat = $request->input('alamat');
                $skts->id_sub_bagian = $request->input('id_sub_bagian');
                $skts->deskripsi = $request->input('deskripsi');
                $skts->id_kualifikasi_profesi = $request->input('id_kualifikasi_profesi');
                $skts->asosiasi = $request->input('asosiasi');
                $skts->tgl_cetak_sertifikat = $request->input('tgl_cetak_sertifikat');
                $skts->provinsi_domisili = $request->input('provinsi_domisili');
                $skts->kabupaten = $request->input('kabupaten');
                $skts->provinsi_registrasi = $request->input('provinsi_registrasi');
                $skts->save();

                $response = [
                    'status' => 200,
                    'message' => 'skt data has been updated',
                    'data' => $skts
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'skt data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating skt data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $skts = Skt::findOrFail($id);

            if (!$skts->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'skt data not found',
                ];
                return response()->json($response, 404);
            }
            $response = [
                'status' => 200,
                'message' => 'skt data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating skt data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
