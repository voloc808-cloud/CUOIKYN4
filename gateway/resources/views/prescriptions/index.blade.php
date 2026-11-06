@extends('layouts.app')
@section('title', 'Đơn thuốc - PIMS')
@section('heading', request()->routeIs('prescriptions.pending') ? 'Đơn chờ duyệt' : 'Lịch sử đơn thuốc')
@section('subheading', 'Theo dõi trạng thái cấp phát thuốc')
@section('content')
<div class="card content-card"><div class="card-body"><div class="table-responsive"><table class="table align-middle">
<thead><tr><th>Mã đơn</th><th>Bệnh nhân</th><th>Chẩn đoán</th><th>Ngày kê</th><th>Trạng thái</th><th></th></tr></thead><tbody>
@forelse(($prescriptions['data'] ?? []) as $item)
<tr><td><strong>{{ $item['prescription_code'] }}</strong></td><td>{{ $item['patient_name'] }}<small class="d-block text-secondary">{{ $item['patient_code'] ?? '' }}</small></td><td>{{ $item['diagnosis'] ?: '—' }}</td><td>{{ date('d/m/Y H:i', strtotime($item['created_at'])) }}</td><td><span class="badge {{ $item['status']==='DISPENSED' ? 'text-bg-success' : ($item['status']==='REJECTED' ? 'text-bg-danger' : 'text-bg-warning') }}">{{ $item['status'] }}</span></td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('prescriptions.show', $item['id']) }}">Chi tiết</a></td></tr>
@empty<tr><td colspan="6" class="text-center text-secondary py-4">Chưa có đơn thuốc.</td></tr>@endforelse
</tbody></table></div>@include('partials.pagination', ['paginator' => $prescriptions])</div></div>
@endsection
