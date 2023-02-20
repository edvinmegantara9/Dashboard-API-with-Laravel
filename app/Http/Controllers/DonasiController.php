<?php

namespace App\Http\Controllers;

use App\Models\Donasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonasiController extends Controller
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
            $data = Donasi::with('restorant')->orderBy('donasis.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('donasis.name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('donasis.phone_number', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('donasis.bukti_transfer', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('donasis.payment_method', 'LIKE', '%' . $keyword . '%');
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
            'restorant_id' => 'required',
            'name' => 'required',
            'phone_number' => 'required',
            'bukti_transfer' => 'required',
            'payment_method' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $donasi = Donasi::create([
                'restorant_id' => $request->input('restorant_id'),
                'name' => $request->input('name'),
                'phone_number' => $request->input('phone_number'),
                'bukti_transfer' => $request->input('bukti_transfer'),
                'payment_method' => $request->input('payment_method'),
            ]);
            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Donasi data has been Created',
                'data' => $donasi
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 401,
                'message' => 'Error occoured on creating Donasi data',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'restorant_id' => 'required',
            'name' => 'required',
            'phone_number' => 'required',
            'bukti_transfer' => 'required',
            'payment_method' => 'required',
        ]);

        $donasi = Donasi::findorfail($id);
        try {
            DB::beginTransaction();

            $donasi->update([
                'name' => $request->input('name'),
                'phone_number' => $request->input('phone_number'),
                'bukti_transfer' => $request->input('bukti_transfer'),
                'payment_method' => $request->input('payment_method'),
            ]);
            DB::commit();
            return response()->json([
                'status' => 201,
                'message' => 'Donasi data has been Updated',
                'data' => $donasi
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 401,
                'message' => 'Error occoured on Updating Donasi data',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        try {
            $donasi = Donasi::findorfail($id);

            if (!$donasi->delete()) {
                $response = [
                    'status' => 404,
                    'message' => 'Donasi data not found',
                ];
                return response()->json($response, 404);
            }
            return response()->json([
                'status' => 201,
                'message' => 'Donasi data has been deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 401,
                'message' => 'Error occoured on deleting Donasi data',
                'error' => $e
            ]);
        }
    }
}
