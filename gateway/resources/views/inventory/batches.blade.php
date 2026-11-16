@extends('layouts.app')
@section('title', 'Tồn kho theo lô - PIMS')
@section('heading', 'Tồn kho theo lô')
@section('subheading', 'Sắp xếp theo hạn sử dụng gần nhất')
@section('content')
<div class="card content-card"><div class="card-body">
<form class="d-flex gap-2 mb-3" style="max-width:420px"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Tìm mã lô"><button class="btn btn-outline-primary">Tìm</button></form>
<div class="table-responsive"><table class="table align-middle"><thead><tr><th>Thuốc</th><th>Mã lô</th><th>NSX</th><th>HSD</th><th>Đã nhập</th><th>Còn lại</th><th>Trạng thái HSD</th></tr></thead><tbody>
@forelse(($batches['data'] ?? []) as $batch)
@php($expiryTimestamp = strtotime($batch['expires_at']))
<tr><td><strong>{{ $batch['medicine']['name'] ?? '#'.$batch['medicine_id'] }}</strong><small class="d-block text-secondary">{{ $batch['medicine']['code'] ?? '' }}</small></td><td>{{ $batch['batch_code'] }}</td><td>{{ date('d/m/Y', strtotime($batch['manufactured_at'])) }}</td><td>{{ date('d/m/Y', $expiryTimestamp) }}</td><td>{{ $batch['quantity_imported'] }}</td><td class="fw-semibold">{{ $batch['quantity_remaining'] }}</td><td>
@if($expiryTimestamp < time())<span class="badge text-bg-danger">Đã hết hạn</span>@elseif($expiryTimestamp <= strtotime('+30 days'))<span class="badge text-bg-warning">Sắp hết hạn</span>@else<span class="badge text-bg-success">Còn hạn</span>@endif
</td></tr>
@empty<tr><td colspan="7" class="text-center text-secondary py-4">Chưa có lô trong kho.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination', ['paginator' => $batches])
</div></div>
@endsection
