<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Mews\Purifier\Facades\Purifier;

class ServiceController extends Controller
{
    // Lister tous les services
    public function index()
    {
        return response()->json(Service::all());
    }

    // Créer un service
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'in:draft,published'
        ]);

        if ($validator->fails()) 
            return response()->json($validator->errors(), 422);

        // Nettoyage XSS
        $data = $request->only(['title','description','status']);
        $data['title'] = strip_tags($data['title']);               // Supprime tous les scripts dans le title
        $data['description'] = Purifier::clean($data['description']); // Supprime les scripts dangereux dans description
        $data['slug'] = Str::slug($data['title']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service = Service::create($data);

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
        $service = Service::find($id);
        if (!$service) return response()->json(['message' => 'Service not found'], 404);

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'description' => 'string',
            'status' => 'in:draft,published'
        ]);

        if ($validator->fails()) return response()->json($validator->errors(), 422);

        $data = [];

        if ($request->title) {
            $data['title'] = strip_tags($request->title);
            $data['slug'] = Str::slug($data['title']);
        }

        if ($request->description) {
            $data['description'] = Purifier::clean($request->description);
        }

        if ($request->status) {
            $data['status'] = $request->status;
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        $service->update($data);

        return response()->json($service);
    }

    // Supprimer un service
    public function destroy($id)
    {
        $service = Service::find($id);
        if (!$service) return response()->json(['message' => 'Service not found'], 404);

        $service->delete();

        return response()->json(['message' => 'Service deleted successfully']);
    }
}