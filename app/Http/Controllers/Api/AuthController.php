<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $r, ActivityService $log)
    {
        $d = $r->validate(['email' => 'required|email', 'password' => 'required']);
        $u = User::with('role.permissions', 'branch')->where('email', $d['email'])->first();
        if (! $u || ! Hash::check($d['password'], $u->password) || ! $u->is_active) {
            throw ValidationException::withMessages(['email' => 'Invalid credentials or inactive account.']);
        }$u->update(['last_login_at' => now()]);
        $log->log('login', $u);

        return response()->json(['token' => $u->createToken('crm-web')->plainTextToken, 'user' => $u]);
    }

    public function me(Request $r)
    {
        return $r->user()->load('role.permissions', 'branch');
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()?->delete();

        return ['message' => 'Logged out'];
    }
}
