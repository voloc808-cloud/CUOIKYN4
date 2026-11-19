@extends('layouts.app')
@section('title', 'Chi tiết xuất hủy - PIMS')
@section('heading', 'Phiếu '.$receipt['receipt_code'])
@section('subheading', date('d/m/Y H:i', strtotime($receipt['disposed_at'])))
@section('content')
<div class="card content-card"><div class="card-body"><div class="alert alert-secondary"><strong>Lý do:</strong> {{ $receipt['reason'] }}</div><table class="table"><thead><tr><th>Mã lô</th><th>Thuốc #</th><th>HSD</th><th>Số lượng hủy</th></tr></thead><tbody>@foreach($receipt['details'] as $detail)<tr><td>{{ $detail['batch']['batch_code'] }}</td><td>{{ $detail['batch']['medicine_id'] }}</td><td>{{ date('d/m/Y', strtotime($detail['batch']['expires_at'])) }}</td><td>{{ $detail['quantity'] }}</td></tr>@endforeach</tbody></table></div></div>
@endsection
