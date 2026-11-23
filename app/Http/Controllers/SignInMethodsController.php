<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Used by the "Sign-in methods" card on the profile page. */
class SignInMethodsController extends Controller
{
    public function unlinkGoogle(Request $request)
    {
        $admin = $request->user('admin');

        if (! $admin->hasPassword()) {
            return response()->json([
                'success' => false,
                'message' => 'Set a password before unlinking Google.',
            ], 422);
        }

        $admin->forceFill([
            'google_id'        => null,
            'google_linked_at' => null,
            'avatar'           => null,
        ])->save();

        return response()->json(['success' => true, 'message' => 'Google account unlinked.']);
    }
}