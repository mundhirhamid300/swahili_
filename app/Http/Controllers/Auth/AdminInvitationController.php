<?php

/** Hii controller hupokea maombi ya mtumiaji na kuratibu jibu la sehemu hii ya mfumo. */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminInvitationController extends Controller
{
    public function accept(string $token)
    {
        $user = User::query()
            ->where('invitation_token_hash', hash('sha256', $token))
            ->where('role', 'admin')
            ->where('status', 'pending')
            ->first();

        if (! $user || ! $user->invitation_expires_at || $user->invitation_expires_at->isPast()) {
            return redirect()->route('login')
                ->with('error', 'This invitation is invalid or has expired. Ask the Super Admin to send a new invitation.');
        }

        $user->forceFill([
            'status' => 'active',
            'email_verified_at' => now(),
            'invitation_accepted_at' => now(),
            'invitation_token_hash' => null,
            'invitation_expires_at' => null,
            'must_change_password' => true,
        ])->save();

        return redirect()->route('login')->with(
            'status',
            'Invitation confirmed. Sign in with the temporary password from your email, then create a new password.'
        );
    }
}
