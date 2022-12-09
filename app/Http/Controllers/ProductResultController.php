<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductResultController extends Controller
{
    public function get(Request $request)
    {
        $row      = $request->input('row');
        $keyword  = $request->input('keyword');
        $sortby   = $request->input('sortby');
        $sorttype = $request->input('sorttype');
        $user_id  = Auth::user()->id;
        // $status   = json_decode($request->input('status'));

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $data = ProductResult::orderBy('product_results.' . $sortby, $sorttype)
                ->where('user_id', $user_id)
                // ->whereIn('status', $status)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('product_results.full_name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.no_transaction', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.nik', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.age', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.work', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.address', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'product_result data has been retrieved',
                    'data' => $data
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving product data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'product_id'         => 'required|integer',
            'product_payment_id' => 'required|integer',
            'full_name'          => 'required|string',
            'nik'                => 'required|string',
            'age'                => 'required|integer',
            'work'               => 'required|string',
            'address'            => 'required|string',
            'total_point'        => 'required|integer',
            'sim_type'           => 'required|string',
            'needs'              => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // find product by id
            $product = Product::where('id', $request->input('product_id'))->first();
            if (!$product) {
                $response = [
                    'status' => 404,
                    'message' => 'product not found',
                ];
                return response()->json($response, 404);
            }

            $product_result = new ProductResult;
            $product_result->user_id            = Auth::user()->id;
            $product_result->product_id         = $product->id;
            $product_result->product_payment_id = $request->input('product_payment_id'); 
            $product_result->full_name          = $request->input('full_name');
            $product_result->nik                = $request->input('nik');     
            $product_result->age                = $request->input('age');   
            $product_result->work               = $request->input('work');   
            $product_result->address            = $request->input('address');   
            $product_result->sim_type           = $request->input('sim_type');   
            $product_result->needs              = $request->input('needs');  
            $product_result->total_point        = $request->input('total_point');
            $product_result->repetition         = 1;
            $product_result->expired_at         = date('Y-m-d H:i:s',strtotime('+'.$product->expired_result.' day'));

            // menentukan gagal dan lolos dari test
            if ($request->input('total_point') > $product->max_point_result) {
                $product_result->status = 'lolos';
                $message = 'Selamat Anda telah lulus dalam tes Psikotes!';
            } else {
                $product_result->status = 'gagal';
                $message = 'Mohon maaf, anda belum lulus test psikotes. namun anda bisa mengulanginya lagi sebelum 24 Jam';
            }
                   
            $product_result->save(); 

            DB::commit();

            $response = [
                'status' => 201,
                'message' => $message,
                'data' => $product_result,
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on creating product data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'product_id'         => 'required|exists:products',
            'product_payment_id' => 'required|exists:product_payments',
            'full_name'          => 'required|string',
            'nik'                => 'required|string',
            'age'                => 'required|integer',
            'work'               => 'required|string',
            'address'            => 'required|string',
            'total_point'        => 'required|string',
            'sim_type'           => 'required|string',
            'needs'              => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            // find product by id
            $product = Product::where('id', $request->input('product_id'))->first();
            if (!$product) {
                $response = [
                    'status' => 404,
                    'message' => 'product not found',
                ];
                return response()->json($response, 404);
            }

            $product_result = ProductResult::where('id', $id)->first();

            if (!$product_result) {
                $response = [
                    'status' => 404,
                    'message' => 'Result not found',
                ];
                return response()->json($response, 404);
            }

            $product_result->user_id            = Auth::user()->id;
            $product_result->product_id         = $product->id;
            $product_result->product_payment_id = $request->input('product_payment_id'); 
            $product_result->full_name          = $request->input('full_name');
            $product_result->nik                = $request->input('nik');    
            $product_result->age                = $request->input('age');   
            $product_result->work               = $request->input('work');   
            $product_result->address            = $request->input('address'); 
            $product_result->sim_type           = $request->input('sim_type');   
            $product_result->needs              = $request->input('needs');   
            $product_result->total_point        = $request->input('total_point');
            $product_result->repetition         = $product_result->repetition + 1;

            // menentukan gagal dan lolos dari test
            if ($request->input('total_point') > $product->max_point_result) {
                $product_result->status = 'lolos';
                $message = 'Selamat Anda telah lulus dalam tes Psikotes!';
            } else {
                $product_result->status = 'gagal';
                $message = 'Mohon maaf, anda belum lulus test psikotes. namun anda bisa mengulanginya lagi sebelum 24 Jam';
            }
                   
            $product_result->save(); 

            DB::commit();

            $response = [
                'status' => 201,
                'message' => $message,
                'data' => $product_result,
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 400,
                'message' => 'error occured on updated result data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function generatePdf($id) {
        
    }
}
