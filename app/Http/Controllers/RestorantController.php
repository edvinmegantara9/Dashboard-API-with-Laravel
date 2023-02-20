<?php

namespace App\Http\Controllers;

use App\Models\Restorant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RestorantController extends Controller
{
    public function get(Request $request)
    {
        $row        = $request->input('row');
        $keyword    = $request->input('keyword');
        $sortby     = $request->input('sortby');
        $sorttype   = $request->input('sorttype');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $data = Restorant::orderBy('restorants.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('restorants.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('restorants.phone_number', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('restorants.address', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('restorants.kecamatan', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('restorants.desa', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('restorants.nomor_rekening', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'restorants data has been retrieved',
                    'data' => $data
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving categorie data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'phone_number' => 'required',
            'address' => 'required',
            'kecamatan' => 'required',
            'desa' => 'required',

        ]);

        try {
            DB::beginTransaction();

            $restorant = Restorant::create([
                'name' => $request->input('name'),
                'phone_number' => $request->input('phone_number'),
                'address' => $request->input('address'),
                'kecamatan' => $request->input('kecamatan'),
                'desa' => $request->input('desa'),
                'nomor_rekening' => $request->input('nomor_rekening'),
            ]);
            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Restorant data has been Created',
                'data' => $restorant
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 401,
                'message' => 'Error occoured on creating Restorant data',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required',
            'phone_number' => 'required',
            'address' => 'required',
            'kecamatan' => 'required',
            'desa' => 'required',
        ]);

        $restorant = Restorant::findorfail($id);

        try {
            DB::beginTransaction();

            $restorant->update([
                'name' => $request->input('name'),
                'phone_number' => $request->input('phone_number'),
                'address' => $request->input('address'),
                'kecamatan' => $request->input('kecamatan'),
                'desa' => $request->input('desa'),
                'nomor_rekening' => $request->input('nomor_rekening'),
            ]);
            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Restorant data has been Updated',
                'data' => $restorant
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 401,
                'message' => 'Error occoured on Updating Restorant data',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $restorant = Restorant::findorfail($id);

            if (!$restorant->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'Restorant data not found',
                ];
                return response()->json($response, 404);
            }
            return response()->json([
                'status' => 201,
                'message' => 'Restorant data has been deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => 'Error occoured on deleting Restorant data',
                'error' => $e
            ]);
        }
    }
}
