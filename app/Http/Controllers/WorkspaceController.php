<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** First-run page where a new company owner names the workspace. Routes use the super_admin middleware. */
class WorkspaceController extends Controller
{
    public function show(Request $request)
    {
        $tenant = $request->user('admin')->tenant;

        return view('pages.auth.onboarding', compact('tenant'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
        ]);

        // Only the logged-in owner's own company can be changed, never an id from the request.
        $request->user('admin')->tenant->update(['name' => trim($data['name'])]);

        return redirect()->route('auth.home.main')->with('auth_status', 'Workspace saved.');
    }
}