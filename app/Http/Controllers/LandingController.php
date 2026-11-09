<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        $organizations = Organization::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('welcome', compact('organizations'));
    }
}
