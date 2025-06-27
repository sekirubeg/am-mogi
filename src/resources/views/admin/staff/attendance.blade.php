@extends('layouts.app1')
@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')
<div class="container text-center">
    <h1 class="title">{{ $user->name }}さんの勤怠一覧</h1>

    <div class="calender  gap-3 mb-5">
        <a href="{{ route('admin.staff.attendance', ['id'=>$user->id,'month' => $prevMonth]) }}">← 前月</a>
        <div><img src="{{ asset('images/calendar.png') }}" alt="aa" class="calendar-img"><strong>{{ \Carbon\Carbon::parse($month)->format('Y年m月') }}</strong></div>
        <a href="{{ route('admin.staff.attendance', ['id'=>$user->id,'month' => $nextMonth]) }}">翌月 →</a>
    </div>

    <table class="table text-center" border="0" style="margin-bottom: 0;">
        <thead class="table-light">
            <tr class="table-header">
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($datesInMonth as $date)
                <tr class="table-data">
                    {{-- 休憩合計時間（例：1:00）を計算 --}}
                    @php
                        $dateStr = $date->format('Y-m-d');
                        $a = $attendancesByDate[$dateStr] ?? null;
                        $totalBreak = 0;
                        if ($a) {
                            $totalBreak = $a->attendance_breaks->sum(function($b) {
                            return $b->break_end && $b->break_start
                                ? \Carbon\Carbon::parse($b->break_end)->diffInSeconds($b->break_start)
                                : 0;
                            });
                        }
                    @endphp
                    <td>{{ $date->locale('ja')->isoFormat('MM/DD (dd)') }}</td>
                    <td>{{ $a && $a->clock_in ? \Carbon\Carbon::parse($a->clock_in)->format('H:i') : '' }}</td>
                    <td>{{ $a && $a->clock_out ? \Carbon\Carbon::parse($a->clock_out)->format('H:i') : '' }}</td>
                    <td>
                        {{ $a ? gmdate('H:i', $totalBreak) : '' }}
                    </td>
                    <td>
                        @if ($a && $a->clock_in && $a->clock_out)
                            @php
                                $workSec = \Carbon\Carbon::parse($a->clock_out)->diffInSeconds($a->clock_in) - $totalBreak;
                                echo gmdate('H:i', $workSec);
                            @endphp
                        @else
                            -
                        @endif
                    </td>
                    <td>

                        @if ($a)
                            <a href="{{route('admin.attendance.detail', ['id' => $a->id]) }}" style="text-decoration: none; color:#000000;">詳細</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4 mb-5 text-end" >
        <form action="{{ route('admin.attendance.staff.csv') }}" method="post" >
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">
            <input type="hidden" name="month" value="{{ $month }}">
            <button type="submit" class="btn btn-primary" style="background-color: #000000; padding:7px 40px; border-radius:3px; font-size:22px;">CSV出力</button>
        </form>
    </div>
</div>
@endsection
