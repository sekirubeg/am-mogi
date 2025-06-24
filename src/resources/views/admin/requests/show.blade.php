@extends('layouts.app1')
@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/detail.css') }}">
@endsection

@section('content')

    <div class="container text-center">
        <h1 class="title">勤怠詳細</h1>
        <form action="{{ route('admin.attendance.approve', ['id' => $attendance->id]) }}" method="post" id="approval-form">
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
                        {{ \Carbon\Carbon::parse($attendanceRequest->requested_clock_in)->format('H:i') }}
                        <input type="hidden" name="clock_in"
                            value="{{ \Carbon\Carbon::parse($attendanceRequest->requested_clock_in)->format('H:i') }}">
                    </td>
                    <td>〜</td>
                    <td>
                        {{ \Carbon\Carbon::parse($attendanceRequest->requested_clock_out)->format('H:i') }}
                        <input type="hidden" name="clock_out"
                            value="{{ \Carbon\Carbon::parse($attendanceRequest->requested_clock_out)->format('H:i') }}">
                    </td>
                </tr>
                @foreach ($attendanceRequestBreaks as $break)
                    <tr>
                        <th>休憩 {{ $loop->iteration }}</th>
                        <td>
                            {{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}
                            <input type="hidden" name="breaks[{{ $loop->index }}][break_start]"
                                value="{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}">
                        </td>
                        <td>〜</td>
                        <td>
                            {{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}
                            <input type="hidden" name="breaks[{{ $loop->index }}][break_end]"
                                value="{{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}">
                        </td>
                    </tr>
                @endforeach
                <tr class="last-row">
                    <th>備考</th>
                    <td colspan="2">
                        {{ $attendanceRequest->remarks }}
                        <input type="hidden" name="remarks" value="{{ $attendanceRequest->remarks }}">
                    </td>
                </tr>
            </table>
            <div class="button-group">
                <button type="submit" class="btn btn-primary"
                    style="background-color: #000000 ; padding:5px 40px; font-size:20px;" id="approve-button">承認</button>
            </div>
            <input type="hidden" name="date" value="{{ $date }}">
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('approval-form');
            const button = document.getElementById('approve-button');

            form.addEventListener('submit', function (e) {
                e.preventDefault(); // 通常のフォーム送信を止める

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('送信に失敗しました');
                    return response.json(); // 成功時はJSONを返すようにする
                })
                .then(data => {
                    // 成功時の処理
                    button.textContent = '承認済み';
                    button.disabled = true;
                })
                .catch(error => {
                    alert('エラーが発生しました：' + error.message);
                });
            });
        });
    </script>
@endsection
