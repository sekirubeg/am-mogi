@extends('layouts.app')
@section('title', 'メール認証')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/mail.css') }}">
@endsection

@section('content')
<div class="container">
    <h3>登録していただいたメールアドレスに認証メールを送付しました。</h3>
    <h3>メール認証を完了してください。</h3>

    <a href="https://mailtrap.io/" class="button">認証はこちらから</a>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="confirm-button">確認メールを再送する</button>
    </form>
</div>
@endsection
