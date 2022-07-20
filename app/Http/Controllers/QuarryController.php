<?php

namespace App\Http\Controllers;

use App\Models\DocumentQuarry;
use App\Models\Quarry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuarryController extends Controller
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

            $quarries = Quarry::orderBy('quarries.' . $sortby, $sorttype)
                ->with('document_quarries')
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('quarries.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('address', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('notes', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($quarries) {
                $response = [
                    'status' => 200,
                    'message' => 'quarry data has been retrieved',
                    'data' => $quarries
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

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'address' => 'required',
            'notes' => 'required',
            'longitude' => 'required',
            'latitude' => 'required',
            'document_quarries' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $quarry = new Quarry;
			$quarry->name = $request->input('name');
			$quarry->notes = $request->input('notes');
			$quarry->address = $request->input('address');
            $quarry->longitude = $request->input('longitude');
			$quarry->latitude = $request->input('latitude');

			if ($quarry->save()) { 
                foreach ($request->get('document_quarries') as $d) {
					$detail = new DocumentQuarry;
					$detail->quarry_id = $quarry->id;
					$detail->image = $d['image'];
					$detail->save();
				}
            }

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'quarry data has been created',
                'data' => $quarry
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating quarry data',
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
            'document_quarries' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $quarries = Quarry::find($id);

            if ($quarries) {
                $quarries->name = $request->input('name');
                $quarries->address = $request->input('address');
                $quarries->notes = $request->input('notes');
                $quarries->longitude = $request->input('longitude');
                $quarries->latitude = $request->input('latitude');
                $quarries->save();

                DocumentQuarry::where("quarry_id", $quarries->id)->delete();
                foreach ($request->get('document_quarries') as $d) {
					$detail = new DocumentQuarry;
					$detail->quarry_id = $quarries->id;
					$detail->image = $d['image'];
					$detail->save();
				}

                DB::commit();

                $response = [
                    'status' => 200,
                    'message' => 'quarry data has been updated',
                    'data' => $quarries
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'quarry data not found',
            ];

            return response()->json($response, 404);

        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating quarry data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function delete($id)
    {
        try {
            $quarries = Quarry::findOrFail($id);
            
            if (!$quarries->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'quarry data not found',
                ];
                return response()->json($response, 404);
            }

            DocumentQuarry::where("quarry_id", $id)->delete();

            $response = [
                'status' => 200,
                'message' => 'quarry data has been deleted',
            ];

            return response()->json($response, 200);

        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on creating quarry data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
