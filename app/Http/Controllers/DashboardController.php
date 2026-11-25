<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        $destination = match ($request->user()->role) {
            'admin' => 'admin.dashboard',
            'client' => 'client.dashboard',
            default => abort(403),
        };

        return redirect()->route($destination);
    }

    public function show(Request $request): Response
    {
        return Inertia::render('dashboard', [
            'name' => $request->user()->name,
            'role' => $request->user()->role,
        ]);
    }
}