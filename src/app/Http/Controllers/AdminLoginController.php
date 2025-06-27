<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;


class AdminLoginController extends Controller
{
    //
    public function index()
    {
        return view('admin.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate(); // セッション固定攻撃対策
            return redirect()->route('admin.attendance.list');
        }

        return back()->withErrors(['email' => 'ログイン情報が登録されていません']);
    }

}
