<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Users;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register']]);
    }

    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request)
    {
        //validate incoming request 
        $this->validate($request, [
            'full_name'     => 'required',
            'email'         => 'required|string|email|unique:users', 
            'password'      => 'required|confirmed|min:6',
            'phone_number'  => 'required|string|unique:users',
        ],[
          'required' => 'Data :attribute harus diisi'
        ]);

        try {
            $user = new Users;
            $user->full_name    = $request->input('full_name');
            $user->email        = $request->input('email');
            $user->password     = app('hash')->make($request->input('password'));
            $user->phone_number = $request->input('phone_number');
            $user->save();

            return response()->json( [
                        'status'  => '201', 
                        'message' => 'Anda berhasil mendaftar, selanjutnya akan mengarahkan ke halaman login'
            ], 201);

        } 
          catch (\Exception $e) 
        {
            return response()->json( [
                       'status' => 409,
                       'result' => 'Anda gagal mendaftar',
                       'message' => $e
            ], 409);
        }
    }
	
     /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */	 
    public function login(Request $request)
    {
          //validate incoming request 
        $this->validate($request, [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ],[
          'required' => 'Data :attribute harus diisi'
        ]);

        $credentials = $request->only(['email', 'password']);
        $email = $request->input('email');
        $password = $request->input('password');
        $user = Users::where('email', $email)->first();
    
        if ($user) {
          if (Hash::check($password, $user->password)) {
            if (!$token = Auth::attempt($credentials)) {
              return response()->json(
                [ 'status' => 401,
                  'message' => 'Unauthorized']
                , 401);
            }
            return $this->respondWithToken($token, $user);
          } else {
            return response()->json([
              'status' => 400,
              'message' => 'Login gagal, pastikan email & password benar'
            ], 400);
          }
        } else {
          return response()->json([
            'status' => 400,
            'message' => 'Login gagal, pastikan email & password benar',
          ], 400);
        }
    }

    public function respondWithToken($token, $user)
    {
        return response()->json([
            'status' => 200,
            'message' => 'login berhasil',
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => null,
            'user' => $user
        ], 200);
    }
	
     /**
     * Get user details.
     *
     * @param  Request  $request
     * @return Response
     */	 	
    public function me()
    {   
        $user = Users::find(Auth::id());
        return response()->json($user);
    }

    /**
    * Request an email verification email to be sent.
    *
    * @param  Request  $request
    * @return Response
    */
    public function emailRequestVerification(Request $request)
    {
      if ( $request->user()->hasVerifiedEmail() ) {
          return response()->json([
            'status'  => 200,
            'message' => 'Email sudah terverifikasi'
          ]);
      }
      
      $request->user()->sendEmailVerificationNotification();
      
      return response()->json([
        'status'  => 200,
        'message' => 'Email permintaan verifikasi dikirim ke '. Auth::user()->email
      ]);
    }

  /**
  * Verify an email using email and token from email.
  *
  * @param  Request  $request
  * @return Response
  */
  public function emailVerify(Request $request)
  {
    $this->validate($request, [
      'token' => 'required|string',
    ]);

    \Tymon\JWTAuth\Facades\JWTAuth::getToken();
    \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
    if (!$request->user() ) {
        return response()->json([
          'status'  => 401,
          'message' => 'Invalid token',
        ], 401);
      }
      
      if ( $request->user()->hasVerifiedEmail() ) {
        return response()->json([
          'status' => 200, 
          'message' => 'Email '.$request->user()->getEmailForVerification().' sudah terverifikasi.'
        ], 200);
      }
      $request->user()->markEmailAsVerified();
      return response()->json([
        'status'  => 201,
        'message' => 'Email '. $request->user()->email.' sukses terverifikasi.'
      ], 201);
    }
}