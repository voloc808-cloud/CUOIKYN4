@extends('layouts.app')
@section('title', 'Chi tiết phiếu nhập - PIMS')
@section('heading', 'Phiếu nhập '.$receipt['receipt_code'])
@section('subheading', 'Ngày nhập '.date('d/m/Y H:i', strtotime($receipt['imported_at'])))
@section('content')
<div class="card content-card mb-3"><div class="card-body"><div class="row g-3"><div class="col-md-3"><small class="text-secondary">Nhà cung cấp</small><div>#{{ $receipt['supplier_id'] }}</div></div><div class="col-md-3"><small class="text-secondary">Số hóa đơn</small><div>{{ $receipt['invoice_number'] ?: '—' }}</div></div><div class="col-md-6"><small class="text-secondary">Ghi chú</small><div>{{ $receipt['note'] ?: '—' }}</div></div></div></div></div>
<div class="card content-card"><div class="card-body"><div class="table-responsive"><table class="table"><thead><tr><th>Thuốc</th><th>Mã lô</th><th>NSX</th><th>HSD</th><th>SL nhập</th><th>SL quy đổi tồn</th></tr></thead><tbody>
@foreach($receipt['details'] as $detail)<tr><td>{{ $detail['medicine']['name'] ?? '#'.$detail['medicine_id'] }}</td><td class="fw-semibold">{{ $detail['batch_code'] }}</td><td>{{ date('d/m/Y', strtotime($detail['manufactured_at'])) }}</td><td>{{ date('d/m/Y', strtotime($detail['expires_at'])) }}</td><td>{{ $detail['quantity'] }}</td><td>{{ $detail['batch']['quantity_remaining'] ?? '' }}</td></tr>@endforeach
</tbody></table></div><a href="{{ route('inventory.receipts') }}" class="btn btn-outline-secondary">Quay lại</a></div></div>
@endsection
