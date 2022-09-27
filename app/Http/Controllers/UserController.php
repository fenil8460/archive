<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    //
    public function updatePassword(Request $request){
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $password = Hash::make($request->password);

        User::where('id',Auth::user()->id)->update(['password'=>$password]);

        return redirect('/list-task')->with('message', "Password updated successfully!");
    }
}
