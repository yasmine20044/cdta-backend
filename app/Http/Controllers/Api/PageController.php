<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;

class PageController extends Controller
{
    // Lister toutes les pages
    public function index()
    {
        return response()->json(Page::all());
    }

    // Voir une page spécifique
    public function show($id)
    {
        $page = Page::find($id);
        if (!$page) return response()->json(['message' => 'Page not found'], 404);

        return response()->json($page);
    }

    // Créer une page
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'in:draft,published'
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('pages', 'public');
        }
          
        $page = Page::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => Purifier::clean($request->content),
            'status' => $request->status ?? 'draft',
            'image' => $imagePath
        ]);

        return response()->json($page, 201);
    }

    // Modifier une page
    public function update(Request $request, $id)
    {
        $page = Page::find($id);
        if (!$page) return response()->json(['message' => 'Page not found'], 404);

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'content' => 'string',
            'status' => 'in:draft,published'
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        if ($request->title) {
            $page->title = $request->title;
            $page->slug = Str::slug($request->title);
        }

       if ($request->content){
    $page->content = strip_tags($request->content);
}
        if ($request->status) $page->status = $request->status;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('pages', 'public');
            $page->image = $imagePath;
        }

        $page->save();

        return response()->json($page);
    }

    // Supprimer une page
    public function destroy($id)
    {
        $page = Page::find($id);
        if (!$page) return response()->json(['message' => 'Page not found'], 404);

        $page->delete();
        return response()->json(['message' => 'Page deleted successfully']);
    }
}
