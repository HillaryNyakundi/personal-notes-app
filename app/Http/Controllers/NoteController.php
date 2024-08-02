<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notes = Note::query()
        ->where("user_id", request()->user()->id)
        ->orderBy("created_at", 'desc')->paginate();
        return view ('note.index', ['notes' => $notes]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('note.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    \Log::info('Attempting to store a new note');
    
    if (!$request->user()) {
        return redirect()->route('login')->withErrors(['You must be logged in to create a note.']);
    }

    \Log::info('User authenticated: ', ['user_id' => $request->user()->id]);

    $data = $request->validate([
        'note' => ['required', 'string'],
    ]);

    \Log::info('Validated data: ', $data);

    $data['user_id'] = $request->user()->id;

    \Log::info('Data with user_id: ', $data);

    try {
        $note = Note::create($data);
        \Log::info('Note created successfully: ', $note->toArray());
    } catch (\Exception $e) {
        \Log::error('Error creating note: ', ['message' => $e->getMessage()]);
        throw $e;
    }

    return to_route('note.show', $note)->with('message', 'Note was created');
    }



    /**
     * Display the specified resource.
     */
    public function show(Note $note)
    {
        if ($note->user_id !== request()->user()->id){
            abort(403);
        }
        return view('note.show', ['note' => $note]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Note $note)
    {
        return view('note.edit', ['note' => $note]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Note $note)
    {
        $data = $request->validate([
            'note' => ['required', 'string']
        ]);

        $note->update($data);

        return to_route('note.show', $note)->with('message', 'Note was updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note)
    {
        $note->delete();

        return to_route('note.index')->with('message', 'Note was deleted');
    }
}
