@extends('layouts.app')
@php($editing = is_array($medicine))
@section('title', ($editing ? 'Sửa' : 'Thêm').' thuốc - PIMS')
@section('heading', $editing ? 'Cập nhật thuốc' : 'Thêm thuốc')
@section('subheading', 'Tồn kho được chuẩn hóa theo đơn vị cơ sở')
@section('content')
<div class="card content-card"><div class="card-body p-4">
<form class="row g-3" method="POST" action="{{ $editing ? route('medicines.update', $medicine['id']) : route('medicines.store') }}">@csrf @if($editing) @method('PUT') @endif
    <div class="col-md-4"><label class="form-label">Mã thuốc</label><input class="form-control" name="code" value="{{ old('code', $medicine['code'] ?? '') }}" required></div>
    <div class="col-md-8"><label class="form-label">Tên thuốc</label><input class="form-control" name="name" value="{{ old('name', $medicine['name'] ?? '') }}" required></div>
    <div class="col-md-6"><label class="form-label">Hoạt chất</label><input class="form-control" name="active_ingredient" value="{{ old('active_ingredient', $medicine['active_ingredient'] ?? '') }}" required></div>
    <div class="col-md-3"><label class="form-label">Hàm lượng</label><input class="form-control" name="strength" value="{{ old('strength', $medicine['strength'] ?? '') }}" required></div>
    <div class="col-md-3"><label class="form-label">Dạng bào chế</label><input class="form-control" name="dosage_form" value="{{ old('dosage_form', $medicine['dosage_form'] ?? '') }}"></div>
    <div class="col-md-4"><label class="form-label">Đơn vị cơ sở</label><select class="form-select" name="base_unit_id" required>@foreach($units as $unit)<option value="{{ $unit['id'] }}" @selected((int) old('base_unit_id', $medicine['base_unit_id'] ?? 0) === $unit['id'])>{{ $unit['name'] }} ({{ $unit['code'] }})</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Ngưỡng tồn tối thiểu</label><input type="number" step="0.001" min="0" class="form-control" name="minimum_stock" value="{{ old('minimum_stock', $medicine['minimum_stock'] ?? 0) }}" required></div>
    <div class="col-md-4"><label class="form-label">Trạng thái</label><select class="form-select" name="is_active"><option value="1" @selected(old('is_active', $medicine['is_active'] ?? true))>Đang sử dụng</option><option value="0" @selected(!old('is_active', $medicine['is_active'] ?? true))>Ngừng sử dụng</option></select></div>
    <div class="col-12"><button class="btn btn-primary">Lưu thuốc</button> <a class="btn btn-outline-secondary" href="{{ route('medicines.index') }}">Hủy</a></div>
</form></div></div>
@endsection
