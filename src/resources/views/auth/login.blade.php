@extends('layouts.app')
@section('title', 'ログイン')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')

    <div class="login">
        <div class="login__title">
            <h1>ログイン</h1>
        </div>

        <form action="{{ route('login.store') }}" method="POST" class="login__form" novalidate>
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


            <button type="submit" class="login__button">ログインする</button>
        </form>
        <div class="login__form__link">
            <a href="{{ route('register') }}" style="text-decoration: none; color:#0073CC;">会員登録はこちら</a>
        </div>
    </div>
@endsection
