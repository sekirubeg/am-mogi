<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    //
    public function index()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate(); // セッション固定攻撃対策
            return redirect()->route('admin.attendance.list');
        }

        return back()->withErrors(['email' => 'メールアドレスまたはパスワードが正しくありません。']);
    }

}
