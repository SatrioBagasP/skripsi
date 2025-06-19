<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function index()
    {
        return view('Pages.Auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required'],
            'password' => ['required'],
        ]);

        // login via nim,nip,email,username
        $user = User::where('email', $request->username)
            ->orWhere('name', $request->username)
            ->orWhereHasMorph(
                'userable',
                [Mahasiswa::class, Dosen::class],
                function ($q, $type) use ($request) {
                    $column = $type === Mahasiswa::class ? 'npm' : 'nip';

                    $q->where($column, $request->username);
                }
            )
            ->first();


        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('dashboard.index');
        } else {
            return back()->withErrors([
                'loginFailed' => 'User belum terdaftar, silahkan hubungi Admin!',
            ]);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
