@extends('layouts.app')
@section('title', '勤怠管理')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@php
    $is_on_break = $attendance && $attendance->attendance_breaks()->whereNull('break_end')->exists();
@endphp

@section('content')
    <div class="text-center">
        <p class="text-muted">
            @if($attendance && $attendance->clock_out)
                退勤済
            @elseif ($attendance)
                @if(!$is_on_break)
                    出勤中
                @else
                    休憩中
                @endif
            @else
                勤務外
            @endif
        </p>
        <h2 id="date"></h2>
        <h1 id="clock" class="display-1">--:--</h1>
        @if ($attendance && $attendance->clock_out)
            <p>お疲れ様でした！</p>

        @elseif($attendance)
            @if(!$is_on_break)
            <div class="d-flex justify-content-center flex-row align-items-center" >
                <form action="{{ route('attendance.end') }}" method="POST" style="text-align:center;" class="mt-5 ">
                    @csrf
                    <button type="submit" class="btn btn-dark">退 勤</button>
                </form>
                <form action="{{ route('attendance.break.start') }}" method="POST" style="text-align:center;" class="mt-5" >
                    @csrf
                    <button type="submit" class="btn" style="background-color: #FFFFFF; margin-left:3vw; ">休 憩 入</button>
                </form>
            </div>
            @else
                <form action="{{ route('attendance.break.end') }}" method="POST" style="text-align:center;" class="mt-5 ">
                    @csrf
                    <button type="submit" class="btn" style="background-color: #FFFFFF;">休 憩 戻</button>
                </form>
            @endif

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
