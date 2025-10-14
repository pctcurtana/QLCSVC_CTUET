<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Iphone;
use Inertia\Inertia;    

class testdataController extends Controller
{
    public function testQuery () {
        $dataiphone = Iphone::select('model','storage','color')
        ->take(5)
        ->get();
        return Inertia::render('Home',['dataip' => $dataiphone]);
    }
}
