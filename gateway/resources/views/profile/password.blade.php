@extends('layouts.app')
@section('title', 'Đổi mật khẩu - PIMS')
@section('heading', 'Đổi mật khẩu')
@section('subheading', 'Mật khẩu mới cần ít nhất 8 ký tự')
@section('content')
<div class="card content-card"><div class="card-body p-4">
    <form method="POST" action="{{ route('profile.password.update') }}" class="row g-3">
        @csrf @method('PUT')
        <div class="col-12"><label class="form-label">Mật khẩu hiện tại</label><input type="password" name="current_password" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Mật khẩu mới</label><input type="password" name="password" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Xác nhận mật khẩu</label><input type="password" name="password_confirmation" class="form-control" required></div>
        <div class="col-12"><button class="btn btn-primary">Cập nhật mật khẩu</button></div>
    </form>
</div></div>
@endsection
