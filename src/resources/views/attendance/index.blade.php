@extends('layouts.app')
@section('title', '勤怠管理')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
    <div class="text-center">
        <p class="text-muted">
            @if ($attendance)
                勤務中
            @else
                勤務外
            @endif
        </p>
        <h2 id="date"></h2>
        <h1 id="clock" class="display-1">--:--</h1>
        @if ($attendance)
        <form action="{{ route('attendance.end') }}" method="POST" style="text-align:center;" class="mt-5">
            @csrf
            <button type="submit" class="btn btn-dark">退勤</button>
        </form>
        @else
        <form action="{{ route('attendance.start') }}" method="POST" style="text-align:center;" class="mt-5">
            @csrf
            <button type="submit" class="btn btn-dark">出勤</button>
        </form>
        @endif
    </div>
    <script>
        function updateClock() {
            const now = new Date();

            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'short'
            };
            const formattedDate = now.toLocaleDateString('ja-JP', options);
            const formattedTime = now.toLocaleTimeString('ja-JP', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            document.getElementById('date').textContent = formattedDate;
            document.getElementById('clock').textContent = formattedTime;
        }

        setInterval(updateClock, 1000); // 1秒ごとに更新
        updateClock(); // 初期表示
    </script>

@endsection
