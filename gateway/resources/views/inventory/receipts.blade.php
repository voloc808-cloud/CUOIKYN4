@extends('layouts.app')
@section('title', 'Phiếu nhập kho - PIMS')
@section('heading', 'Phiếu nhập kho')
@section('subheading', 'Nhập thuốc theo từng lô và hạn sử dụng')
@section('content')
<div class="card content-card"><div class="card-body">
<div class="d-flex justify-content-between gap-2 mb-3"><form class="d-flex gap-2"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Mã phiếu hoặc số hóa đơn"><button class="btn btn-outline-primary">Tìm</button></form><a class="btn btn-primary" href="{{ route('inventory.receipts.create') }}">Lập phiếu nhập</a></div>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Mã phiếu</th><th>Ngày nhập</th><th>Nhà cung cấp</th><th>Số hóa đơn</th><th>Số lô</th><th></th></tr></thead><tbody>
@forelse(($receipts['data'] ?? []) as $receipt)<tr><td class="fw-semibold">{{ $receipt['receipt_code'] }}</td><td>{{ date('d/m/Y H:i', strtotime($receipt['imported_at'])) }}</td><td>{{ $receipt['supplier']['name'] ?? '#'.$receipt['supplier_id'] }}</td><td>{{ $receipt['invoice_number'] }}</td><td>{{ $receipt['details_count'] }}</td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('inventory.receipts.show', $receipt['id']) }}">Chi tiết</a></td></tr>
@empty<tr><td colspan="6" class="text-center text-secondary py-4">Chưa có phiếu nhập.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination', ['paginator' => $receipts])
</div></div>
@endsection
