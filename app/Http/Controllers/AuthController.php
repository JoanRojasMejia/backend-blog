<?php

namespace App\Http\Controllers;

// use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
// use JWTAuth;

class AuthController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth:api', ['except' => ['login', 'register']]);
  }

  // Login User ------------------------------------ ->

  public function login(Request $request)
  {
    $credentials = $request->only('email', 'password');

    if (!$token = auth()->attempt($credentials)) {
      return response()->json([
        'mesaage' => 'Unauthorized',
        "code" => 401
      ], Response::HTTP_UNAUTHORIZED);
    }

    return $this->respondWithToken($token);
  }

  protected function respondWithToken($token)
  {
    return response()->json([
      'access_token' => $token,
      'token_type' => 'bearer',
      'expires_in' => auth()->factory()->getTTL() * 120
    ]);
  }

  // Login User ------------------------------------ <-

  // Register User ------------------------------------ ->

  public function register(Request $request)
  {

    $validator = $this->validateUser($request);
    if ($validator->fails()) {
      return response()->json([
        'error_validation' => $validator->errors()->toJson()
      ], Response::HTTP_NOT_ACCEPTABLE);
    }

    $this->createAndSaveUser($request);
    return response()->json([
      'message' => 'User created',
      'code' => 200
    ], Response::HTTP_CREATED);
  }

  private function validateUser(Request $request)
  {

    $validator  = Validator::make($request->all(), [
      'name' => 'required|string',
      'email' => 'required|string|email|max:100|unique:user',
      'password' => 'required|string|min:6|confirmed',
      'phone' => 'required|string'
    ]);

    return $validator;
  }

  private function createAndSaveUser(Request $request)
  {
    $user = new User();

    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = bcrypt($request->password);
    $user->phone = $request->phone;

    $user->type = 2;
    $user->creation_date = now()->format('d-m-Y');
    $user->update_date = now()->format('d-m-Y');

    $user->save();
  }

  // Register User ------------------------------------ <-

  public function logout()
  {
    auth()->logout();
    return response()->json([
      'message' => 'Successfully logged out',
      'code' => 200
    ]);
  }

  // public function me()
  // {
  //   return response()->json(auth()->user());
  // }

  // public function refresh()
  // {
  //   return $this->respondWithToken(auth()->refresh());
  // }
}
