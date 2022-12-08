<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
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
            $data = Product::with('product_detail')->orderBy('products.' . $sortby, $sorttype)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('products.name', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'products data has been retrieved',
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
            'name'         => 'required',
            'amount'       => 'required',
            'expired_time' => 'required|integer',
            'product_details' => 'required|array|min:1',
            'product_details.*.question'   => 'required',
            'product_details.*.answer_correct' => 'required',
            'product_details.*.point' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $product = new Product;
			$product->name         = $request->input('name');
            $product->amount       = $request->input('amount');
            $product->expired_time = $request->input('expired_time');

            if ($product->save()) {
				foreach ($request->get('product_details') as $d) {
					$detail = new ProductDetails;
					$detail->product_id     = $product->id;
					$detail->question       = $d['question'];
                    $detail->answer_correct = $d['answer_correct'];
                    $detail->point          = $d['point'];
					$detail->save();
				}
            }

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'product data has been created',
                'data' => $product
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
        try {
            DB::beginTransaction();

            $product = Product::find($id);

            if ($product) {
                $product->name         = !empty($request->input('name')) ? $request->input('name') : $product->name;
                $product->amount       = !empty($request->input('amount')) ? $request->input('amount') : $product->amount;
                $product->expired_time = !empty($request->input('expired_time')) ? $request->input('expired_time') : $product->expired_time;

                if ($product->save()) {
                    ProductDetails::where("product_id", $product->id)->delete();

                    foreach ($request->get('product_details') as $d) {
                        $detail = new ProductDetails;
                        $detail->product_id     = $product->id;
                        $detail->question       = $d['question'];
                        $detail->answer_correct = $d['answer_correct'];
                        $detail->point          = $d['point'];
                        $detail->save();
                    }
                }

                DB::commit();

                $response = [
                    'status' => 200,
                    'message' => 'product data has been updated',
                    'data' => $product
                ];

                return response()->json($response, 200);
            }
            $response = [
                'status' => 404,
                'message' => 'product data not found',
            ];

            return response()->json($response, 404);

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

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $product = Product::findOrFail($id);
            if (!$product) {
                $response = [
                    'status' => 404,
                    'message' => 'product data not found',
                ];
                return response()->json($response, 404);
            }

            ProductDetails::where("product_id", $product->id)->delete();

            if (!$product->delete()) {
				DB::rollBack();
				$response = [
                    'status'  => 401,
					'message' => 'Error during delete',
				];
				return response()->json($response, 401);
			}

            DB::commit();
            
            $response = [
                'status' => 200,
                'message' => 'product data has been deleted',
            ];
            return response()->json($response, 200);
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
}
