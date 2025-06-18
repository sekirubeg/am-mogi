@extends('layouts.app')
@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')

    <div class="container text-center">
        <h1 class="title">勤怠詳細</h1>
        <form action="{{ route('attendance.application', ['id' => $attendance->id]) }}" method="post">
            @csrf
            <table>
                <tr>
                    <th>名前</th>
                    <td>{{ $attendance->user->name }}</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>{{ $formatted['year'] }}年</td>
                    <td>{{ $formatted['month_day'] }}</td>
                </tr>
                <tr>
                    <th>出勤・退勤</th>
                    <td class="start-end">

                            <input type="text" value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}"
                                name="clock_in" class="start">
                    </td>
                    <td>〜</td>
                    <td>
                        @if ($attendance->clock_out)
                            <input type="text" value="{{ old('clock_out',\Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}"
                                name="clock_out" class="end">
                    </td>
                </tr>
                @foreach ($attendance->attendance_breaks as $break)
                    <tr>
                        <th>休憩 {{ $loop->iteration }}</th>
                        <td>
                            <input type="text"
                                value="{{ old("breaks.{$break->id}.break_start", \Carbon\Carbon::parse($break->break_start)->format('H:i')) }}"
                                name="breaks[{{ $break->id }}][break_start]" class="start">
                        </td>
                        <td>〜</td>
                        <td>
                            <input type="text"
                                value="{{ old("breaks.{$break->id}.break_end", \Carbon\Carbon::parse($break->break_end)->format('H:i')) }}"
                                name="breaks[{{ $break->id }}][break_end]" class="end">
                        </td>
                    </tr>
                @endforeach
                <tr class="last-row">
                    <th>備考</th>
                    <td >
                        <textarea name="remarks" id="" cols="30" rows="3" style="border: 1px solid #d9d9d9">{{ $attendance->remarks }}</textarea>
                    </td>
                </tr>
            </table>
            <div class="button-group">
                @if ($attendance->status == 0)
                    <p style="color: red;">*承認待ちのため修正はできません。</p>
                @else
                <p style="color: red;">*承認済み</p>
            </div>
            <input type="hidden" name="date" value="{{ $date }}">
        </form>
    </div>
@endsection
