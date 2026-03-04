<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;
 use Illuminate\Support\Facades\Cache;


class NewsController extends Controller
{
    public function index()
{
    return Cache::remember('news_all', 60, function () {
        return News::all();
    });
}
    public function show($id) { 
        $news = News::find($id);
        if(!$news) return response()->json(['message'=>'News not found'],404);
        return response()->json($news);
    }

    //store
    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'status' => 'in:draft,published',
        'excerpt' => 'nullable|string|max:500',
        'author' => 'nullable|string|max:255',
        'category' => 'nullable|string|max:100',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    foreach (['content','excerpt','author','category'] as $field) {
        if (isset($validated[$field])) {
            $validated[$field] = Purifier::clean($validated[$field]);
        }
    }

   $validated['title'] = strip_tags($validated['title']);  
   
   $validated['slug']  = Str::slug($validated['title']);
    $validated['status'] = $validated['status'] ?? 'draft';
    $validated['author'] = $validated['author'] ?? 'CDTA';
    $validated['published_at'] = now();

    if ($request->hasFile('image')) {
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/','', $request->file('image')->getClientOriginalName());
        $path = $request->file('image')->storeAs('events', $filename, 'public');
        $validated['image'] = $path;
    }

    $news = News::create($validated);
    Cache::forget('news_all');

    return response()->json($news, 201);

}

//update
   public function update(Request $request, $id)
{
    $news = News::findOrFail($id);

    $validated = $request->validate([
        'title' => 'sometimes|string|max:255',
        'content' => 'sometimes|string',
        'status' => 'sometimes|in:draft,published',
        'excerpt' => 'nullable|string|max:500',
        'author' => 'nullable|string|max:255',
        'category' => 'nullable|string|max:100',
        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
    ]);

    foreach (['title','content','excerpt','author','category'] as $field) {
        if (isset($validated[$field])) {
            $validated[$field] = Purifier::clean($validated[$field]);
        }
    }

    if (isset($validated['title'])) {
    $cleanTitle = strip_tags($validated['title']);  // aucun HTML
    $validated['title'] = $cleanTitle;
    $validated['slug'] = Str::slug($cleanTitle);
}
    if ($request->hasFile('image')) {
       $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/','', $request->file('image')->getClientOriginalName());
       $path = $request->file('image')->storeAs('events', $filename, 'public');
       $validated['image'] = $path;
    }

    $news->update($validated);
    Cache::forget('news_all');

    return response()->json($news);
}
    public function destroy($id) {
        $news = News::find($id);
        if(!$news) return response()->json(['message'=>'News not found'],404);
        $news->delete();
        Cache::forget('news_all');
        return response()->json(['message'=>'News deleted successfully']);
    }
}