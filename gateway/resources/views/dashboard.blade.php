@extends('layouts.app')
@section('title', 'Tổng quan - PIMS')
@section('heading', 'Xin chào, '.($currentUser['name'] ?? ''))
@section('subheading', 'Phiên đăng nhập được xác thực bởi auth-service')
@section('content')
<div class="row g-3">
    <div class="col-md-6 col-xl-4"><div class="card content-card h-100"><div class="card-body"><div class="text-secondary mb-2">Vai trò</div><div class="h4">{{ match ($currentUser['role_code'] ?? '') { 'ADMIN' => 'Quản trị viên', 'DOCTOR' => 'Bác sĩ', 'PHARMACIST' => 'Dược sĩ / Nhân viên kho', default => '' } }}</div></div></div></div>
    <div class="col-md-6 col-xl-4"><div class="card content-card h-100"><div class="card-body"><div class="text-secondary mb-2">Tài khoản</div><div class="h5">{{ $currentUser['email'] ?? '' }}</div><span class="badge text-bg-success">Đang hoạt động</span></div></div></div>
    <div class="col-md-6 col-xl-4"><div class="card content-card h-100"><div class="card-body"><div class="text-secondary mb-2">Chức năng</div><div class="h4">{{ ($currentUser['role_code'] ?? '') === 'ADMIN' ? 'Quản lý tài khoản' : (($currentUser['role_code'] ?? '') === 'DOCTOR' ? 'Kê đơn thuốc' : 'Quản lý kho dược') }}</div><p class="mb-0 text-secondary">Chọn chức năng ở menu bên trái để bắt đầu.</p></div></div></div>
</div>
@endsection
