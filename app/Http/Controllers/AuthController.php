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
            'nip' => 'required|string|unique:users',
            'password' => 'required',
            'position' => 'required',
            'group' => 'required',
            'role_id' => 'required',
            'email' => 'required'
        ]);

        try 
        {
            $user = new Users;
            $user->nip= $request->input('nip');
            $user->full_name = $request->input('full_name');
            $user->password = app('hash')->make($request->input('password'));
            $user->position = $request->input('position');
            $user->group = $request->input('group');
            $user->email = $request->input('email');
            $user->role_id = $request->input('role_id');
            $user->save();

            return response()->json( [
                        'entity' => 'user', 
                        'action' => 'create', 
                        'result' => 'success'
            ], 201);

        } 
        catch (\Exception $e) 
        {
            return response()->json( [
                       'entity' => 'user', 
                       'action' => 'create', 
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
            'nip' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['nip', 'password']);
        $nip = $request->input('nip');
        $password = $request->input('password');
        $user = Users::with('role')->where('nip', $nip)->first();
    
        if ($user) {
          if (Hash::check($password, $user->password)) {
            // // $data = [
            // //   'id' => $user->id,
            // //   'email' => $user->email,
            // //   'type' => $user->type,
            // //   'exp' => Carbon::now()->addDays(7)->timestamp
            // // ];
            $user->role->opd;
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
	
     /**
     * Get user details.
     *
     * @param  Request  $request
     * @return Response
     */	 	
    public function me()
    {   
        $user = Users::find(Auth::id)->with('role');
        return response()->json($user);
    }
}