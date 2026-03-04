<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;
 use Illuminate\Support\Facades\Cache;

class EventController extends Controller
{
 

public function index()
{
    return Cache::remember('events_all', 60, function () {
        return Event::all();
    });
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'status'      => 'required|string|max:50',
            'start_date'  => 'required|date',
            'end_date'    => 'nullable|date',
            'location'    => 'required|string|max:255',
            'category'    => 'required|string|max:100',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        // Nettoyage
        $validated['description'] = Purifier::clean($validated['description']); // garde <p>, <b>, etc.
        $validated['title']       = strip_tags($validated['title']);             // aucun HTML
        $validated['status']      = strip_tags($validated['status']);
        $validated['location']    = strip_tags($validated['location']);
        $validated['category']    = strip_tags($validated['category']);
        $validated['slug']        = Str::slug($validated['title']);

        if ($request->hasFile('image')) {
            $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/','', $request->file('image')->getClientOriginalName());
            $path = $request->file('image')->storeAs('events', $filename, 'public');
            $validated['image'] = $path;
        }

        $event = Event::create($validated);
        Cache::forget('events_all');

        return response()->json($event, 201);
    }

    public function show($id)
    {
        return response()->json(Event::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title'       => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'status'      => 'sometimes|string|max:50',
            'start_date'  => 'sometimes|date',
            'end_date'    => 'nullable|date',
            'location'    => 'sometimes|string|max:255',
            'category'    => 'sometimes|string|max:100',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        foreach (['description'] as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = Purifier::clean($validated[$field]); // garde HTML sûr
            }
        }

        foreach (['status','location','category'] as $field) {
            if (isset($validated[$field])) {
                $validated[$field] = strip_tags($validated[$field]);       // aucun HTML
            }
        }

        if (isset($validated['title'])) {
            $validated['title'] = strip_tags($validated['title']);
            $validated['slug']  = Str::slug($validated['title']);
        }

        if ($request->hasFile('image')) {
            $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/','', $request->file('image')->getClientOriginalName());
            $path = $request->file('image')->storeAs('events', $filename, 'public');
            $validated['image'] = $path;
        }

        $event->update($validated);
        Cache::forget('events_all');

        return response()->json($event);
    }

    public function destroy($id)
    {
        Event::destroy($id);
        Cache::forget('events_all');
        return response()->json(['message' => 'Deleted']);
    }
}