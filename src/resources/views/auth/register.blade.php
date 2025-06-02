@extends('layouts.app')
@section('title', '会員登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')

    <div class="register">
        <div class="register__title">
            <h1>会員登録</h1>
        </div>

        <form action="{{ route('register.store') }}" method="POST" class="register__form" novalidate>
            @csrf
            <div class="register__form__input">
                <label for="name">ユーザ-名</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required>
            </div>
            @error('name')
                <div class="error-message">
                    {{ $message }}
                </div>
            @enderror

            <div class="register__form__input">
                <label for="email">メールアドレス</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required>
            </div>
            @error('email')
                <div class="error-message">
                    {{ $message }}
                </div>
            @enderror

            <div class="register__form__input">
                <label for="password">パスワード</label>
                <input type="password" name="password" id="password" required>
            </div>
            @error('password')
                <div class="error-message">
                    {{ $message }}
                </div>
            @enderror

            <div class="register__form__input">
                <label for="password_confirmation">確認用パスワード</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required>
            </div>
            @error('password_confirmation')
                <div class="error-message">
                    {{ $message }}
                </div>
            @enderror
            <button type="submit" class="register__button">登録する</button>
        </form>
        <div class="register__form__link">
            <a href="{{ route('login') }}" style="text-decoration: none; color:#0073CC;">ログインはこちら</a>
        </div>
    </div>
@endsection
