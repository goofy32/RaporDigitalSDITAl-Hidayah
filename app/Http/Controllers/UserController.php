<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        // Ensure user can only view their own profile
        if (Auth::guard('web')->id() !== $user->id) {
            abort(403);
        }
        
        return view('admin.profile.show', compact('user'));
    }
}