<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;



class NewsController extends Controller
{
    public function index() { return response()->json(News::all()); }
    public function show($id) { 
        $news = News::find($id);
        if(!$news) return response()->json(['message'=>'News not found'],404);
        return response()->json($news);
    }
    public function store(Request $request) {
        $v = Validator::make($request->all(), [
            'title'=>'required|string|max:255',
            'content'=>'required|string',
            'status'=>'in:draft,published'
        ]);
        if($v->fails()) return response()->json($v->errors(),422);

        $imagePath = null;

if($request->hasFile('image')){
    $imagePath = $request->file('image')->store('news','public');
}

        $news = News::create([
    'title' => $request->title,
    'slug' => Str::slug($request->title),
    'content' => Purifier::clean($request->content),
    'status' => $request->status ?? 'draft',
    'excerpt' => $request->excerpt,
    'author' => $request->author ?? 'CDTA',
    'published_at' => now(),
    'category' => $request->category,
    'image' => $imagePath
]);
        return response()->json($news,201);
    }
   public function update(Request $request,$id) {

    $news = News::find($id);
    if(!$news) return response()->json(['message'=>'News not found'],404);

    $v = Validator::make($request->all(), [
        'title'=>'string|max:255',
        'content'=>'string',
        'status'=>'in:draft,published'
    ]);

    if($v->fails()) return response()->json($v->errors(),422);

    if($request->title){
        $news->title = $request->title;
        $news->slug = Str::slug($request->title);
    }

    if($request->hasFile('image')){
        $imagePath = $request->file('image')->store('news','public');
        $news->image = $imagePath;
    }

    if($request->content){
    $news->content = strip_tags($request->content);
}
    if($request->status) $news->status = $request->status;
    if($request->excerpt) $news->excerpt = $request->excerpt;
    if($request->author) $news->author = $request->author;
    if($request->category) $news->category = $request->category;

    $news->save();

    return response()->json($news);
}
    public function destroy($id) {
        $news = News::find($id);
        if(!$news) return response()->json(['message'=>'News not found'],404);
        $news->delete();
        return response()->json(['message'=>'News deleted successfully']);
    }
}