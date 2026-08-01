<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Log de gebruiker in. Verwacht dat de frontend eerst
     * GET /sanctum/csrf-cookie heeft aangeroepen.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, remember: true)) {
            throw ValidationException::withMessages([
                'email' => ['De combinatie van e-mailadres en wachtwoord is onjuist.'],
            ]);
        }

        // Voorkomt session fixation
        $request->session()->regenerate();

        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    /**
     * Geeft de huidige ingelogde gebruiker terug, of 401 als niemand
     * is ingelogd. De React-app gebruikt dit endpoint om bij het laden
     * te checken of de sessie nog geldig is.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}
