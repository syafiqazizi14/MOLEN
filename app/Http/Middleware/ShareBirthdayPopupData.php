<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ShareBirthdayPopupData
{
    /**
     * Share birthday list to views for authenticated users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $birthdays = User::whereRaw('DAY(tanggal_lahir) = DAY(CURDATE())')
                ->whereRaw('MONTH(tanggal_lahir) = MONTH(CURDATE())')
                ->where('is_active', 1)
                ->get(['name', 'gambar', 'tanggal_lahir']);

            view()->share('globalBirthdays', $birthdays);
        }

        return $next($request);
    }
}
