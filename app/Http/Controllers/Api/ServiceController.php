<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
 use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    // Lister tous les services
   public function index()
{
    return Cache::remember('services_all', 60, function () {
        return Service::all();
    });
}

    // Créer un service
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'status'      => 'in:draft,published',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Nettoyage
        $validated['description'] = Purifier::clean($validated['description']); // garde HTML safe
        $validated['title']       = strip_tags($validated['title']);             // aucun HTML
        $validated['status']      = strip_tags($validated['status']);
        $validated['slug']        = Str::slug($validated['title']);
        $validated['status']      = $validated['status'] ?? 'draft';

        if ($request->hasFile('image')) {
            $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/','', $request->file('image')->getClientOriginalName());
            $path = $request->file('image')->storeAs('events', $filename, 'public');
            $validated['image'] = $path;
        }

        $service = Service::create($validated);
        Cache::forget('services_all');
        return response()->json($service, 201);
    }

    // Voir un service spécifique
    public function show($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['message' => 'Service not found'], 404);

        return response()->json($service);
    }

    // Mettre à jour un service
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status'      => 'sometimes|in:draft,published',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if (isset($validated['description'])) {
            $validated['description'] = Purifier::clean($validated['description']);
        }

        foreach (['title','status'] as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = strip_tags($validated[$field]);
            }
        }

        if (isset($validated['title'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if ($request->hasFile('image')) {
            $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/','', $request->file('image')->getClientOriginalName());
            $path = $request->file('image')->storeAs('events', $filename, 'public');
            $validated['image'] = $path;
        }

        $service->update($validated);
        Cache::forget('services_all');
        return response()->json($service);
    }

    // Supprimer un service
    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['message' => 'Service not found'], 404);

        $service->delete();
        Cache::forget('services_all');
        return response()->json(['message' => 'Service deleted successfully']);
    }
}