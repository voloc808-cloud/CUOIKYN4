<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'PIMS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .sidebar { min-height: 100vh; background: #173b57; }
        .sidebar .nav-link { color: #dce9f3; border-radius: .5rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #245a80; color: #fff; }
        .brand-mark { width: 42px; height: 42px; display: grid; place-items: center; background: #2b91c8; border-radius: 12px; }
        .content-card { border: 0; box-shadow: 0 8px 24px rgba(23, 59, 87, .08); }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <aside class="col-md-3 col-xl-2 sidebar p-3">
            <div class="d-flex align-items-center gap-2 text-white mb-4">
                <div class="brand-mark fs-4">✚</div>
                <div><strong>PIMS</strong><small class="d-block text-white-50">Quản lý kho dược</small></div>
            </div>
            <nav class="nav flex-column gap-1">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Tổng quan</a>
                @if(($currentUser['role_code'] ?? '') === 'DOCTOR')
                    <a class="nav-link" href="{{ route('prescriptions.create') }}">Kê đơn</a>
                    <a class="nav-link" href="{{ route('prescriptions.index') }}">Lịch sử đơn</a>
                @elseif(($currentUser['role_code'] ?? '') === 'ADMIN')
                    <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Quản lý tài khoản</a>
                @elseif(($currentUser['role_code'] ?? '') === 'PHARMACIST')
                    <a class="nav-link" href="{{ route('prescriptions.pending') }}">Đơn chờ duyệt</a>
                    <a class="nav-link {{ request()->routeIs('medicines.*') ? 'active' : '' }}" href="{{ route('medicines.index') }}">Danh mục thuốc</a>
                    <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">Nhà cung cấp</a>
                    <a class="nav-link {{ request()->routeIs('inventory.receipts*') ? 'active' : '' }}" href="{{ route('inventory.receipts') }}">Nhập kho</a>
                    <a class="nav-link {{ request()->routeIs('inventory.batches') ? 'active' : '' }}" href="{{ route('inventory.batches') }}">Tồn theo lô</a>
                    <a class="nav-link {{ request()->routeIs('inventory.movements') ? 'active' : '' }}" href="{{ route('inventory.movements') }}">Biến động kho</a>
                    <a class="nav-link {{ request()->routeIs('inventory.disposals*') ? 'active' : '' }}" href="{{ route('inventory.disposals') }}">Xuất hủy</a>
                    <a class="nav-link {{ request()->routeIs('inventory.alerts*') ? 'active' : '' }}" href="{{ route('inventory.alerts') }}">Cảnh báo</a>
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">Báo cáo</a>
                @endif
                <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.password') }}">Đổi mật khẩu</a>
            </nav>
        </aside>
        <main class="col-md-9 col-xl-10 px-md-4 py-3">
            <header class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h4 mb-0">@yield('heading', 'PIMS')</h1>
                    <small class="text-secondary">@yield('subheading')</small>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <div class="text-end">
                        <strong>{{ $currentUser['name'] ?? '' }}</strong>
                        <small class="d-block text-secondary">{{ match ($currentUser['role_code'] ?? '') { 'ADMIN' => 'Quản trị viên', 'DOCTOR' => 'Bác sĩ', 'PHARMACIST' => 'Dược sĩ / Nhân viên kho', default => '' } }}</small>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="btn btn-outline-danger btn-sm">Đăng xuất</button></form>
                </div>
            </header>
            @include('partials.flash')
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
