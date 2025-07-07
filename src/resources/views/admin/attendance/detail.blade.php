@extends('layouts.app1')
@section('title', '管理者勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')

    <div class="container text-center">
        <h1 class="title">勤怠詳細</h1>
        @if (session('message'))
            <div class="alert alert-success">
                {{ session('message') }}
            </div>
        @endif
        <form action="{{ route('admin.attendance.application', ['id' => $attendance->id]) }}" method="post">
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
                        <td>
                            @if ($attendance->clock_in)
                                <input type="text"
                                    value="{{ old('clock_in', \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')) }}"
                                    name="clock_in" class="start">
                            @else
                                <span class="text-muted">未登録</span>
                            @endif
                            @error('clock_in')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>〜</td>
                        <td>
                            @if ($attendance->clock_out)
                                <input type="text"
                                    value="{{ old('clock_out', \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')) }}"
                                    name="clock_out" class="end">
                            @else
                                <input type="text"
                                name="clock_out" class="end">
                            @endif

                            @error('clock_out')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </td>
                </tr>
                @foreach ($attendance->attendance_breaks as $break)
                    <tr>
                        <th>休憩 {{ $loop->iteration }}</th>
                        <td>
                            <input type="text"
                                value="{{ old("breaks.{$break->id}.break_start", \Carbon\Carbon::parse($break->break_start)->format('H:i')) }}"
                                name="breaks[{{ $break->id }}][break_start]" class="start">
                            @error("breaks.{$break->id}.break_start")
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </td>
                        <td>〜</td>
                        <td>
                            <input type="text"
                                value="{{ old("breaks.{$break->id}.break_end", \Carbon\Carbon::parse($break->break_end)->format('H:i')) }}"
                                name="breaks[{{ $break->id }}][break_end]" class="end">
                            @error("breaks.{$break->id}.break_end")
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </td>
                    </tr>
                @endforeach
                <tr>
                    @php
                        $nextBreakNumber =
                            ($attendance->attendance_breaks ? $attendance->attendance_breaks->count() : 0) + 1;
                    @endphp
                    <th>休憩 {{ $nextBreakNumber }}</th>
                    <td>
                        <input type="text" name="new_breaks[0][break_start]" class="start"
                            value="{{ old('new_breaks.0.break_start') }}">
                        @error('new_breaks.0.break_start')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </td>
                    <td>〜</td>
                    <td>
                        <input type="text" name="new_breaks[0][break_end]" class="end"
                        value="{{ old('new_breaks.0.break_end') }}">
                        @error('new_breaks.0.break_end')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>

                <tr class="last-row">
                    <th>備考</th>
                    <td>
                        <textarea name="remarks" id="" cols="30" rows="3" style="border: 1px solid #d9d9d9">{{ $attendance->remarks }}</textarea>
                        @error('remarks')
                            <div class="error">{{ $message }}</div>
                        @enderror
                    </td>
                </tr>
            </table>
            <div class="button-group">
                    <button type="submit" class="btn btn-primary"
                        style="background-color: #000000 ; padding:5px 40px; font-size:20px;">修正</button>
            </div>
            @if (isset($attendance->request) && $attendance->request->review_status === 0)
                <p style="color: red; text-align:right; margin-top:1vh;">*承認していない申請があります。</p>
            @endif
            <input type="hidden" name="date" value="{{ $date }}">
        </form>
    </div>
@endsection
