<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class ArticleExist
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next)
  {

    $idArticle = $request->route('id');
    $article = DB::collection('article')->where('_id', $idArticle)->get()->first();

    if (is_null($article)) {
      return response()->json([
        'message' => 'Cooment not exist',
        'code' => 400
      ], Response::HTTP_NOT_ACCEPTABLE);
    }

    return $next($request);
  }
}
