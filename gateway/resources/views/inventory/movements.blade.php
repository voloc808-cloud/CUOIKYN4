@extends('layouts.app')
@section('title', 'Biến động kho - PIMS')
@section('heading', 'Lịch sử biến động kho')
@section('subheading', 'Nhật ký bất biến của mọi thay đổi tồn kho')
@section('content')
<div class="card content-card"><div class="card-body"><div class="table-responsive"><table class="table align-middle">
<thead><tr><th>Thời gian</th><th>Thuốc</th><th>Lô</th><th>Loại</th><th>Số lượng</th><th>Tham chiếu</th><th>Người thực hiện</th></tr></thead><tbody>
@forelse(($movements['data'] ?? []) as $movement)<tr><td>{{ date('d/m/Y H:i:s', strtotime($movement['created_at'])) }}</td><td>{{ $movement['medicine']['name'] ?? '#'.$movement['medicine_id'] }}</td><td>{{ $movement['batch']['batch_code'] ?? '' }}</td><td><span class="badge text-bg-primary">{{ $movement['movement_type'] }}</span></td><td class="fw-semibold">{{ $movement['quantity'] }}</td><td>{{ $movement['reference_type'] }} #{{ $movement['reference_id'] }}</td><td>#{{ $movement['performed_by'] }}</td></tr>
@empty<tr><td colspan="7" class="text-center text-secondary py-4">Chưa có biến động kho.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination', ['paginator' => $movements])</div></div>
@endsection
