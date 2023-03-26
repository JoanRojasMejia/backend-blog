<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth:api');
  }

  // Read Article ------------------------------------ ->

  public function index()
  {
    $users = Category::all();
    return response()->json([
      "users" => $users,
      "code" => 200,
    ], Response::HTTP_OK);
  }

  // Read Article ------------------------------------ <-

  // Create Article ------------------------------------ ->

  public function save(Request $request)
  {

    $validator = $this->validateCategory($request);

    if ($validator->fails()) {
      return response()->json([
        'error_validation' => $validator->errors()->toJson()
      ], Response::HTTP_NOT_ACCEPTABLE);
    }

    $this->createAndSaveCategory($request);

    return response()->json([
      'message' => 'Category created',
      'code' => 200
    ], Response::HTTP_CREATED);
  }

  private function validateCategory(Request $request)
  {
    $validator  = Validator::make($request->all(), [
      'name' => 'required|string|max:50|unique:category',
    ]);

    return $validator;
  }

  private function createAndSaveCategory(Request $request)
  {
    $category = new Category();

    $category->name = $request->name;
    $category->creation_date = now()->format('d-m-Y');
    $category->update_date = now()->format('d-m-Y');

    $category->save();
  }

  // Create Article ------------------------------------ <-

  // Create Article ------------------------------------ ->

  public function delete($id)
  {
    $category = Category::find($id);

    if (is_null($category)) {
      return response()->json([
        'message' => 'Category not exist',
        'code' => 400
      ], Response::HTTP_NOT_ACCEPTABLE);
    }

    Category::destroy($id);

    return response()->json([
      'message' => 'Category deleted',
      'code' => 200
    ], Response::HTTP_OK);
  }

  // Create Article ------------------------------------ <-
}
