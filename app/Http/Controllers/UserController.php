<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        if (Auth::user()->isAdmin())
        {
            $users = User::all();
            return view('users/index', compact('users'));
        } else{
            abort(403, __('Unauthorized action.'));
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->isAdmin()){
           $user = new User();
            return view("users/edit", compact("user"));
        } else{
            abort(403, __('Unauthorized action.'));
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->isAdmin()){
        $validated = [];
        $validated['name'] = $request->input('name');
        $validated['admin'] = $request->input('admin');
        $validated['email'] = $request->input('email');
        $validated['password'] = bcrypt($request->input('password'));
        $validated['formats'] = '["HD","UHD","FHD"]';
        User::create($validated);

        return redirect()->route('users.index')
            ->with('success',__('User created successfully.'));
        } else{
            abort(403, __('Unauthorized action.'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        if (Auth::user()->isAdmin()){
            $user = User::find($id);
            return view("users/edit", compact("user"));
        } else{
            abort(403, __('Unauthorized action.'));
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        if (Auth::user()->isAdmin()){
            $validated = [];
            $validated['name'] = $request->input('name');
            $validated['email'] = $request->input('email');
            $validated['admin'] = $request->input('admin');
            $user->update($validated);
            return redirect()->route('users.index')
                ->with('success', __('User updated successfully.'));
        } else{
            abort(403, __('Unauthorized action.'));
        }
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if (Auth::user()->isAdmin() && $user->email != env('ADMIN_USER')) {
            $user->delete();
            return redirect()->route('users.index')
                ->with('success', __('User deleted successfully.'));
        } else {
            abort(403, __('Unauthorized action.'));
        }
    }
}
