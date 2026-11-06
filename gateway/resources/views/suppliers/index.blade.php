@extends('layouts.app')
@section('title', 'Nhà cung cấp - PIMS')
@section('heading', 'Nhà cung cấp')
@section('subheading', 'Danh sách đơn vị cung ứng thuốc và vật tư')
@section('content')
<div class="card content-card"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <form class="d-flex gap-2"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Mã, tên hoặc điện thoại"><button class="btn btn-outline-primary">Tìm</button></form>
        <a class="btn btn-primary" href="{{ route('suppliers.create') }}">Thêm nhà cung cấp</a>
    </div>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Mã</th><th>Nhà cung cấp</th><th>Liên hệ</th><th>Địa chỉ</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>@forelse(($suppliers['data'] ?? []) as $supplier)
        <tr><td><code>{{ $supplier['code'] }}</code></td><td class="fw-semibold">{{ $supplier['name'] }}<small class="d-block text-secondary">{{ $supplier['tax_code'] }}</small></td>
            <td>{{ $supplier['contact_person'] }}<small class="d-block text-secondary">{{ $supplier['phone'] }} {{ $supplier['email'] }}</small></td><td>{{ $supplier['address'] }}</td>
            <td><span class="badge {{ $supplier['is_active'] ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $supplier['is_active'] ? 'Hoạt động' : 'Ngừng hợp tác' }}</span></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('suppliers.edit', $supplier['id']) }}">Sửa</a>
                <form class="d-inline" method="POST" action="{{ route('suppliers.destroy', $supplier['id']) }}" onsubmit="return confirm('Xóa nhà cung cấp này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form></td></tr>
        @empty<tr><td colspan="6" class="text-center text-secondary py-4">Không có nhà cung cấp phù hợp.</td></tr>@endforelse</tbody>
    </table></div>
    @include('partials.pagination', ['paginator' => $suppliers])
</div></div>
@endsection
