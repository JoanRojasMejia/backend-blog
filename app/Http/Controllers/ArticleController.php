<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
  //

  public function __construct()
  {
    $this->middleware('auth:api');
  }

  private $fields = array(
    'id_category',
    'title',
    'slug',
    'small_text',
    'long_text',
    'url_image'
  );

  // Read Article ------------------------------------ ->

  public function index()
  {
    $acticle = Article::all();
    return response()->json([
      "articles" => $acticle,
      "code" => 200
    ], Response::HTTP_OK);
  }

  // Read Article ------------------------------------ <-

  // Create Article ------------------------------------ ->

  public function save(Request $request)
  {
    $validator = $this->validateArticle($request);

    if ($validator->fails()) {
      return response()->json([
        'error_validation' => $validator->errors()->toJson()
      ], Response::HTTP_NOT_ACCEPTABLE);
    }

    $this->createAndSaveArticle($request);

    return response()->json([
      'message' => 'Article created',
      'code' => 200
    ], Response::HTTP_CREATED);
  }

  private function createAndSaveArticle(Request $request)
  {
    $article = new Article();

    $article->id_category = $request->id_category;
    $article->title = $request->title;
    $article->slug = $request->slug;
    $article->small_text = $request->small_text;
    $article->long_text = $request->long_text;
    $article->url_image = $request->url_image;

    $article->creator = auth()->user()->_id;
    $article->creation_date = now()->format('d-m-Y');
    $article->update_date = now()->format('d-m-Y');
    $article->comments = [];
    $article->likes = [];

    $article->save();
  }

  private function validateArticle(Request $request)
  {
    $validator  = Validator::make($request->all(), [
      'id_category' => 'required|string',
      'title' => 'required|string|max:100',
      'slug' => 'required|unique:article',
      'small_text' => 'required|string|max:400',
      'long_text' => 'required|string|max:3000',
    ]);

    return $validator;
  }

  // Create Article ------------------------------------ <-

  // Update Article ------------------------------------ ->

  public function update(Request $request, $id)
  {

    $updatedArticle = array();
    $article = Article::find($id);

    foreach ($this->fields as $field) {
      if (!is_null($request->$field)) {
        $updatedArticle[$field] =  $request->$field;
        continue;
      }
      $updatedArticle[$field] = $article->$field;
    }

    $updatedArticle['update_date'] = now()->format('d-m-Y');

    Article::findOrFail($id)->update($updatedArticle);
    return response()->json([
      'message' => 'Article updated',
      'article' => $updatedArticle,
      'code' => 200
    ], Response::HTTP_OK);
  }

  // Update Article ------------------------------------ <-

  // Delete Article ------------------------------------ ->

  public function delete($id)
  {

    $article = Article::find($id);

    if (!$this->checkCreator($article->creator)) {
      return response()->json([
        'message' => 'user not authorizated'
      ], Response::HTTP_UNAUTHORIZED);
    }

    Article::destroy($id);
    return response()->json([
      'message' => 'Article deleted',
      'code' => 200
    ], Response::HTTP_OK);
  }

  // Delete Article ------------------------------------ <-

  private function checkCreator($creator)
  {
    $currentUser = auth()->user();
    return $creator === $currentUser->_id || $currentUser->type === 1;
  }

  // Create Comment ------------------------------------ ->

  public function saveComment(Request $request, $id)
  {
    if (is_null($request->content)) {
      return response()->json([
        'message' => 'No content was sent'
      ], Response::HTTP_BAD_REQUEST);
    }

    $comment = $request->content;
    $allFieldCommet = array(
      'creator' => auth()->user()->_id,
      'name' => auth()->user()->name,
      'content' => $comment,
      'id_date' => now()->format('H:i:s')
    );

    DB::collection('article')->where('_id', $id)->push('comments', $allFieldCommet, true);

    return response()->json([
      'message' => 'Comment added',
      'code' => 200
    ], Response::HTTP_OK);
  }

  // Create Comment ------------------------------------ <-

  // Delete Comment ------------------------------------ ->

  public function deleteComment(Request $request, $id)
  {
    if (is_null($request->id_date)) {
      return response()->json([
        'message' => 'No id_date was sent'
      ], Response::HTTP_BAD_REQUEST);
    }

    $article = Article::where('comments.id_date', $request->id_date)->get()->first()->toArray();

    if (empty($article)) {
      return response()->json([
        'message' => 'Cooment not exist',
        'code' => 400
      ], Response::HTTP_NOT_ACCEPTABLE);
    }

    $commentSearch = null;

    foreach ($article['comments'] as $comment) {
      if ($comment['id_date'] == $request->id_date) {
        $commentSearch = $comment;
        break;
      }
    }

    if (!$this->checkCreator($commentSearch['creator'])) {
      return response()->json([
        'message' => 'user not authorizated'
      ], Response::HTTP_UNAUTHORIZED);
    }

    DB::collection('article')->where('_id', $id)->pull('comments', $commentSearch);

    return response()->json([
      'message' => 'Comment deleted',
      'code' => 200
    ], Response::HTTP_OK);
  }

  // Delete Comment ------------------------------------ <-

  public function saveLike($id)
  {

    $allFieldLike = array(auth()->user()->_id,);

    DB::collection('article')->where('_id', $id)->push('likes', $allFieldLike, true);

    return response()->json([
      'message' => 'Like added',
      'code' => 200
    ], Response::HTTP_OK);
  }

  // Delete Like ------------------------------------ ->

  public function deleteLike($id)
  {
    $like = Article::where('likes', auth()->user()->_id)->get()->toArray();

    if (empty($like)) {
      return response()->json([
        'message' => 'Like not exist',
        'code' => 400
      ], Response::HTTP_NOT_ACCEPTABLE);
    }

    DB::collection('article')->where('_id', $id)->pull('likes', auth()->user()->_id);

    return response()->json([
      'message' => 'Like deleted',
      'code' => 200
    ], Response::HTTP_OK);
  }

  // Delete Like ------------------------------------ <-

  public function findArticleBySlug($slug)
  {

    $article = Article::where('slug', $slug)->get()->toArray();

    if (empty($article)) {
      return response()->json([
        'message' => 'Article not exist',
        'code' => 400
      ], Response::HTTP_NOT_ACCEPTABLE);
    }

    return response()->json([
      'message' => 'Article found',
      'article' => $article,
      'code' => 200
    ], Response::HTTP_OK);
  }
}
