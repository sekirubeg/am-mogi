<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__logo" style="margin-left: 2vw; margin-right: 1vw;">
            <a href="{{ route('attendance') }}">
                <img src="{{ asset('images/logo.svg') }}" alt="Attendance Management Logo">
            </a>
        </div>

        <nav class="header__nav">
            <ul>
                @auth
                <li><a href="{{ route('attendance') }}">勤怠</a></li>
                <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                <li><a href="{{ route('attendance.application.list') }}">申請</a></li>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        style="background:transparent; color:#fff; border:none; cursor: pointer; font-size:16px;">ログアウト</button>
                </form>
                @endauth
            </ul>
        </nav>
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>
