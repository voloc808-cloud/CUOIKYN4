# PIMS - Hệ thống Quản lý Thuốc và Kho dược

Monorepo SOA sử dụng Laravel 10, Lumen 10, PHP 8.1+, Microsoft SQL Server và JWT.

## Thành phần

- `gateway`: giao diện Blade + Bootstrap, điểm vào duy nhất từ bên ngoài.
- `auth-service`: xác thực JWT và quản lý tài khoản.
- `catalog-service`: thuốc, đơn vị, quy đổi và nhà cung cấp.
- `inventory-service`: nhập kho, tồn theo lô, FEFO, xuất hủy và cảnh báo.
- `prescription-service`: kê đơn, duyệt, từ chối và Saga cấp phát.
- `report-service`: tổng hợp báo cáo và xuất Excel/PDF, không có database.
- `packages/pims-common`: middleware, response envelope và HTTP client dùng chung.

## Khởi chạy bằng Docker

```powershell
Copy-Item .env.example .env
Copy-Item gateway/.env.example gateway/.env -Force
Get-ChildItem services -Directory | ForEach-Object {
    Copy-Item (Join-Path $_.FullName '.env.example') (Join-Path $_.FullName '.env') -Force
}
docker compose build
docker compose up -d
```

Đảm bảo `JWT_SECRET` và `INTERNAL_API_KEY` giống nhau trong gateway và mọi service. Gateway chạy tại `http://localhost:8080`; các service chỉ nằm trong mạng `pims-net`.

## Migration và dữ liệu mẫu

```powershell
docker compose exec auth-service php artisan migrate --seed
docker compose exec catalog-service php artisan migrate --seed
docker compose exec inventory-service php artisan migrate --seed
docker compose exec prescription-service php artisan migrate --seed
```

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị viên | `admin@pims.local` | `Admin@123` |
| Bác sĩ | `doctor@pims.local` | `Doctor@123` |
| Dược sĩ | `pharmacist@pims.local` | `Pharmacist@123` |

Chỉ quản trị viên được tạo, sửa, khóa và xóa tài khoản bác sĩ/dược sĩ; tài khoản quản trị không được quản lý qua giao diện này. Đặt `PIMS_ADMIN_EMAIL` và `PIMS_ADMIN_PASSWORD` trong `services/auth-service/.env` trước khi seed để thay thông tin mặc định. Không dùng mật khẩu mẫu khi triển khai thật. Seeder không ghi đè tài khoản đã tồn tại.

Seeder tạo 20 thuốc, 4 nhà cung cấp, 40 lô gồm lô hết hạn/sắp hết hạn và các đơn thuốc mẫu.

## Chạy trực tiếp trên Windows

Máy cần PHP CLI với `mbstring`, `sqlsrv`, `pdo_sqlsrv`, Composer và SQL Server Express. Tạo bốn database `db_auth`, `db_catalog`, `db_inventory`, `db_prescription`; `report-service` không cần database. Trong `.env` của bốn service có database, đặt `DB_HOST=THAILOC-DEV\SQLEXPRESS`, `DB_PORT=` và tài khoản SQL Server có quyền trên database tương ứng.

Trong `gateway/.env` và `.env` của tất cả service, `JWT_SECRET` và `INTERNAL_API_KEY` phải giống nhau. Các URL gọi service khi chạy local là `http://127.0.0.1:8001` (auth), `:8002` (catalog), `:8003` (inventory), `:8004` (prescription), `:8005` (report). Inventory cần `SERVICE_CATALOG_URL`; prescription cần URL catalog và inventory; report cần URL catalog, inventory và prescription.

Chạy `composer install` tại mỗi thư mục ứng dụng; với report-service cần thêm extension `zip`, `gd`, `fileinfo` để xuất file. Sau khi chạy migration và seeder theo thứ tự auth → catalog → inventory → prescription, khởi động bằng `start_all.bat` và truy cập `http://127.0.0.1:8080`.

## Chức năng

- Bác sĩ kê đơn nhiều dòng và chỉ xem đơn của mình.
- Dược sĩ quản lý danh mục, nhập kho, xuất hủy, duyệt/từ chối đơn.
- Xuất kho FEFO bỏ qua lô hết hạn, khóa dòng và idempotent theo `prescription_id`.
- Saga hoàn kho khi bước chốt trạng thái đơn thất bại.
- Nếu yêu cầu trừ kho bị timeout, service đối soát theo mã đơn. Khi chưa xác định được kết quả, đơn giữ `APPROVED`; dược sĩ có thể bấm duyệt lại để hoàn tất an toàn.
- Cảnh báo HSD theo số ngày cấu hình và cảnh báo dưới ngưỡng tồn tối thiểu.
- Báo cáo nhập–xuất–tồn theo thời gian, tải Excel và PDF.

## Kiểm thử

```powershell
docker compose exec inventory-service ./vendor/bin/phpunit
docker compose exec prescription-service ./vendor/bin/phpunit
docker compose exec catalog-service ./vendor/bin/phpunit
docker compose exec auth-service ./vendor/bin/phpunit
```

Các test nghiệp vụ bao phủ FEFO nhiều lô, bỏ lô hết hạn, thiếu tồn, validate lô, chuyển trạng thái, rollback Saga và chống trừ kho lặp.

Để kiểm tra API từ đầu đến cuối trên SQL Server local, chạy `powershell -File scripts/verify-workflow.ps1` từ thư mục gốc. Script đăng nhập hai tài khoản mẫu, thử nhập kho, kê và duyệt đơn FEFO, thiếu tồn, từ chối, xuất hủy, cảnh báo, báo cáo; sau đó hoàn kho, xóa đúng dữ liệu thử và đối chiếu tồn từng lô với lúc bắt đầu.

## Tài liệu

- `docs/architecture.md`: boundary SOA, resilience, correlation ID và Saga.
- `docs/service-catalog.md`: trách nhiệm, dependency và endpoint của từng service.
- `services/*/openapi.yaml`: hợp đồng API public/internal.

## Trạng thái

Đã hoàn thành các giai đoạn 1–8: nền tảng, xác thực, danh mục, kho, đơn thuốc/FEFO, xuất hủy/cảnh báo, báo cáo và kiểm thử hoàn thiện.
