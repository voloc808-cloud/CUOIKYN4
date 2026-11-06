@extends('layouts.app')
@section('title', 'Lập phiếu nhập - PIMS')
@section('heading', 'Lập phiếu nhập kho')
@section('subheading', 'Mỗi dòng tương ứng một lô thuốc')
@section('content')
<form method="POST" action="{{ route('inventory.receipts.store') }}">@csrf
<div class="card content-card mb-3"><div class="card-body row g-3">
    <div class="col-md-3"><label class="form-label">Mã phiếu</label><input class="form-control" name="receipt_code" value="{{ old('receipt_code', 'PN'.now()->format('YmdHis')) }}" required></div>
    <div class="col-md-4"><label class="form-label">Nhà cung cấp</label><select class="form-select" name="supplier_id" required>@foreach($suppliers as $supplier)<option value="{{ $supplier['id'] }}">{{ $supplier['name'] }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label">Ngày nhập</label><input type="datetime-local" class="form-control" name="imported_at" value="{{ old('imported_at', now()->format('Y-m-d\TH:i')) }}" required></div>
    <div class="col-md-2"><label class="form-label">Số hóa đơn</label><input class="form-control" name="invoice_number" value="{{ old('invoice_number') }}"></div>
    <div class="col-12"><label class="form-label">Ghi chú</label><input class="form-control" name="note" value="{{ old('note') }}"></div>
</div></div>
<div class="card content-card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Danh sách lô</h2><button type="button" class="btn btn-outline-primary btn-sm" id="add-row">Thêm dòng</button></div>
    <div id="items"></div>
    <div class="mt-3"><button class="btn btn-primary">Hoàn tất nhập kho</button> <a href="{{ route('inventory.receipts') }}" class="btn btn-outline-secondary">Hủy</a></div>
</div></div>
</form>
<template id="item-template">
<div class="row g-2 align-items-end border rounded p-2 mb-2 inventory-row">
    <div class="col-md-3"><label class="form-label">Thuốc</label><select class="form-select medicine" data-field="medicine_id" required><option value="">Chọn thuốc</option>@foreach($medicines as $medicine)<option value="{{ $medicine['id'] }}">{{ $medicine['code'] }} - {{ $medicine['name'] }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label">Đơn vị nhập</label><select class="form-select unit" data-field="unit_id" required></select></div>
    <div class="col-md-2"><label class="form-label">Mã lô</label><input class="form-control" data-field="batch_code" required></div>
    <div class="col-md-2"><label class="form-label">NSX</label><input type="date" class="form-control" data-field="manufactured_at" required></div>
    <div class="col-md-2"><label class="form-label">HSD</label><input type="date" class="form-control" data-field="expires_at" required></div>
    <div class="col-md-1"><button type="button" class="btn btn-outline-danger remove-row">×</button></div>
    <div class="col-md-3"><label class="form-label">Số lượng</label><input type="number" step="0.001" min="0.001" class="form-control" data-field="quantity" required></div>
    <div class="col-md-3"><label class="form-label">Đơn giá</label><input type="number" step="0.01" min="0" class="form-control" data-field="unit_cost" value="0"></div>
    <div class="col-md-6 small text-secondary conversion-note"></div>
</div>
</template>
<script>
const medicines = @json(collect($medicines)->keyBy('id'));
const units = @json(collect($units)->keyBy('id'));
const container = document.getElementById('items');
function renumber() {
    [...container.querySelectorAll('.inventory-row')].forEach((row, index) => {
        row.querySelectorAll('[data-field]').forEach(input => input.name = 'items[' + index + '][' + input.dataset.field + ']');
    });
}
function updateUnits(row) {
    const medicine = medicines[row.querySelector('.medicine').value];
    const select = row.querySelector('.unit');
    select.innerHTML = '';
    if (!medicine) return;
    const choices = new Map([[String(medicine.base_unit_id), {id: medicine.base_unit_id, factor: 1}]]);
    (medicine.conversions || []).forEach(conversion => {
        if (String(conversion.to_unit_id) === String(medicine.base_unit_id)) choices.set(String(conversion.from_unit_id), {id: conversion.from_unit_id, factor: conversion.factor});
    });
    choices.forEach(choice => {
        const option = document.createElement('option');
        option.value = choice.id;
        option.textContent = units[choice.id].name + ' (x' + Number(choice.factor) + ')';
        select.appendChild(option);
    });
    row.querySelector('.conversion-note').textContent = 'Tồn kho được quy đổi về ' + units[medicine.base_unit_id].name + '.';
}
function addRow() {
    const row = document.getElementById('item-template').content.firstElementChild.cloneNode(true);
    row.querySelector('.medicine').addEventListener('change', () => updateUnits(row));
    row.querySelector('.remove-row').addEventListener('click', () => { if (container.children.length > 1) { row.remove(); renumber(); } });
    container.appendChild(row); renumber();
}
document.getElementById('add-row').addEventListener('click', addRow);
addRow();
</script>
@endsection
