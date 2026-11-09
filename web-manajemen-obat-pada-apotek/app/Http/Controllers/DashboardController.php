<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;

class DashboardController extends Controller
{
    public function index()
    {
        // Anda bisa mengirim data ringkasan ke view dashboard nanti
        return view('dashboard'); 
    }
}
