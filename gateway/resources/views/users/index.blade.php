@extends('layouts.app')
@section('title', 'Quản lý tài khoản - PIMS')
@section('heading', 'Quản lý tài khoản')
@section('subheading', 'Chỉ Dược sĩ / Nhân viên kho được truy cập')
@section('content')
<div class="card content-card">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
            <form class="d-flex gap-2" method="GET"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Tìm theo tên hoặc email"><button class="btn btn-outline-primary">Tìm</button></form>
            <a class="btn btn-primary" href="{{ route('users.create') }}">Thêm tài khoản</a>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Họ tên</th><th>Email</th><th>Vai trò</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead>
                <tbody>
                @forelse(($users['data'] ?? []) as $user)
                    <tr>
                        <td class="fw-semibold">{{ $user['name'] }}</td>
                        <td>{{ $user['email'] }}</td>
                        <td>{{ $user['role_code'] === 'DOCTOR' ? 'Bác sĩ' : 'Dược sĩ / Nhân viên kho' }}</td>
                        <td><span class="badge {{ $user['is_active'] ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user['is_active'] ? 'Hoạt động' : 'Đã khóa' }}</span></td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', $user['id']) }}">Sửa</a>
                            <form class="d-inline" method="POST" action="{{ route('users.status', $user['id']) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $user['is_active'] ? 0 : 1 }}"><button class="btn btn-sm btn-outline-warning">{{ $user['is_active'] ? 'Khóa' : 'Mở khóa' }}</button></form>
                            <form class="d-inline" method="POST" action="{{ route('users.destroy', $user['id']) }}" onsubmit="return confirm('Xóa tài khoản này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xóa</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-secondary py-4">Không có tài khoản phù hợp.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if(($users['last_page'] ?? 1) > 1)
            <nav><ul class="pagination mb-0">
                @for($page = 1; $page <= $users['last_page']; $page++)
                    <li class="page-item {{ $page === $users['current_page'] ? 'active' : '' }}"><a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $page]) }}">{{ $page }}</a></li>
                @endfor
            </ul></nav>
        @endif
    </div>
</div>
@endsection
