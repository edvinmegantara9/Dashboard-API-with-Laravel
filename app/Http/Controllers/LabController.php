<?php

namespace App\Http\Controllers;

use App\Models\DocumentLab;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabController extends Controller
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

            $labs = Lab::orderBy('labs.' . $sortby, $sorttype)
                ->with('document_labs')
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('labs.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('address', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('notes', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($labs) {
                $response = [
                    'status' => 200,
                    'message' => 'lab data has been retrieved',
                    'data' => $labs
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving lab data',
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
            'document_labs' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $lab = new Lab;
			$lab->name = $request->input('name');
			$lab->notes = $request->input('notes');
			$lab->address = $request->input('address');
            $lab->longitude = $request->input('longitude');
			$lab->latitude = $request->input('latitude');

			if ($lab->save()) { 
                foreach ($request->get('document_labs') as $d) {
					$detail = new DocumentLab;
					$detail->lab_id = $lab->id;
					$detail->image = $d['image'];
					$detail->save();
				}
            }

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'lab data has been created',
                'data' => $lab
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating lab data',
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
            'document_labs' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $labs = Lab::find($id);

            if ($labs) {
                $labs->name = $request->input('name');
                $labs->address = $request->input('address');
                $labs->notes = $request->input('notes');
                $labs->longitude = $request->input('longitude');
                $labs->latitude = $request->input('latitude');
                $labs->save();

                DocumentLab::where("lab_id", $labs->id)->delete();
                foreach ($request->get('document_labs') as $d) {
					$detail = new DocumentLab;
					$detail->lab_id = $labs->id;
					$detail->image = $d['image'];
					$detail->save();
				}

                DB::commit();

                $response = [
                    'status' => 200,
                    'message' => 'lab data has been updated',
                    'data' => $labs
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'lab data not found',
            ];

            return response()->json($response, 404);

        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating lab data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $labs = Lab::findOrFail($id);
            
            if (!$labs->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'lab data not found',
                ];
                return response()->json($response, 404);
            }

            DocumentLab::where("lab_id", $id)->delete();

            $response = [
                'status' => 200,
                'message' => 'lab data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating lab data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
