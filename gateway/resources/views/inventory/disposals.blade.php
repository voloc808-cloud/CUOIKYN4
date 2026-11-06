@extends('layouts.app')
@section('title', 'Xuất hủy - PIMS')
@section('heading', 'Phiếu xuất hủy')
@section('subheading', 'Theo dõi thuốc hủy và lý do')
@section('content')
<div class="d-flex justify-content-end mb-3"><a class="btn btn-primary" href="{{ route('inventory.disposals.create') }}">Lập phiếu xuất hủy</a></div>
<div class="card content-card"><div class="card-body"><table class="table align-middle"><thead><tr><th>Mã phiếu</th><th>Ngày hủy</th><th>Lý do</th><th>Số lô</th><th></th></tr></thead><tbody>
@forelse(($disposals['data'] ?? []) as $item)<tr><td><strong>{{ $item['receipt_code'] }}</strong></td><td>{{ date('d/m/Y H:i', strtotime($item['disposed_at'])) }}</td><td>{{ $item['reason'] }}</td><td>{{ count($item['details'] ?? []) }}</td><td><a class="btn btn-sm btn-outline-primary" href="{{ route('inventory.disposals.show', $item['id']) }}">Chi tiết</a></td></tr>
@empty<tr><td colspan="5" class="text-center text-secondary py-4">Chưa có phiếu xuất hủy.</td></tr>@endforelse
</tbody></table>@include('partials.pagination', ['paginator' => $disposals])</div></div>
@endsection
