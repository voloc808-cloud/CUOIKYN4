@extends('layouts.app')
@php($editing = is_array($supplier))
@section('title', ($editing ? 'Sửa' : 'Thêm').' nhà cung cấp - PIMS')
@section('heading', $editing ? 'Cập nhật nhà cung cấp' : 'Thêm nhà cung cấp')
@section('subheading', 'Thông tin dùng khi lập phiếu nhập kho')
@section('content')
<div class="card content-card"><div class="card-body p-4">
<form class="row g-3" method="POST" action="{{ $editing ? route('suppliers.update', $supplier['id']) : route('suppliers.store') }}">@csrf @if($editing) @method('PUT') @endif
    <div class="col-md-4"><label class="form-label">Mã nhà cung cấp</label><input class="form-control" name="code" value="{{ old('code', $supplier['code'] ?? '') }}" required></div>
    <div class="col-md-8"><label class="form-label">Tên nhà cung cấp</label><input class="form-control" name="name" value="{{ old('name', $supplier['name'] ?? '') }}" required></div>
    <div class="col-md-4"><label class="form-label">Người liên hệ</label><input class="form-control" name="contact_person" value="{{ old('contact_person', $supplier['contact_person'] ?? '') }}"></div>
    <div class="col-md-4"><label class="form-label">Điện thoại</label><input class="form-control" name="phone" value="{{ old('phone', $supplier['phone'] ?? '') }}"></div>
    <div class="col-md-4"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="{{ old('email', $supplier['email'] ?? '') }}"></div>
    <div class="col-md-4"><label class="form-label">Mã số thuế</label><input class="form-control" name="tax_code" value="{{ old('tax_code', $supplier['tax_code'] ?? '') }}"></div>
    <div class="col-md-6"><label class="form-label">Địa chỉ</label><input class="form-control" name="address" value="{{ old('address', $supplier['address'] ?? '') }}"></div>
    <div class="col-md-2"><label class="form-label">Trạng thái</label><select class="form-select" name="is_active"><option value="1" @selected(old('is_active', $supplier['is_active'] ?? true))>Hoạt động</option><option value="0" @selected(!old('is_active', $supplier['is_active'] ?? true))>Ngừng</option></select></div>
    <div class="col-12"><button class="btn btn-primary">Lưu nhà cung cấp</button> <a class="btn btn-outline-secondary" href="{{ route('suppliers.index') }}">Hủy</a></div>
</form></div></div>
@endsection
