@extends('layouts.app')
@section('title', 'Kê đơn - PIMS')
@section('heading', 'Kê đơn thuốc')
@section('subheading', 'Tạo đơn mới để dược sĩ kiểm tra và cấp phát')
@section('content')
<form method="POST" action="{{ route('prescriptions.store') }}" class="card content-card"><div class="card-body">@csrf
<div class="row g-3"><div class="col-md-6"><label class="form-label">Tên bệnh nhân *</label><input class="form-control" name="patient_name" value="{{ old('patient_name') }}" required></div><div class="col-md-3"><label class="form-label">Mã bệnh nhân</label><input class="form-control" name="patient_code" value="{{ old('patient_code') }}"></div><div class="col-12"><label class="form-label">Chẩn đoán</label><textarea class="form-control" name="diagnosis">{{ old('diagnosis') }}</textarea></div></div>
<hr><div class="d-flex justify-content-between mb-2"><h2 class="h6">Danh sách thuốc</h2><button type="button" class="btn btn-sm btn-outline-primary" onclick="addRow()">Thêm thuốc</button></div><div id="items"></div>
<button class="btn btn-primary mt-3">Lưu đơn thuốc</button></div></form>
<template id="row"><div class="row g-2 mb-2 prescription-row"><div class="col-md-5"><select class="form-select medicine" required><option value="">Chọn thuốc</option>@foreach($medicines as $medicine)<option value="{{ $medicine['id'] }}">{{ $medicine['code'] }} - {{ $medicine['name'] }}</option>@endforeach</select></div><div class="col-md-2"><input type="number" step="0.001" min="0.001" class="form-control quantity" placeholder="Số lượng" required></div><div class="col-md-4"><input class="form-control instructions" placeholder="Cách dùng" required></div><div class="col-md-1"><button type="button" class="btn btn-outline-danger" onclick="this.closest('.prescription-row').remove();renameRows()">×</button></div></div></template>
<script>function renameRows(){document.querySelectorAll('.prescription-row').forEach((r,i)=>{r.querySelector('.medicine').name=`items[${i}][medicine_id]`;r.querySelector('.quantity').name=`items[${i}][quantity]`;r.querySelector('.instructions').name=`items[${i}][instructions]`;});}function addRow(){document.getElementById('items').append(document.getElementById('row').content.cloneNode(true));renameRows();}addRow();</script>
@endsection
