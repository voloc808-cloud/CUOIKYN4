<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập PIMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card border-0 shadow-lg" style="max-width: 430px; width: 100%">
        <div class="card-body p-4 p-md-5">
            <div class="text-center mb-4"><div class="display-5 text-primary">✚</div><h1 class="h3">Đăng nhập PIMS</h1><p class="text-secondary">Hệ thống Quản lý Thuốc & Kho dược</p></div>
            @include('partials.flash')
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <div class="mb-3"><label class="form-label">Email</label><input class="form-control form-control-lg" type="email" name="email" value="{{ old('email') }}" autofocus></div>
                <div class="mb-4"><label class="form-label">Mật khẩu</label><input class="form-control form-control-lg" type="password" name="password"></div>
                <button class="btn btn-primary btn-lg w-100">Đăng nhập</button>
            </form>
            <div class="small text-secondary mt-4">
                <div>Quản trị viên: admin@pims.local / Admin@123</div>
                <div>Bác sĩ: doctor@pims.local / Doctor@123</div>
                <div>Dược sĩ: pharmacist@pims.local / Pharmacist@123</div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
