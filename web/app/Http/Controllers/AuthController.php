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
    public function redirectToProvider(Request $request, $provider)
    {
        if ($request->has('role')) {
            session(['auth_role' => $request->role]);
        }
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from provider.
     */
    public function handleProviderCallback(Request $request, $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Avtorizatsiyada xatolik yuz berdi'], 400);
        }

        $user = User::where($provider . '_id', $socialUser->getId())->first();
        $role = session('auth_role', 'user');
        
        // Ensure role is valid
        if (!in_array($role, ['user', 'owner'])) {
            $role = 'user';
        }

        if (!$user) {
            if ($socialUser->getEmail()) {
                $user = User::where('email', $socialUser->getEmail())->first();
            }

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email' => $socialUser->getEmail(),
                    $provider . '_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(),
                    'role' => $role,
                ]);
            } else {
                $user->update([
                    $provider . '_id' => $socialUser->getId(),
                    'avatar' => $user->avatar ?? $socialUser->getAvatar(),
                    'role' => $user->role === 'user' ? $role : $user->role, // Upgrade role if necessary
                ]);
            }
        } else {
            // Upgrade role if logging in with ?role=owner but currently a normal user
            if ($role === 'owner' && $user->role === 'user') {
                $user->update(['role' => 'owner']);
            }
        }

        Auth::login($user, true);
        
        // Clear session role
        session()->forget('auth_role');

        $token = $user->createToken('auth_token')->plainTextToken;

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Muvaffaqiyatli tizimga kirdingiz',
                'token' => $token,
                'user' => $user
            ]);
        }

        return redirect('/?auth_token=' . urlencode($token) . '&user_id=' . $user->id . '&user_name=' . urlencode($user->name) . '&user_role=' . $user->role);
    }
}
