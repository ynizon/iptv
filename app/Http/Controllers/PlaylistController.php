<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $playlists = Playlist::all();
        return view("playlists/index", compact("playlists"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $playlist = new Playlist();
        return view("playlists/edit", compact("playlist"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = [];
        $validated['name'] = $request->input('name');
        $validated['url'] = $request->input('url');
        $validated['tld'] = $request->input('tld');
        $validated['ip'] = $request->input('ip');
        Playlist::create($validated);

        return redirect()->route('playlists.index')
            ->with('success',__('Playlist created successfully.'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $playlist = Playlist::find($id);
        return view("playlists/edit", compact("playlist"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Playlist $playlist)
    {
        $validated = [];
        $validated['name'] = $request->input('name');
        $validated['url'] = $request->input('url');
        $validated['tld'] = $request->input('tld');
        $validated['ip'] = $request->input('ip');
        $playlist->update($validated);
        return redirect()->route('playlists.index')
            ->with('success', __('Playlist updated successfully.'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Playlist $playlist)
    {
        $playlist->delete();
        return redirect()->route('playlists.index')
            ->with('success', __('Playlist deleted successfully.'));
    }
}
