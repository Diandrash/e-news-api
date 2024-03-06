<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Http\Request;

class ApiArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::all();
        return response()->json([
            'status' => 200,
            'message' => 'All Articles',
            'data'=> $articles,
        ]);
    }

    public function search(Request $request)
    {
        $keyword = $request['keyword'];
        $articles = Article::where('title', 'LIKE', "%$keyword%")->get();
        return response()->json([
            "status"=> 200,
            "message"=> "Search Product Result",
            "data"=> $articles
        ]);

    }

    public function category(Request $request)
    {
        $categoryId = $request['categoryId'];
        $articles = Article::where('category_id', $categoryId)->get();
        return response()->json([
            "status"=> 200,
            "message"=> "Search Product Result",
            "data"=> $articles
        ]);

    }
    public function myindex(Request $request)
    {
        $userId = $request['user_id'];
        $articles = Article::where('author_id', $userId)->get();
        return response()->json($articles);
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {



        $article = Article::create([
            'author_id' => $request['author_id'],
            'category_id'=> $request['category_id'],
            'title'=> $request['title'],
            'text'=> $request['text'],
            // 'image'=> $filename,
            'image'=> $request['image'],
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Article Created',
            'data'=> $article,
        ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        return response()->json([
            'status' => 200,
            'message' => 'All Articles',
            'data'=> $article,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Article $article)
    {
        $article->update([
            'author_id' => $request['author_id'],
            'category_id'=> $request['category_id'],
            'title'=> $request['title'],
            'text'=> $request['text'],
            // 'image'=> $filename,
            'image'=> $request['image'],
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Article Updated',
            'data'=> $article,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $article->delete();
    
        return response()->json('Article Deleted');

    }
}
