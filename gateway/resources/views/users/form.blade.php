@extends('layouts.app')
@php($editing = is_array($user))
@section('title', ($editing ? 'Sửa' : 'Thêm').' tài khoản - PIMS')
@section('heading', $editing ? 'Cập nhật tài khoản' : 'Thêm tài khoản')
@section('subheading', 'Vai trò hệ thống chỉ gồm Bác sĩ và Dược sĩ / Nhân viên kho')
@section('content')
<div class="card content-card"><div class="card-body p-4">
    <form method="POST" action="{{ $editing ? route('users.update', $user['id']) : route('users.store') }}" class="row g-3">
        @csrf @if($editing) @method('PUT') @endif
        <div class="col-md-6"><label class="form-label">Họ tên</label><input class="form-control" name="name" value="{{ old('name', $user['name'] ?? '') }}" required></div>
        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $user['email'] ?? '') }}" required></div>
        <div class="col-md-6"><label class="form-label">Vai trò</label><select class="form-select" name="role" required><option value="DOCTOR" @selected(old('role', $user['role_code'] ?? '') === 'DOCTOR')>Bác sĩ</option><option value="PHARMACIST" @selected(old('role', $user['role_code'] ?? '') === 'PHARMACIST')>Dược sĩ / Nhân viên kho</option></select></div>
        <div class="col-md-6"><label class="form-label">Trạng thái</label><select class="form-select" name="is_active"><option value="1" @selected(old('is_active', $user['is_active'] ?? true))>Hoạt động</option><option value="0" @selected(!old('is_active', $user['is_active'] ?? true))>Khóa</option></select></div>
        <div class="col-md-6"><label class="form-label">Mật khẩu {{ $editing ? '(để trống nếu không đổi)' : '' }}</label><input class="form-control" type="password" name="password" {{ $editing ? '' : 'required' }}></div>
        <div class="col-md-6"><label class="form-label">Xác nhận mật khẩu</label><input class="form-control" type="password" name="password_confirmation" {{ $editing ? '' : 'required' }}></div>
        <div class="col-12 d-flex gap-2"><button class="btn btn-primary">{{ $editing ? 'Lưu thay đổi' : 'Tạo tài khoản' }}</button><a class="btn btn-outline-secondary" href="{{ route('users.index') }}">Hủy</a></div>
    </form>
</div></div>
@endsection
