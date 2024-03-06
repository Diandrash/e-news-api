<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
// use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Collection;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $client = new Client();
        $url = "http://localhost:8001/api/articles";
        $response = $client->request("GET", $url);
         // Mendapatkan data artikel dari API
        $jsonArticles = $response->getBody()->getContents();
        $articleArray = json_decode($jsonArticles, true)['data'];
        
        $articles = collect($articleArray)->map(function ($articleData) {
            return Article::updateOrCreate(['id' => $articleData['id']], $articleData);
        });

        $categories = Category::all();
        return view('articles.index', [
            'title' => 'Article Pages',
            'categories' => $categories,
            'articles' => $articles,
        ]);
    }

    public function search(Request $request)
    {
        $client = new Client();
        $url = "http://localhost:8001/api/articles/search";
        $parameter = [
            $keyword = $request['search'],
        ];
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type'=>'application/json'],
            'json' => [
                'keyword' => $keyword,
            ],
        ]);
        $jsonArticles = $response->getBody()->getContents();
        $articleArray = json_decode($jsonArticles, true)['data'];
        dd($articleArray);
        // Membuat koleksi artikel dari data yang diterima
        $articles = collect($articleArray)->map(function ($articleData) {
            // Mencoba mencari artikel berdasarkan id, dan kemudian memperbarui atau membuat artikel
            return Article::updateOrCreate(['id' => $articleData['id']], $articleData);
        });
        $categories = Category::all();
        dd($articles);
        return view('articles.index', [
            'title' => $keyword . 'Search',
            'categories' => $categories,
            'articles' => $articles,
        ]);
    }

    public function category(Request $request)
    {
        $client = new Client();
        $url = 'http://localhost:8001/api/articles/category';
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'categoryId' => $request['category']
            ]
            ]);
        $jsonArticles = $response->getBody()->getContents();
        $articleArray = json_decode($jsonArticles, true)['data'];
        // dd($articleArray);
        // Membuat koleksi artikel dari data yang diterima
        $articles = collect($articleArray)->map(function ($articleData) {
            // Mencoba mencari artikel berdasarkan id, dan kemudian memperbarui atau membuat artikel
            return Article::updateOrCreate(['id' => $articleData['id']], $articleData);
        });
        $categories = Category::all();
        // dd($articles);
        return view('articles.index', [
            'title' => 'Category Search',
            'categories' => $categories,
            'articles' => $articles,
        ]);
    }
    public function myindex()
    {
        $client = new Client();
        $url = 'http://localhost:8001/api/myarticles';
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'userId' => auth()->user()->id
            ]
        ]);
        $jsonArticles = $response->getBody()->getContents();
        $arrayArticles = json_decode($jsonArticles, true)['data'];
        $articles = collect($arrayArticles)->map(function ($articleData) {
              return Article::updateOrCreate(['id' => $articleData['id'], $articleData]);
        });
        // $articles = Article::where('author_id', auth()->user()->id)->get();
        $categories = Category::all();
        return view('articles.myindex', [
            'title' => 'Article Pages',
            'categories' => $categories,
            'articles' => $articles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        return view('articles.create', [
            'title' => 'Create Article',
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'author_id' => 'required',
            'category_id' => 'required',
            'title' => 'required|min:3|max:300',
            'text' => 'required|min:3|max:3048',
            'image' => 'required|mimes:jpg,jpeg,png,heic,webp',
        ]);

        $file = $request->file('image');
        $filename = $file->getClientOriginalName();
        $file->move('articleimages', $filename);

        $parameter = [
            'author_id'=> $request['author_id'],
            'category_id'=> $request['category_id'],
            'title' => $request['title'],
            'image'=> $filename,
            'text'=> $request['text'],
        ];
        // dd($parameter);
        $client = new Client();
        $url = "http://localhost:8001/api/myarticles/create";
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type'=>'application/json'],
            'body' => json_encode($parameter) 
        ]);
        Alert::success('Success', 'Product Created');
        return redirect()->intended('/myarticles');
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {   
        $client = new Client();
        $articleId = $article->id;
        $url = "http://localhost:8001/api/articles/$articleId";
        $response = $client->request("GET", $url);
        
        $jsonArticles = $response->getBody()->getContents();
        $articleArray = json_decode($jsonArticles, true)['data'];
        
        $articleCollection = collect([$articleArray]);
        $articleModel = new Article();
        $articleModel->updateOrCreate(['id' => $articleArray['id']], $articleArray);
        $articleModel = Article::find($articleArray['id']);

        $categoryId = $article->category_id;
        $articles = Article::where('category_id', $categoryId)->whereNotIn('id', [$article->id])->get();
        return view('articles.show', [
            'title' => $article->title,
            'article' => $article,
            'articles' => $articles,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        
        // $client = new Client();
        // $url = "http://localhost:8001/api/articles/$article/edit";
        // $response = $client->request("GET", $url);
        //  // Mendapatkan data artikel dari API
        // $jsonArticles = $response->getBody()->getContents();
        // $articleArray = json_decode($jsonArticles, true)['data'];
        
        // // Membuat koleksi artikel dari data yang diterima
        // $articles = collect($articleArray)->map(function ($articleData) {
        //     // Mencoba mencari artikel berdasarkan id, dan kemudian memperbarui atau membuat artikel
        //     return Article::updateOrCreate(['id' => $articleData['id']], $articleData);
        // });

        // Tampilkan koleksi artikel
        // dd($articles);
        $categoryId = $article->category_id;
        $categories = Category::all();
        return view('articles.edit', [
            'title' => 'Edit Article',
            'categories' => $categories,
            'article' => $article,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateArticleRequest $request, Article $article)
    {
        $validatedData = $request->validate([
            'author_id' => 'required',
            'category_id' => 'required',
            'title' => 'required|min:3|max:300',
            'text' => 'required|min:3|max:3048',
            'image' => 'mimes:jpg,jpeg,png,heic,webp',
        ]);

        $filename = $article->image;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = $file->getClientOriginalName();
            $file->move('articleimages', $filename);
        }

        $parameter = [
            '_method' => 'PUT',
            'author_id'=> $request['author_id'],
            'category_id'=> $request['category_id'],
            'title' => $request['title'],
            'image'=> $filename,
            'text'=> $request['text'],
        ];
        $client = new Client();
        $articleId = $article->id;
        $url = "http://localhost:8001/api/myarticles/$articleId/edit";
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type'=>'application/json'],
            'body' => json_encode($parameter) 
        ]);

        Alert::success('Success', 'Article Updated');
        return redirect()->intended('/myarticles');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article)
    {
        $parameter = [
            '_method' => 'DELETE',
        ];

        $client = new Client();
        $articleId = $article->id;
        $url = "http://localhost:8001/api/myarticles/$articleId/delete";
        $response = $client->request('POST', $url, [
            'headers' => ['Content-Type'=>'application/json'],
            'body' => json_encode($parameter) 
        ]);

        Alert::success('Success', 'Article Deleted');
        return redirect()->intended('/myarticles');
    }
}
