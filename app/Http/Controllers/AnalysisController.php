<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use function Termwind\render;

class AnalysisController extends Controller
{
    public function index()
    {
        return Inertia::render('Analysis');
    }
}
