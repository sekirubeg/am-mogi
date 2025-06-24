@extends('layouts.app1')
@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/apply.css') }}">
@endsection

@section('content')
<div class="container text-center">
    <h1 class="title">申請一覧</h1>

    {{-- タブ --}}
    <div class="tabs">
        <a href="{{ route('admin.attendance.application.list', ['review_status' => 0]) }}"
           class="tab {{ $status == 0 ? 'active' : '' }}">承認待ち</a>
        <a href="{{ route('admin.attendance.application.list', ['review_status' => 1]) }}"
           class="tab {{ $status != 0 ? 'active' : '' }}">承認済み</a>
    </div>

    <table class="application-table">
        <thead>
            <tr>
                <th>状態</th>
                <th>名前</th>
                <th>対象日時</th>
                <th>申請理由</th>
                <th>申請日時</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requests as $request)
                <tr>
                    <td>{{ $request->review_status == '0' ? '承認待ち' : '承認済み' }}</td>
                    <td>{{ $request->user->name }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->attendance->attendance_date)->format('Y/m/d') }}</td>
                    <td>{{ $request->remarks }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->created_at)->format('Y/m/d') }}</td>
                    <td><a href="{{ route('admin.attendance.show', $request->id) }}" style="color:#000000; font-weight:700; text-decoration:none;">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
