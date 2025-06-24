@extends('layouts.app1')
@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff.css') }}">
@endsection

@section('content')

<div class="container text-center" style="margin-top: 5vh;">
    <h1 class="title" style="margin-bottom: 3vh">スタッフ一覧</h1>

    <table class="table text-center" border="0">
        <thead class="table-light">
            <tr class="table-header">
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
            @foreach($staff as $staff)
            <tr>
                <td>{{ $staff->name }}</td>
                <td>{{ $staff->email }}</td>
                <td>
                    <a href="{{ route('admin.staff.attendance', $staff->id) }}" style="text-decoration: none; color:#000000; font-weight:bold;">詳細</a>
                </td>
            </tr>
            @endforeach
        <tbody>
        </tbody>
    </table>
</div>
@endsection
