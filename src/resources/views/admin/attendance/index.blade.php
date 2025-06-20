@extends('layouts.app1')
@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list.css') }}">
@endsection

@section('content')

<div class="container text-center">
    <h1 class="title">{{ $targetDate->format('Y年n月d日') }}の勤怠一覧</h1>

    <div class="calender  gap-3 mb-5">
        <a href="{{ route('admin.attendance.list', ['date' => $prevDay]) }}">← 前日</a>
        <div><img src="{{ asset('images/calendar.png') }}" alt="aa" class="calendar-img"><strong>{{ $targetDate->format('Y年m月d日') }}</strong></div>
        <a href="{{ route('admin.attendance.list', ['date' => $nextDay]) }}">翌日 →</a>
    </div>

    <table class="table text-center" border="0">
        <thead class="table-light">
            <tr class="table-header">
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances[$targetDate->toDateString()] ?? [] as $a)
                <tr class="table-data">
                    @php
                        $totalBreak = $a->attendance_breaks->sum(function($b) {
                            return $b->break_end && $b->break_start
                                ? \Carbon\Carbon::parse($b->break_end)->diffInSeconds($b->break_start)
                                : 0;
                        });
                    @endphp
                    <td>{{ $a->user->name }}</td>
                    <td>{{ $a->clock_in ? \Carbon\Carbon::parse($a->clock_in)->format('H:i') : '' }}</td>
                    <td>{{ $a->clock_out ? \Carbon\Carbon::parse($a->clock_out)->format('H:i') : '' }}</td>
                    <td>{{ gmdate('H:i', $totalBreak) }}</td>
                    <td>
                        @if ($a->clock_in && $a->clock_out)
                            @php
                                $workSec = \Carbon\Carbon::parse($a->clock_out)->diffInSeconds($a->clock_in) - $totalBreak;
                            @endphp
                            {{ gmdate('H:i', $workSec) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <a href="{{route('admin.attendance.detail', ['id' => $a->id]) }}" style="text-decoration: none; color:#000000;">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
