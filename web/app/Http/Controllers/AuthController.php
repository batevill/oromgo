<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider.
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['error' => 'Avtorizatsiyada xatolik yuz berdi']);
        }

        $user = User::where($provider . '_id', $socialUser->getId())->first();

        if (!$user) {
            // Agar email bo'lsa email orqali izlab ko'ramiz
            if ($socialUser->getEmail()) {
                $user = User::where('email', $socialUser->getEmail())->first();
            }

            if (!$user) {
                // Yangi foydalanuvchini yaratamiz
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $socialUser->getEmail(),
                    $provider . '_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'role' => 'user',
                ]);
            } else {
                // Eski foydalanuvchiga provider id sini ulaymiz
                $user->update([
                    $provider . '_id' => $socialUser->getId(),
                    'avatar' => $user->avatar ?? $socialUser->getAvatar()
                ]);
            }
        }

        Auth::login($user, true);

        // API bo'lsa tokenni qaytarishimiz mumkin: 
        // $token = $user->createToken('auth_token')->plainTextToken;
        // return response()->json(['token' => $token, 'user' => $user]);

        return redirect('/'); // Yoki kerakli dashboard manziliga
    }
}
