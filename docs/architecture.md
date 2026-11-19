# Kiến trúc PIMS

PIMS là monorepo SOA gồm một Laravel gateway và năm Lumen service độc lập. Gateway là điểm vào duy nhất từ trình duyệt; service không mở port ra host và chỉ trao đổi qua mạng pims-net.

## Sơ đồ thành phần

~~~text
Browser
   |
   v
Laravel Gateway
   |----> Auth Service ---------> db_auth
   |----> Catalog Service ------> db_catalog
   |----> Inventory Service ----> db_inventory
   |----> Prescription Service -> db_prescription
   '----> Report Service (không DB)

Prescription ---> Catalog
Prescription ---> Inventory ---> Catalog
Report ---------> Inventory, Catalog, Prescription (read-only)
~~~

Mọi service chỉ giao tiếp qua REST. Không service nào đọc database của service khác hoặc import model/nghiệp vụ của service khác.

## Quy ước giao tiếp

- Public API dùng /api/v1; internal API dùng /internal/v1.
- Internal API yêu cầu đồng thời JWT người dùng được chuyển tiếp và X-Internal-Key.
- Gateway/service chuyển tiếp X-Correlation-ID; middleware tạo UUID nếu request đầu vào chưa có.
- Response có status, data, message, errors; lỗi nghiệp vụ có thêm code.
- HTTP client timeout 5 giây; chỉ GET được retry tối đa hai lần.
- Circuit breaker mở 30 giây sau ba lỗi liên tiếp; lỗi downstream được chuẩn hóa thành HTTP 503.

## Dữ liệu và giao dịch

SQL Server chứa bốn database độc lập: db_auth, db_catalog, db_inventory, db_prescription. Report service không có database.

Luồng duyệt đơn dùng Saga đồng bộ:

1. Prescription kiểm tra trạng thái NEW.
2. Inventory kiểm tra đủ tồn cho toàn bộ dòng thuốc.
3. Prescription đổi sang APPROVED.
4. Inventory trừ FEFO trong một transaction có row lock, idempotent theo prescription_id.
5. Prescription đổi sang DISPENSED.
6. Nếu bước cuối lỗi, Inventory restore theo prescription_id, rồi đơn quay về NEW.

## Package dùng chung

packages/pims-common chỉ chứa tiện ích kỹ thuật: response envelope, JWT/role/internal-key middleware, correlation ID, HTTP client và exception hạ tầng. Package không chứa model hay logic nghiệp vụ.

## Quy tắc triển khai

- Mỗi ứng dụng có composer.json, .env.example, Dockerfile, test suite và lifecycle độc lập.
- Chỉ gateway publish port 8080; SQL Server và service chỉ có mặt trong pims-net.
- Secret mẫu phải được thay trước khi dùng ngoài môi trường local.
