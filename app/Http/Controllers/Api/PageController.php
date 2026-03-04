<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Mews\Purifier\Facades\Purifier;
 use Illuminate\Support\Facades\Cache;

class PageController extends Controller
{
    // Lister toutes les pages
   public function index()
{
    return Cache::remember('pages_all', 60, function () {
        return Page::all();
    });
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
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'content' => 'required|string',
            'status'  => 'in:draft,published',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Nettoyage
        if (isset($validated['content'])) {
            $validated['content'] = Purifier::clean($validated['content']); // HTML safe
        }

        if (isset($validated['title'])) {
            $validated['title'] = strip_tags($validated['title']); // aucun HTML
            $validated['slug']  = Str::slug($validated['title']);
        }

        $validated['status'] = $validated['status'] ?? 'draft';

        // Gestion image
        if ($request->hasFile('image')) {
            $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/','', $request->file('image')->getClientOriginalName());
            $path = $request->file('image')->storeAs('events', $filename, 'public');
            $validated['image'] = $path;
        }

        $page = Page::create($validated);
        Cache::forget('pages_all');
        return response()->json($page, 201);
    }

    // Modifier une page
    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $validated = $request->validate([
            'title'   => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'status'  => 'sometimes|in:draft,published',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Nettoyage
        if (isset($validated['content'])) {
            $validated['content'] = Purifier::clean($validated['content']); // HTML safe
        }

        if (isset($validated['title'])) {
            $validated['title'] = strip_tags($validated['title']); // aucun HTML
            $validated['slug']  = Str::slug($validated['title']);
        }

        // Gestion image
        if ($request->hasFile('image')) {
            $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/','', $request->file('image')->getClientOriginalName());
            $path = $request->file('image')->storeAs('events', $filename, 'public');
            $validated['image'] = $path;
        }

        $page->update($validated);
        Cache::forget('pages_all');
        return response()->json($page);
    }

    // Supprimer une page
    public function destroy($id)
    {
        $page = Page::find($id);
        if (!$page) return response()->json(['message' => 'Page not found'], 404);

        $page->delete();
        Cache::forget('pages_all');
        return response()->json(['message' => 'Page deleted successfully']);
    }
}
