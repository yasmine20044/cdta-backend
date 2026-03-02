<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Mews\Purifier\Facades\Purifier;



class EventController extends Controller
{
    // GET all events
    public function index()
    {
        return response()->json(Event::all());
    }

    public function store(Request $request)
{
   $data = $request->only([
   'title',
   'description',
   'status',
   'start_date',
   'end_date',
   'location',
   'category'
]);

    $data['description'] = Purifier::clean($data['description']);
    $data['slug'] = \Str::slug($request->title);

     if($request->hasFile('image')){
        $data['image'] = $request->file('image')->store('events','public');
    }

    $event = Event::create($data);

    return response()->json($event, 201);
}
    // GET one event
    public function show($id)
    {
        return response()->json(Event::findOrFail($id));
    }

    // PUT update
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);
   $data = $request->only([
    'title',
    'description',
    'status',
    'start_date',
    'end_date',
    'location',
    'category'
]);

         if(isset($data['description'])){
        $data['description'] = Purifier::clean($data['description']);
    }

        $event->update($data);
        if($request->hasFile('image')){
        $data['image'] = $request->file('image')->store('events','public');
       
    }
     $event->update($data);
        return response()->json($event);
    }

    // DELETE
    public function destroy($id)
    {
        Event::destroy($id);
        return response()->json(['message' => 'Deleted']);
    }
}