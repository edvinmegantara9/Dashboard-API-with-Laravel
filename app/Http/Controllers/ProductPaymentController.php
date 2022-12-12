<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            $data = ProductPayment::orderBy('product_payments.' . $sortby, $sorttype)
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
                'message' => 'product payment data has been created',
                'data' => $product_payment,
                'snap_token' => $snap_token,
                'payment_url' => env('MIDTRANS_URL') . 'snap/v2/vtweb/'. $snap_token,
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

    public function snapPayment($product_payment) {
        // Set your Merchant Server Key
        \Midtrans\Config::$serverKey = env('MIDTRANS_SECRET_KEY');
        // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = false;
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
            if ($status_code == 200) {
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
                    $product_payment->payment_channel = $request->va_numbers[0]->bank;
                    $product_payment->note = $request->va_numbers[0]->va_number;
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
                        'message' => 'Berhasil berhasil singkronisasi!',
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
            $message = $e->getMessage();
            return json_encode(array('status' => 400, 'message' => $message));
        }
    }
}
