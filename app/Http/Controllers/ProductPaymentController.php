<?php

namespace App\Http\Controllers;

use App\Exports\ProductPaymentExport;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ProductPaymentController extends Controller
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
            $data = ProductPayment::with('product_result')->orderBy('product_payments.' . $sortby, $sorttype)
                ->where('user_id', $user_id)
                // ->whereIn('status', $status)
                ->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('product_payments.no_transaction', 'LIKE', '%' . $keyword . '%');
                })
                ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'product_payments data has been retrieved',
                    'data' => $data
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving payment data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function getAdmin(Request $request)
    {
        $row      = $request->input('row');
        $keyword  = $request->input('keyword');
        $sortby   = $request->input('sortby');
        $sorttype = $request->input('sorttype');
        $firstdate = $request->input('firstdate');
        $lastdate  = $request->input('lastdate');
        $category  = $request->input('category');
        $status    = $request->input('status');

        if ($keyword == 'null') $keyword = '';
        $keyword = urldecode($keyword);

        try {
            $data = ProductPayment::with('product_result', 'user', 'category', 'product')
                    ->whereBetween('product_payments.created_at', [date('Y-m-d 00:00:00', strtotime($firstdate)), date('Y-m-d 23:59:59', strtotime($lastdate))])
                    ->orderBy('product_payments.' . $sortby, $sorttype);
                
            if ($category !== "") {
                $data = $data->where('category_id', $category);
            }

            if ($status !== "") {
                $data = $data->where('status', $status);
            }
            
            $data = $data->when($keyword, function ($query) use ($keyword) {
                    return $query
                        ->where('product_payments.no_transaction', 'LIKE', '%' . $keyword . '%');
                    })
                    ->paginate($row);

            if ($data) {
                $response = [
                    'status' => 200,
                    'message' => 'product_payments data has been retrieved',
                    'data' => $data
                ];

                return response()->json($response, 200);
            }
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on retrieving payment data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'product_id'   => 'required',
            'category_id'  => 'required',
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

            $category = Category::where('id', $request->input('category_id'))->first();
            if (!$product) {
                $response = [
                    'status' => 404,
                    'message' => 'category not found',
                ];
                return response()->json($response, 404);
            }

            $product_payment = new ProductPayment;
			$product_payment->no_transaction = autonumber();
            $product_payment->user_id        = Auth::user()->id;
            $product_payment->product_id     = $product->id;
            $product_payment->category_id    = $category->id;
            $product_payment->amount         = $category->amount;
            $product_payment->status         = 'pending'; // default status 0 => PENDING
            $product_payment->save();

            // generate snap token
            $snap_token = $this->snapPayment($product_payment);

            DB::commit();

            $response = [
                'status' => 201,
                'message' => 'Transaksi pembayaran berhasil dibuat!',
                'data' => $product_payment,
                'snap_token' => $snap_token,
                'payment_url' => env('MIDTRANS_URL') . 'snap/v2/vtweb/'. $snap_token,
            ];

            return response()->json($response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'Gagal membuat transaksi pembayaran!',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function snapPayment($product_payment) {
        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = env('MIDTRANS_SECRET_KEY');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        // Set sanitization on (default)
        \Midtrans\Config::$isSanitized = true;
        // Set 3DS transaction for credit card to true
        \Midtrans\Config::$is3ds = true;
        
        $params = array(
            'transaction_details' => array(
                'order_id' => $product_payment->no_transaction,
                'gross_amount' => $product_payment->amount,
            ),
            'customer_details' => array(
                'first_name' => Auth::user()->full_name,
                'last_name' => '',
                'email' => Auth::user()->email,
                'phone' => Auth::user()->phone_number,
            ),
            'enabled_payments' => array(
                "permata_va", "bca_va", "bni_va", "bri_va", "other_va", "gopay", "alfamart", "indomaret", "shopeepay")
        );

        return \Midtrans\Snap::getSnapToken($params);
    }

    public function callback(Request $request) {
        // check signature key
        // SHA512(order_id+status_code+gross_amount+ServerKey)
        $order_id     = $request->order_id;
        $status_code  = $request->status_code;
        $gross_amount = $request->gross_amount;
        $serverKey    = env('MIDTRANS_SECRET_KEY'); 
        $fraud_status = !empty($request->fraud_status) ? $request->fraud_status : '';
        $signature    = hash('sha512', $order_id.$status_code.$gross_amount.$serverKey);
     
        if ($signature == $request->signature_key) {
            if ($status_code == 200 || $status_code == 201) {
                $product_payment = ProductPayment::where('no_transaction', $order_id)->first();

                if (!$product_payment) {
                    $response = [
                        'status' => 404,
                        'message' => 'Transaction tidak ditemukan!',
                    ];
                    return response()->json($response, 404);
                }

                $product_payment->payment_method  = $request->payment_type;
            
                if ($request->va_numbers) {
                    $product_payment->payment_channel = $request->va_numbers[0]['bank'];
                    $product_payment->note = $request->va_numbers[0]['va_number'];
                } else if ($request->store) {
                    $product_payment->payment_channel = $request->store;
                    $product_payment->note = $request->payment_code; 
                } else {
                    $product_payment->payment_channel = "-";
                    $product_payment->note = "-";
                }
               
                switch ($request->transaction_status) {
                    case 'capture':
                        if ($fraud_status == 'challenge'){
                            // TODO set transaction status on your database to 'challenge'
                            // and response with 200 OK
                            $product_payment->status = 'challenge';
                            $product_payment->save();
                        } else if ($fraud_status == 'accept'){
                            // TODO set transaction status on your database to 'success'
                            // and response with 200 OK
                            $product_payment->status = 'success';
                            $product_payment->save();
                        }
                        break;
                    case 'settlement':
                        $product_payment->status = 'success';
                        $product_payment->save();
                        break;
                    case 'pending':
                        $product_payment->status = 'pending';
                        $product_payment->save();
                        break;
                    case 'cancel':
                        $product_payment->status = 'cancel';
                        $product_payment->save();
                        break;
                    case 'expire':
                        $product_payment->status = 'expire';
                        $product_payment->save();
                        break;
                    case 'refund':
                        $product_payment->status = 'refund';
                        $product_payment->save();
                        break;
                    case 'partial_refund':
                        $product_payment->status = 'partial_refund';
                        $product_payment->save();
                        break;
                    default:
                        $product_payment->status = 'pending';
                        $product_payment->save();
                        break;
                }

                $response = [
                    'status' => 201,
                    'message' => 'Callback berhasil!',
                ];
                return response()->json($response, 201);
            }
        } else {
            $response = [
                'status' => 404,
                'message' => 'Callback tidak dikenali!',
            ];
            return response()->json($response, 404);
        }
    }

    public function checkStatus($no_transaction)
    {
        try {
            $client = new \GuzzleHttp\Client();
            $headers = [
                'Content-Type'  => 'application/json', 
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode(env('MIDTRANS_SECRET_KEY').":")
            ];
            $send = [
                'headers'   => $headers,
            ];
    
            $result = $client->get(env('MIDTRANS_URL_API').$no_transaction."/status", $send)->getBody()->getContents();
            $request = json_decode($result);

            $order_id     = $request->order_id;
            $status_code  = $request->status_code;
            $gross_amount = $request->gross_amount;
            $serverKey    = env('MIDTRANS_SECRET_KEY'); 
            $fraud_status = !empty($request->fraud_status) ? $request->fraud_status : '';
            $signature    = hash('sha512', $order_id.$status_code.$gross_amount.$serverKey);
    
            if ($signature == $request->signature_key) {
                if ($status_code == 200 || $status_code == 201) {
                    $product_payment = ProductPayment::where('no_transaction', $order_id)->first();
    
                    if (!$product_payment) {
                        $response = [
                            'status' => 404,
                            'message' => 'Transaction tidak ditemukan!',
                        ];
                        return response()->json($response, 404);
                    }
    
                    $product_payment->payment_method  = $request->payment_type;
                
                    if (!empty($request->va_numbers)) {
                        $product_payment->payment_channel = $request->va_numbers[0]->bank;
                        $product_payment->note = $request->va_numbers[0]->va_number;
                    } else if (!empty($request->store)) {
                        $product_payment->payment_channel = $request->store;
                        $product_payment->note = $request->payment_code; 
                    } else {
                        $product_payment->payment_channel = "-";
                        $product_payment->note = "-";
                    }
                   
                    switch ($request->transaction_status) {
                        case 'capture':
                            if ($fraud_status == 'challenge'){
                                // TODO set transaction status on your database to 'challenge'
                                // and response with 200 OK
                                $product_payment->status = 'challenge';
                                $product_payment->save();
                            } else if ($fraud_status == 'accept'){
                                // TODO set transaction status on your database to 'success'
                                // and response with 200 OK
                                $product_payment->status = 'success';
                                $product_payment->save();
                            }
                            break;
                        case 'settlement':
                            $product_payment->status = 'success';
                            $product_payment->save();
                            break;
                        case 'pending':
                            $product_payment->status = 'pending';
                            $product_payment->save();
                            break;
                        case 'cancel':
                            $product_payment->status = 'cancel';
                            $product_payment->save();
                            break;
                        case 'expire':
                            $product_payment->status = 'expire';
                            $product_payment->save();
                            break;
                        case 'refund':
                            $product_payment->status = 'refund';
                            $product_payment->save();
                            break;
                        case 'partial_refund':
                            $product_payment->status = 'partial_refund';
                            $product_payment->save();
                            break;
                        default:
                            $product_payment->status = 'pending';
                            $product_payment->save();
                            break;
                    }
    
                    $response = [
                        'status' => 201,
                        'message' => 'Berhasil singkronisasi!',
                    ];
                    return response()->json($response, 201);
                }
            } else {
                $response = [
                    'status' => 404,
                    'message' => 'Callback tidak dikenali!',
                ];
                return response()->json($response, 404);
            }

            
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $message = $e->getMessage();
            return json_encode(array('status' => 400, 'message' => $message));
        }
    }

    public function  statusSnap(Request $request) {

        $order_id     = $request->order_id;
        $status_code  = $request->status_code;
        $fraud_status = !empty($request->fraud_status) ? $request->fraud_status : '';
     
            if ($status_code == 200 || $status_code == 201) {
                $product_payment = ProductPayment::where('no_transaction', $order_id)->first();

                if (!$product_payment) {
                    $response = [
                        'status' => 404,
                        'message' => 'Transaction tidak ditemukan!',
                    ];
                    return response()->json($response, 404);
                }

                $product_payment->payment_method  = $request->payment_type;
            
                if ($request->va_numbers) {
                    $product_payment->payment_channel = $request->va_numbers[0]['bank'];
                    $product_payment->note = $request->va_numbers[0]['va_number'];
                } else if ($request->store) {
                    $product_payment->payment_channel = $request->store;
                    $product_payment->note = $request->payment_code; 
                } else {
                    $product_payment->payment_channel = "-";
                    $product_payment->note = "-";
                }
               
                switch ($request->transaction_status) {
                    case 'capture':
                        if ($fraud_status == 'challenge'){
                            // TODO set transaction status on your database to 'challenge'
                            // and response with 200 OK
                            $product_payment->status = 'challenge';
                            $product_payment->save();
                        } else if ($fraud_status == 'accept'){
                            // TODO set transaction status on your database to 'success'
                            // and response with 200 OK
                            $product_payment->status = 'success';
                            $product_payment->save();
                        }
                        break;
                    case 'settlement':
                        $product_payment->status = 'success';
                        $product_payment->save();
                        break;
                    case 'pending':
                        $product_payment->status = 'pending';
                        $product_payment->save();
                        break;
                    case 'cancel':
                        $product_payment->status = 'cancel';
                        $product_payment->save();
                        break;
                    case 'expire':
                        $product_payment->status = 'expire';
                        $product_payment->save();
                        break;
                    case 'refund':
                        $product_payment->status = 'refund';
                        $product_payment->save();
                        break;
                    case 'partial_refund':
                        $product_payment->status = 'partial_refund';
                        $product_payment->save();
                        break;
                    default:
                        $product_payment->status = 'pending';
                        $product_payment->save();
                        break;
                }

                $response = [
                    'status' => 201,
                    'message' => 'Berhasil singkronisasi!',
                ];
                return response()->json($response, 201);
            }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $product_payment = ProductPayment::findOrFail($id);
            if (!$product_payment) {
                $response = [
                    'status' => 404,
                    'message' => 'payment data not found',
                ];
                return response()->json($response, 404);
            }

            if (!$product_payment->delete()) {
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
                'message' => 'payment data has been deleted',
            ];
            return response()->json($response, 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on deleting payment data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }

    public function selectedDelete(Request $request) {
        $this->validate($request, [
            'data' => 'required'
        ]);

        try {
            $selected_delete = ProductPayment::whereIn('id', $request->input('data'));

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
                'message' => 'error occured on creating payment data',
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
            $selected_delete = ProductPayment::whereIn('product_payments.id', $request->input('data'))
            ->join('users', 'users.id', '=', 'product_payments.user_id')
            ->join('products', 'products.id', '=', 'product_payments.product_id')
            ->join('categories', 'categories.id', '=', 'product_payments.category_id')
            ->select(
                'categories.name as category', 'product_payments.no_transaction', 'users.full_name', 'products.name', 'product_payments.amount', 'product_payments.payment_method', 'product_payments.payment_channel', 'product_payments.status', 'product_payments.note'
            )->get();
            Excel::store(new ProductPaymentExport($selected_delete), 'Transaction.xlsx');
        return response()->download(storage_path("app/Transaction.xlsx"), "Transaction.xlsx", ["Access-Control-Allow-Origin" => "*", "Access-Control-Allow-Methods" => "GET, POST, PUT, DELETE, OPTIONS"]);
        } catch (\Exception $e) {
            \Sentry\captureException($e);
            $response = [
                'status' => 400,
                'message' => 'error occured on creating payment data',
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
            $selected = ProductPayment::whereIn('product_payments.id', $request->input('data'))
            ->join('users', 'users.id', '=', 'product_payments.user_id')
            ->join('products', 'products.id', '=', 'product_payments.product_id')
            ->join('categories', 'categories.id', '=', 'product_payments.category_id')
            ->select(
                'categories.name as category', 'product_payments.no_transaction', 'users.full_name', 'products.name', 'product_payments.amount', 'product_payments.payment_method', 'product_payments.payment_channel', 'product_payments.status', 'product_payments.note'
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
                'message' => 'error occured on creating payment data',
                'error' => $e->getMessage()
            ];
            return response()->json($response, 400);
        }
    }
}
