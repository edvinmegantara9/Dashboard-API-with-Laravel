<?php

namespace App\Http\Controllers;

use App\Exports\ProductResultExport;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

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
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('product_results.full_name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.no_transaction', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.nik', 'LIKE', '%' . $keyword . '%')
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
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving product data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function getAdmin(Request $request)
    {
        $row       = $request->input('row');
        $keyword   = $request->input('keyword');
        $sortby    = $request->input('sortby');
        $sorttype  = $request->input('sorttype');
        $firstdate = $request->input('firstdate');
        $lastdate  = $request->input('lastdate');
        $status    = $request->input('status');
        $sim_type  = $request->input('sim_type');
        $needs     = $request->input('needs');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $data = ProductResult::with('payment', 'user', 'product')
                    ->whereBetween('product_results.created_at', [date('Y-m-d 00:00:00', strtotime($firstdate)), date('Y-m-d 23:59:59', strtotime($lastdate))])
                    ->orderBy('product_results.' . $sortby, $sorttype);

            if ($status !== "") {
                $data = $data->where('status', $status);
            }

            if ($sim_type !== "") {
                $data = $data->where('sim_type', $sim_type);
            }

            if ($needs !== "") {
                $data = $data->where('needs', $needs);
            }
            
            $data = $data->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('product_results.full_name', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.nik', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.work', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.address', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.sim_type', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.needs', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.total_point', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('product_results.status', 'LIKE', '%' . $keyword . '%');
                    })
                    ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'product_results data has been retrieved',
                    'data' => $data
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
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
            $product_result->work               = $request->input('work');   
            $product_result->address            = $request->input('address');   
            $product_result->sim_type           = $request->input('sim_type');   
            $product_result->needs              = $request->input('needs');  
            $product_result->total_point        = $request->input('total_point');
            $product_result->repetition         = 1;
            $product_result->expired_at         = date('Y-m-d H:i:s',strtotime('+'.$product->expired_result.' day'));

            // menentukan gagal dan lolos dari test
            if ($request->input('total_point') > $product->max_point_result) {
                $product_result->status = 'LULUS';
                $message = 'Selamat Anda telah lulus dalam tes Psikotes!';
            } else {
                $product_result->status = 'GAGAL';
                $message = 'Mohon maaf, anda belum lulus test psikotes. namun anda bisa mengulanginya lagi sebelum 24 jam';
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
            \Sentry\captureException($e);
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
            'product_id'         => 'required|integer',
            'product_payment_id' => 'required|integer',
            'full_name'          => 'required|string',
            'nik'                => 'required',
            'work'               => 'required|string',
            'address'            => 'required|string',
            'total_point'        => 'required',
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
            $product_result->work               = $request->input('work');   
            $product_result->address            = $request->input('address'); 
            $product_result->sim_type           = $request->input('sim_type');   
            $product_result->needs              = $request->input('needs');   
            $product_result->total_point        = $request->input('total_point');
            $product_result->repetition         = $product_result->repetition + 1;

            // menentukan gagal dan lolos dari test
            if ($request->input('total_point') > $product->max_point_result) {
                $product_result->status = 'LULUS';
                $message = 'Selamat Anda telah lulus dalam tes Psikotes!';
            } else {
                $product_result->status = 'GAGAL';
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
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on updated result data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function show($id) {
        $result = ProductResult::with('payment')->where('id', $id)->first();
        $result->category = Category::where('id', $result->payment->category_id)->first();
    
        if (!$result) {
            $response = [
                'status' => 404,
                'message' => 'Hasil test tidak ditemukan!',
            ];
            return response()->json($response, 404);
        }

        $response = [
            'status' => 200,
            'data' => $result,
        ];
        return response()->json($response, 200);
    }

    public function selectedDelete(Request $request) {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $selected_delete = ProductResult::whereIn('id', $request->input('data'));

            if ($selected_delete->delete()) {
                $response = [
                    'status' => 200,
                    'message' => 'Product data has been deleted',
                ];
    
                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating paket pekerjaan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function selectedExportExcel(Request $request) {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $selected_delete = ProductResult::whereIn('product_results.id', $request->input('data'))
            ->join('users', 'users.id', '=', 'product_results.user_id')
            ->join('products', 'products.id', '=', 'product_results.product_id')
            ->join('product_payments', 'product_payments.id', '=', 'product_results.product_payment_id')
            ->join('categories', 'categories.id', '=', 'product_payments.category_id')
            ->select(
                'categories.name as category', 'users.full_name', 'products.name', 'product_payments.no_transaction', 'product_results.full_name as nama', 'product_results.nik','product_results.work', 'product_results.address', 'product_results.sim_type', 'product_results.needs', 'product_results.total_point', 'product_results.status', 'product_results.expired_at'
            )->get();
            Excel::store(new ProductResultExport($selected_delete), 'Result.xlsx');
        return response()->download(storage_path("app/Result.xlsx"), "Result.xlsx", ["Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS"]);
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating paket pekerjaan data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function selectedExportPdf(Request $request) {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $selected = ProductResult::whereIn('product_results.id', $request->input('data'))
            ->join('users', 'users.id', '=', 'product_results.user_id')
            ->join('products', 'products.id', '=', 'product_results.product_id')
            ->join('product_payments', 'product_payments.id', '=', 'product_results.product_payment_id')
            ->join('categories', 'categories.id', '=', 'product_payments.category_id')
            ->select(
                'categories.name as category', 'users.full_name', 'products.name', 'product_payments.no_transaction', 'product_results.full_name as nama', 'product_results.nik', 'product_results.work', 'product_results.address', 'product_results.sim_type', 'product_results.needs', 'product_results.total_point', 'product_results.status', 'product_results.expired_at'
            )->get();

            if ($selected) {
                $response = [
                    'status' => 200,
                    'message' => 'Product data has been retrieved',
                    'data' => $selected
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating result data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
