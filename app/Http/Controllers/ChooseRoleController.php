<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ChooseRoleController extends Controller
{
    /**
     * Show the choose role page
     */
    public function index()
    {
        $adminUrl = env('ADMIN_APP_URL', url('/admin'));
        return view('choose-role', compact('adminUrl'));
    }

    /**
     * Root route: show role choice view when not authenticated, otherwise redirect to dashboard
     */
    public function home()
    {
        Log::info('ChooseRoleController@home called; ADMIN_APP_URL=' . env('ADMIN_APP_URL'));
        $adminUrl = env('ADMIN_APP_URL');
        return view('choose-role', compact('adminUrl'));
    }

    /**
     * Redirect to chosen role area
     * @param Request $request
     * @param string $role
     */
    public function redirect(Request $request, $role)
    {
        $role = strtolower($role);

        if ($role === 'resident') {
            return redirect()->route('dashboard');
        }

        $adminUrl = env('ADMIN_APP_URL');
        Log::info('ChooseRoleController@redirect called: role=' . $role . ', ADMIN_APP_URL=' . ($adminUrl ?? 'null'));

        if ($role === 'admin') {
            if ($adminUrl) {
                try {
                    $resp = Http::timeout(3)->get($adminUrl);
                    Log::info('Admin URL check response status: ' . $resp->status());
                    if ($resp->status() >= 200 && $resp->status() < 400) {
                        return redirect($adminUrl);
                    }
                } catch (\Throwable $e) {
                    Log::warning('Admin URL check failed: ' . $e->getMessage());
                }
            }
            return redirect()->route('admin.redirect')->with('error', 'Admin app not reachable; check ADMIN_APP_URL');
        }

        return redirect()->route('dashboard');
    }
}
