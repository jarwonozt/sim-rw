<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Self-service Personal Access Token (Sanctum) untuk memakai REST API
 * (docs/api-guide.md) tanpa harus memanggil POST /api/v1/login setiap kali —
 * dipakai baik untuk kebutuhan pengujian developer maupun integrasi produksi
 * (mis. skrip server-to-server) yang login sebagai akun ini.
 */
class ApiTokenController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $token = $request->user()->createToken($data['name']);

        return Redirect::route('profile.edit')->with('newApiToken', $token->plainTextToken);
    }

    public function destroy(Request $request, PersonalAccessToken $apiToken): RedirectResponse
    {
        if ($apiToken->tokenable_id !== $request->user()->id || $apiToken->tokenable_type !== $request->user()->getMorphClass()) {
            abort(403);
        }

        $apiToken->delete();

        return Redirect::route('profile.edit')->with('success', 'Token API berhasil dicabut.');
    }
}
