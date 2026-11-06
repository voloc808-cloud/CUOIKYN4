@extends('layouts.app')
@section('title', 'Danh mục thuốc - PIMS')
@section('heading', 'Danh mục thuốc')
@section('subheading', 'Quản lý hoạt chất, hàm lượng, đơn vị và ngưỡng tồn')
@section('content')
<div class="card content-card"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <form class="d-flex gap-2"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Mã, tên hoặc hoạt chất"><button class="btn btn-outline-primary">Tìm</button></form>
        <a class="btn btn-primary" href="{{ route('medicines.create') }}">Thêm thuốc</a>
    </div>
    <div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Mã</th><th>Tên thuốc</th><th>Hoạt chất / Hàm lượng</th><th>Đơn vị</th><th>Ngưỡng tồn</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>@forelse(($medicines['data'] ?? []) as $medicine)
            <tr><td><code>{{ $medicine['code'] }}</code></td><td class="fw-semibold">{{ $medicine['name'] }}<small class="d-block text-secondary">{{ $medicine['dosage_form'] }}</small></td>
                <td>{{ $medicine['active_ingredient'] }}<small class="d-block text-secondary">{{ $medicine['strength'] }}</small></td>
                <td>{{ $medicine['base_unit']['name'] ?? '' }}</td><td>{{ rtrim(rtrim($medicine['minimum_stock'], '0'), '.') }}</td>
                <td><span class="badge {{ $medicine['is_active'] ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $medicine['is_active'] ? 'Đang dùng' : 'Ngừng dùng' }}</span></td>
                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('medicines.edit', $medicine['id']) }}">Sửa</a>
                    <form class="d-inline" method="POST" action="{{ route('medicines.destroy', $medicine['id']) }}" onsubmit="return confirm('Xóa thuốc này? Hệ thống sẽ kiểm tra lô tồn trước khi xóa.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form></td></tr>
        @empty<tr><td colspan="7" class="text-center text-secondary py-4">Không có thuốc phù hợp.</td></tr>@endforelse</tbody>
    </table></div>
    @include('partials.pagination', ['paginator' => $medicines])
</div></div>
@endsection
