<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UnitKemahasiswaan;
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

        // login via nip,email,username
        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->username)
                ->orWhere('name', $request->username)
                ->orWhereHasMorph(
                    'userable',
                    '*',
                    function ($query, $type) use ($request) {
                        $column = $type === UnitKemahasiswaan::class ? 'name' : 'nip';
                        $query->where($column, $request->username);
                    }
                );
        })->where(function ($query) use ($request) {
            $query->whereHasMorph('userable', '*', function ($q) {
                $q->where('status', true);
            })->whereDoesntHave('userable', function ($query) use ($request) {
                $query->where('name', $request->username);
            });
        })->first();


        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('dashboard.index');
        } else {
            return back()->withErrors([
                'loginFailed' => 'User belum terdaftar atau tidak aktif, silahkan hubungi Admin!',
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
