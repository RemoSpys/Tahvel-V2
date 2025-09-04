<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TahvelCookieController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // https://tahvel.edu.ee/hois_back/user

        $response = Http::withHeaders([
             'cookie' => $request->tahvel_cookie,
        ])->get('https://tahvel.edu.ee/hois_back/user');

        if(!$response->ok() || empty($response->body())) {
            return redirect()->back()->withErrors([
                'tahvel_cookie' => 'Invalid Tahvel Cookie. Please make sure you copied the entire cookie value.',
            ]);
        }

        $request->user()->update([
            'tahvel_cookie' => $request->tahvel_cookie
        ]);

        return redirect()->to(route('dashboard'));
    }
}
