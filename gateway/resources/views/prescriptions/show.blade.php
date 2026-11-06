@extends('layouts.app')
@section('title', 'Chi tiết đơn thuốc - PIMS')
@section('heading', 'Đơn '.$prescription['prescription_code'])
@section('subheading', 'Bệnh nhân: '.$prescription['patient_name'])
@section('content')
<div class="card content-card mb-3"><div class="card-body"><div class="row"><div class="col-md-4"><strong>Trạng thái</strong><p>{{ $prescription['status'] }}</p></div><div class="col-md-8"><strong>Chẩn đoán</strong><p>{{ $prescription['diagnosis'] ?: '—' }}</p></div></div>@if($prescription['rejection_reason'] ?? null)<div class="alert alert-danger">Lý do từ chối: {{ $prescription['rejection_reason'] }}</div>@endif</div></div>
<div class="card content-card"><div class="card-body"><table class="table"><thead><tr><th>Thuốc</th><th>Số lượng</th><th>Cách dùng</th></tr></thead><tbody>@foreach($prescription['details'] as $detail)<tr><td>{{ $detail['medicine']['name'] ?? '#'.$detail['medicine_id'] }}</td><td>{{ $detail['quantity'] }}</td><td>{{ $detail['instructions'] }}</td></tr>@endforeach</tbody></table>
@if(($currentUser['role_code'] ?? '') === 'PHARMACIST' && $prescription['status'] === 'NEW')<div class="d-flex gap-2"><form method="POST" action="{{ route('prescriptions.approve', $prescription['id']) }}">@csrf<button class="btn btn-success" onclick="return confirm('Duyệt và xuất kho FEFO?')">Duyệt & cấp phát</button></form><form method="POST" action="{{ route('prescriptions.reject', $prescription['id']) }}" class="d-flex gap-2 flex-grow-1">@csrf<input class="form-control" name="reason" placeholder="Lý do từ chối" required><button class="btn btn-danger">Từ chối</button></form></div>@endif
</div></div>
@endsection
