<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class UserController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth:api');
  }

  private $fields = array(
    'name',
    'email',
    'phone',
    'type',
  );

  // Read Article ------------------------------------ ->

  public function index()
  {

    if (auth()->user()->type !== 1) {
      return response()->json([
        'message' => 'user not authorizated'
      ], Response::HTTP_UNAUTHORIZED);
    }

    $categories = User::all();
    return response()->json([
      "categories" => $categories,
      "code" => 200,
    ], Response::HTTP_OK);
  }

  // Read Article ------------------------------------ <-

  // Update User ------------------------------------ ->

  public function update(Request $request, $id)
  {

    $currentTypeUser = auth()->user()->type;
    if ($currentTypeUser !== 1) {
      return response()->json([
        'message' => 'user not authorizated'
      ], Response::HTTP_UNAUTHORIZED);
    }

    $updatedUser = array();
    $user = User::find($id);

    foreach ($this->fields as $field) {
      if (!is_null($request->$field)) {
        $updatedUser[$field] =  $request->$field;
        continue;
      }
      $updatedUser[$field] = $user->$field;
    }

    $updatedUser['update_date'] = now()->format('d-m-Y');

    User::findOrFail($id)->update($updatedUser);
    return response()->json([
      'message' => 'OK',
      'user' => $updatedUser,
      'code' => 200
    ], Response::HTTP_OK);
  }

  // Update User ------------------------------------ <-

  // Delete User ------------------------------------ ->

  public function deleteUser($id)
  {

    $currentTypeUser = auth()->user()->type;
    if ($currentTypeUser !== 1) {
      return response()->json([
        'message' => 'user not authorizated'
      ], Response::HTTP_UNAUTHORIZED);
    }

    User::destroy($id);
    return response()->json([
      'message' => 'user deleted', 'code' => 200
    ], Response::HTTP_OK);
  }

  // Delete User ------------------------------------ <-
}
