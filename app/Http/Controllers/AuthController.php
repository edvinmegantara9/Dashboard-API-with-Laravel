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
            'nik'           => 'required|string|unique:users',
            'phone_number'  => 'required',
            'age'           => 'required|integer',
            'work'          => 'required',
            'address'       => 'required'
        ]);

        try {
            $user = new Users;
            $user->full_name    = $request->input('full_name');
            $user->email        = $request->input('email');
            $user->password     = app('hash')->make($request->input('password'));
            $user->nik          = $request->input('nik');
            $user->phone_number = $request->input('phone_number');
            $user->age          = $request->input('age');
            $user->work         = $request->input('work');
            $user->address      = $request->input('address');
            $user->save();

            return response()->json( [
                        'status'  => '201', 
                        'message' => 'success'
            ], 201);

        } 
          catch (\Exception $e) 
        {
            return response()->json( [
                       'status' => 409,
                       'result' => 'failed',
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
              'message' => 'Login Fail!'
            ], 400);
          }
        } else {
          return response()->json([
            'status' => 400,
            'message' => 'Login Fail!',
          ], 400);
        }
    }

    public function respondWithToken($token, $user)
    {
        return response()->json([
            'status' => 200,
            'message' => 'login successful',
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => null,
            // 'user' => $user
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
            'message' => 'Email address is already verified.'
          ]);
      }
      
      $request->user()->sendEmailVerificationNotification();
      
      return response()->json([
        'status'  => 200,
        'message' => 'Email request verification sent to '. Auth::user()->email
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
          'message' => 'Email address '.$request->user()->getEmailForVerification().' is already verified.'
        ], 200);
      }
      $request->user()->markEmailAsVerified();
      return response()->json([
        'status'  => 201,
        'message' => 'Email address '. $request->user()->email.' successfully verified.'
      ], 201);
    }
}