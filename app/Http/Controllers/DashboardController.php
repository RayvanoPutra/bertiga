<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function index() {
        $user = Auth::user();

        if ($user->role === 'super_admin') {
            return redirect()->route('super_admin.dashboard');
        }else{
            return redirect()->route('admin.dashboard');
        }
    }
}
