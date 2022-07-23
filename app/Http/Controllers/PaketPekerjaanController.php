<?php

namespace App\Http\Controllers;

use App\Models\PaketPekerjaan;
use App\Models\PaketPekerjaanAfter;
use App\Models\PaketPekerjaanBefore;
use App\Models\PaketPekerjaanProcess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaketPekerjaanController extends Controller
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

            $paket_pekerjaans = PaketPekerjaan::orderBy('paket_pekerjaans.' . $sortby, $sorttype)
                ->with('user', 'role', 'paket_pekerjaan_afters', 'paket_pekerjaan_processes', 'paket_pekerjaan_befores')
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('paket_pekerjaans.nama_paket', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('nama_paket', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('jenis_pekerjaan', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('sumber_dana', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('nilai_kontrak', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('alamat_pekerjaan', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('kecamatan', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('status_pekerjaan', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('tahun_anggaran', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('longitude', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('latitude', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);


            if ($paket_pekerjaans) {
                $response = [
                    'status' => 200,
                    'message' => 'paket pekerjaan data has been retrieved',
                    'data' => $paket_pekerjaans
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving paket pekerjaan data',
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
            'nama_paket' => 'required',
            'jenis_pekerjaan' => 'required',
            'sumber_dana' => 'required',
            'nilai_kontrak' => 'required',
            'alamat_pekerjaan' => 'required',
            'kecamatan' => 'required',
            'status_pekerjaan' => 'required',
            'tahun_anggaran' => 'required',
            'longitude' => 'required',
            'latitude' => 'required'
        ]);

        try {
            DB::beginTransaction();
            
            $paket_pekerjaans = PaketPekerjaan::create(
                [
                    'user_id' => $request->input('user_id'),
                    'opd_id' => $request->input('opd_id'),
                    'nama_paket' => $request->input('nama_paket'),
                    'jenis_pekerjaan' => $request->input('jenis_pekerjaan'),
                    'sumber_dana' => $request->input('sumber_dana'),
                    'nilai_kontrak' => $request->input('nilai_kontrak'),
                    'alamat_pekerjaan' => $request->input('alamat_pekerjaan'),
                    'kecamatan' => $request->input('kecamatan'),
                    'status_pekerjaan' => $request->input('status_pekerjaan'),
                    'tahun_anggaran' => $request->input('tahun_anggaran'),
                    'longitude' => $request->input('longitude'),
                    'latitude' => $request->input('latitude'),
                ]
            );

            foreach ($request->get('paket_pekerjaan_afters') as $d) {
                $detail = new PaketPekerjaanAfter;
                $detail->paket_pekerjaan_id = $paket_pekerjaans->id;
                $detail->image = $d['image'];
                $detail->save();
            }

            foreach ($request->get('paket_pekerjaan_processes') as $d) {
                $detail = new PaketPekerjaanProcess;
                $detail->paket_pekerjaan_id = $paket_pekerjaans->id;
                $detail->image = $d['image'];
                $detail->save();
            }

            foreach ($request->get('paket_pekerjaan_befores') as $d) {
                $detail = new PaketPekerjaanBefore;
                $detail->paket_pekerjaan_id = $paket_pekerjaans->id;
                $detail->image = $d['image'];
                $detail->save();
            }

            if ($paket_pekerjaans) {

                DB::commit();

                $response = [
                    'status' => 201,
                    'message' => 'paket pekerjaan data has been created',
                    'data' => $paket_pekerjaans
                ];

                return response()->json($response, 201);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating paket pekerjaan data',
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
            'nama_paket' => 'required',
            'jenis_pekerjaan' => 'required',
            'sumber_dana' => 'required',
            'nilai_kontrak' => 'required',
            'alamat_pekerjaan' => 'required',
            'kecamatan' => 'required',
            'status_pekerjaan' => 'required',
            'tahun_anggaran' => 'required',
            'longitude' => 'required',
            'latitude' => 'required'
        ]);

        try {
            DB::beginTransaction();
            $paket_pekerjaans = PaketPekerjaan::find($id);

            if ($paket_pekerjaans) {
                $paket_pekerjaans->user_id = $request->input('user_id');
                $paket_pekerjaans->opd_id = $request->input('opd_id');
                $paket_pekerjaans->nama_paket = $request->input('nama_paket');
                $paket_pekerjaans->jenis_pekerjaan = $request->input('jenis_pekerjaan');
                $paket_pekerjaans->sumber_dana = $request->input('sumber_dana');
                $paket_pekerjaans->nilai_kontrak = $request->input('nilai_kontrak');
                $paket_pekerjaans->alamat_pekerjaan = $request->input('alamat_pekerjaan');
                $paket_pekerjaans->kecamatan = $request->input('kecamatan');
                $paket_pekerjaans->status_pekerjaan = $request->input('status_pekerjaan');
                $paket_pekerjaans->tahun_anggaran = $request->input('tahun_anggaran');
                $paket_pekerjaans->longitude = $request->input('longitude');
                $paket_pekerjaans->latitude = $request->input('latitude');
                $paket_pekerjaans->save();

                PaketPekerjaanAfter::where("paket_pekerjaan_id", $paket_pekerjaans->id)->delete();
                foreach ($request->get('paket_pekerjaan_afters') as $d) {
					$detail = new PaketPekerjaanAfter;
					$detail->paket_pekerjaan_id = $paket_pekerjaans->id;
					$detail->image = $d['image'];
					$detail->save();
				}

                PaketPekerjaanProcess::where("paket_pekerjaan_id", $paket_pekerjaans->id)->delete();
                foreach ($request->get('paket_pekerjaan_processes') as $d) {
					$detail = new PaketPekerjaanProcess;
					$detail->paket_pekerjaan_id = $paket_pekerjaans->id;
					$detail->image = $d['image'];
					$detail->save();
				}

                PaketPekerjaanBefore::where("paket_pekerjaan_id", $paket_pekerjaans->id)->delete();
                foreach ($request->get('paket_pekerjaan_befores') as $d) {
					$detail = new PaketPekerjaanBefore;
					$detail->paket_pekerjaan_id = $paket_pekerjaans->id;
					$detail->image = $d['image'];
					$detail->save();
				}

                DB::commit();

                $response = [
                    'status' => 200,
                    'message' => 'paket pekerjaan data has been updated',
                    'data' => $paket_pekerjaans
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'paket pekerjaan data not found',
            ];

            return response()->json($response, 404);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating paket pekerjaan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $paket_pekerjaans = PaketPekerjaan::findOrFail($id);

            if (!$paket_pekerjaans->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'paket pekerjaan data not found',
                ];
                return response()->json($response, 404);
            }
            $response = [
                'status' => 200,
                'message' => 'paket pekerjaan data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating paket pekerjaan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
