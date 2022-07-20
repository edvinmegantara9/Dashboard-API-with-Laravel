<?php

namespace App\Http\Controllers;

use App\Models\DocumentAlatKontruksi;
use App\Models\AlatKontruksi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlatKontruksiController extends Controller
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

            $alat_kontruksis = AlatKontruksi::orderBy('alat_kontruksis.' . $sortby, $sorttype)
                ->with('document_alat_kontruksis')
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('alat_kontruksis.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('address', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('notes', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($alat_kontruksis) {
                $response = [
                    'status' => 200,
                    'message' => 'alat_kontruksi data has been retrieved',
                    'data' => $alat_kontruksis
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving alat_kontruksi data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'notes' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'document_alat_kontruksis' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $alat_kontruksi = new AlatKontruksi;
			$alat_kontruksi->name = $request->input('name');
			$alat_kontruksi->notes = $request->input('notes');
			$alat_kontruksi->address = $request->input('address');
            $alat_kontruksi->longitude = $request->input('longitude');
			$alat_kontruksi->latitude = $request->input('latitude');

			if ($alat_kontruksi->save()) { 
                foreach ($request->get('document_alat_kontruksis') as $d) {
					$detail = new DocumentAlatKontruksi;
					$detail->alat_kontruksi_id = $alat_kontruksi->id;
					$detail->image = $d['image'];
					$detail->save();
				}
            }

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'alat_kontruksi data has been created',
                'data' => $alat_kontruksi
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating alat_kontruksi data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'notes' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'document_alat_kontruksis' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $alat_kontruksis = AlatKontruksi::find($id);

            if ($alat_kontruksis) {
                $alat_kontruksis->name = $request->input('name');
                $alat_kontruksis->address = $request->input('address');
                $alat_kontruksis->notes = $request->input('notes');
                $alat_kontruksis->longitude = $request->input('longitude');
                $alat_kontruksis->latitude = $request->input('latitude');
                $alat_kontruksis->save();

                DocumentAlatKontruksi::where("alat_kontruksi_id", $alat_kontruksis->id)->delete();
                foreach ($request->get('document_alat_kontruksis') as $d) {
					$detail = new DocumentAlatKontruksi;
					$detail->alat_kontruksi_id = $alat_kontruksis->id;
					$detail->image = $d['image'];
					$detail->save();
				}

                DB::commit();

                $response = [
                    'status' => 200,
                    'message' => 'alat_kontruksi data has been updated',
                    'data' => $alat_kontruksis
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'alat_kontruksi data not found',
            ];

            return response()->json($response, 404);

        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating alat_kontruksi data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $alat_kontruksis = AlatKontruksi::findOrFail($id);
            
            if (!$alat_kontruksis->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'alat_kontruksi data not found',
                ];
                return response()->json($response, 404);
            }

            DocumentAlatKontruksi::where("alat_kontruksi_id", $id)->delete();

            $response = [
                'status' => 200,
                'message' => 'alat_kontruksi data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating alat_kontruksi data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
