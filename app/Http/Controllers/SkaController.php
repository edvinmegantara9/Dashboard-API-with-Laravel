<?php

namespace App\Http\Controllers;

use App\Models\Ska;
use Illuminate\Http\Request;

class SkaController extends Controller
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

            $skas = Ska::orderBy('skas.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('skas.nama', 'LIKE', '%' . $keyword . '%')
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

            if ($skas) {
                $response = [
                    'status' => 200,
                    'message' => 'ska data has been retrieved',
                    'data' => $skas
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving ska data',
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
            $skas = Ska::create(
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

            if ($skas) {
                $response = [
                    'status' => 201,
                    'message' => 'ska data has been created',
                    'data' => $skas
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating ska data',
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
            $skas = Ska::find($id);

            if ($skas) {
                $skas->nama = $request->input('nama');
                $skas->alamat = $request->input('alamat');
                $skas->id_sub_bagian = $request->input('id_sub_bagian');
                $skas->deskripsi = $request->input('deskripsi');
                $skas->id_kualifikasi_profesi = $request->input('id_kualifikasi_profesi');
                $skas->asosiasi = $request->input('asosiasi');
                $skas->tgl_cetak_sertifikat = $request->input('tgl_cetak_sertifikat');
                $skas->provinsi_domisili = $request->input('provinsi_domisili');
                $skas->kabupaten = $request->input('kabupaten');
                $skas->provinsi_registrasi = $request->input('provinsi_registrasi');
                $skas->save();

                $response = [
                    'status' => 200,
                    'message' => 'ska data has been updated',
                    'data' => $skas
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'ska data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating ska data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $skas = Ska::findOrFail($id);

            if (!$skas->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'ska data not found',
                ];
                return response()->json($response, 404);
            }
            $response = [
                'status' => 200,
                'message' => 'ska data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating ska data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
