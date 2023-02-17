<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AuthController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login','register', 'submitForgetPasswordForm', 'emailForgetPassword',]]);
    }

    /**
     * Store a new user.
     *
     * @param  Request  $request
     * @return Response
     */
    public function register(Request $request)
    {
        DB::beginTransaction();
        
        //validate incoming request 
        $this->validate($request, [
            'full_name'     => 'required',
            'nip'           => 'required|string|unique:users', 
            'email'         => 'required|string|email|unique:users', 
            'password'      => 'required|confirmed|min:6',
            'role_id'       => 'required', 
        ],[
          'required' => 'Data :attribute harus diisi'
        ]);

        try {
            $user = new User;
            $user->full_name    = $request->input('full_name');
            $user->nip          = $request->input('nip');
            $user->email        = $request->input('email');
            $user->password     = app('hash')->make($request->input('password'));
            $user->role_id      = $request->input('role_id');
            $user->save();

            DB::commit();

            return response()->json( [
                'status'  => '201', 
                'message' => 'User berhasil di daftarkan!!'
            ], 201);

        } 
          catch (\Exception $e) 
        {
            DB::rollBack();
            \Sentry\captureException($e);
            return response()->json( [
                       'status' => 409,
                       'result' => 'Gagal mendaftar User',
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
            'nip'      => 'required|string',
            'password' => 'required|string',
        ],[
          'required' => 'Data :attribute harus diisi'
        ]);

        $credentials = $request->only(['nip', 'password']);
        
        $nip = $request->input('nip');
        $password = $request->input('password');
        $user = User::where('nip', $nip)->first();

        if ($user) {
          if (Hash::check($password, $user->password)) {
            if (!$token = Auth::attempt($credentials)) {
              return response()->json(
                [ 'status' => 401,
                  'message' => 'Sesi telah berakhir, silahkan untuk login kembali!']
                , 401);
            }
            return $this->respondWithToken($token, $user);
          } else {
            return response()->json([
              'status' => 400,
              'message' => 'Login gagal, pastikan nip & password benar'
            ], 400);
          }
        } else {
          return response()->json([
            'status' => 400,
            'message' => 'Login gagal, pastikan nip & password benar',
          ], 400);
        }
    }

    /**
     * Fungsi untuk merubah password
     */
    public function changePassword(Request $request) {

      $this->validate($request, [
          'old_password' => 'required',
          'password' => 'required|confirmed|min:6',
      ],[
          'required' => 'Data :attribute harus diisi'
      ]);

      $old_password = $request->input('old_password');
      $new_password = $request->input('password');

      $auth = Auth::user()->id;
      $user = User::where('id', $auth)->first();
      if ($user) {
          if (Hash::check($old_password, $user->password)) {
              $password = Hash::make($new_password);
              $user->password = $password;
              if (!$user->update()) {
                  return response()->json([
                      'status' => 404,
                      'message' => 'Gagal mengganti password!'
                  ], 404);
              }

              $response = [
                  'status' => 201,
                  'message' => 'Password berhasil diganti!',
              ];

              return response()->json($response, 201);

          } else {
              return response()->json([
                  'status'  => 400,
                  'success' => false,
                  'message' => 'Password lama tidak sesuai!',
              ], 400);
          }
      } else {
          return response()->json([
              'status'  => 404,
              'success' => false,
              'message' => 'User tidak ditemukan!',
              'data' => ''
          ], 404);
      }
    }
	
     /**
     * Get user details.
     *
     * @param  Request  $request
     * @return Response
     */	 	
    public function me()
    {   
        $user = User::find(Auth::id());
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
     * Fungsi mengirim email untuk reset password
     */
    public function emailForgetPassword(Request $request)
    {      
      $this->validate($request, [
        'nip' => 'required',
      ],[
          'required' => 'Data :attribute harus diisi'
      ]);

      $nip = $request->input('nip');
      $user = User::where('nip', $nip)->first();

      if ($user) {
        $user->sendForgetPasswordNotification();
        return response()->json([
          'status'  => 200,
          'message' => 'Email permintaan reset password dikirim ke '. $user->email
        ]);
      } else {
        return response()->json([
          'status' => 400,
          'message' => 'Email tidak terdaftar',
        ], 400);
      }
    }

    /**
     * Fungsi untuk membuat password baru dari token yang di kirim melalui email
     */
    public function submitEmailResetPassword(Request $request) {
      $this->validate($request, [
        'token' => 'required|string',
        'password' => 'required|confirmed|min:6',
      ]);

      \Tymon\JWTAuth\Facades\JWTAuth::getToken();
      \Tymon\JWTAuth\Facades\JWTAuth::parseToken()->authenticate();
      if (!$request->user() ) {
        return response()->json([
          'status'  => 401,
          'message' => 'Invalid token',
        ], 401);
      }

      $user = User::where('id', Auth::user()->id)->first();
      $user->password     = app('hash')->make($request->input('password'));
      $user->save();

      return response()->json([
        'status'  => 201,
        'message' => 'Berhasil mereset password, silahkan login ulang'
      ], 201);
    }

    /**
    * Verify an email using email and token from email.
    *
    * @param  Request  $request
    * @return Response
    */
    public function emailVerify(Request $request) {
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
          'message' => 'Email '.$request->user()->getEmailForVerification().' sudah terverifikasi.',
          'data' => Auth::user()
        ], 200);
      }
      $request->user()->markEmailAsVerified();
      return response()->json([
        'status'  => 200,
        'message' => 'Email '. $request->user()->email.' sukses terverifikasi.',
        'data' => Auth::user()
      ], 200);
    }

    /**
     * Fungsi untuk memanggil link form untuk mereset password dengan mengirimkan token
     */
    public function emailResetPassword(Request $request) {
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

      return response()->json([
        'status'  => 201,
        'message' => 'nanti muncul link form reset disni.'
      ], 201);
    }

    public function respondWithToken($token, $user)
    {
      $user->roles->menus;

      return response()->json([
          'status' => 200,
          'message' => 'login berhasil',
          'token' => $token,
          'token_type' => 'bearer',
          'expires_in' => null,
          'user' => $user
      ], 200);
    }
}