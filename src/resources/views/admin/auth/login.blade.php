@extends('layouts.app')
@section('title', '管理者ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')

    <div class="login">
        <div class="login__title" style="margin-top: 10vh">
            <h1 style="font-weight: bold; font-size:36px">管理者ログイン</h1>
        </div>

        <form action="" method="POST" class="login__form" novalidate>
            @csrf
            <div class="login__form__input">
                <label for="email">メールアドレス</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>
            @error('email')
                <div class="error-message">
                    {{ $message }}
                </div>
            @enderror

            <div class="login__form__input">
                <label for="password">パスワード</label>
                <input type="password" name="password" id="password" required>
            </div>
            @error('password')
                <div class="error-message">
                    {{ $message }}
                </div>
            @enderror


            <button type="submit" class="login__button">管理者ログインする</button>
        </form>
    </div>
@endsection
