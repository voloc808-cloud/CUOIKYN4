# Service catalog

| Service | Trách nhiệm | Database | Được gọi bởi | Gọi ra |
|---|---|---|---|---|
| gateway | Blade UI, route, auth, correlation ID, chuẩn hóa lỗi | Không sở hữu | Browser | Tất cả service |
| auth-service | Đăng nhập/đăng xuất, đổi mật khẩu, tài khoản, vai trò | db_auth | Gateway | Không |
| catalog-service | Thuốc, hoạt chất, hàm lượng, đơn vị/quy đổi, nhà cung cấp | db_catalog | Gateway, Inventory, Prescription, Report | Không |
| inventory-service | Nhập lô, tồn, FEFO, restore, xuất hủy, biến động, cảnh báo | db_inventory | Gateway, Prescription, Report | Catalog |
| prescription-service | Kê đơn, lịch sử, duyệt/từ chối, trạng thái, Saga | db_prescription | Gateway, Report | Catalog, Inventory |
| report-service | Tổng hợp và xuất Excel/PDF chỉ đọc | Không | Gateway | Inventory, Catalog, Prescription |

## Endpoint contract

| Service | Public API chính | Internal API |
|---|---|---|
| auth | /api/v1/auth/*, /api/v1/users/* | Chưa cần |
| catalog | /api/v1/medicines, /api/v1/units, /api/v1/unit-conversions, /api/v1/suppliers | GET /internal/v1/medicines/{id} |
| inventory | /api/v1/import-receipts, /api/v1/batches, /api/v1/disposal-receipts, /api/v1/stock-movements, /api/v1/alerts | POST /internal/v1/stock/check, POST /internal/v1/stock/deduct-fefo, POST /internal/v1/stock/restore |
| prescription | /api/v1/prescriptions, /api/v1/prescriptions/{id}/approve, /api/v1/prescriptions/{id}/reject | GET /internal/v1/prescriptions |
| report | /api/v1/reports/inventory, /api/v1/reports/import-export, /api/v1/reports/prescriptions, /api/v1/reports/export | Không |

Mọi service có GET /health. Chi tiết status code và envelope nằm trong openapi.yaml của service tương ứng.

## Mã lỗi nghiệp vụ

| Code | Chủ sở hữu | Ý nghĩa |
|---|---|---|
| INSUFFICIENT_STOCK | Inventory | Không đủ tồn khả dụng cho ít nhất một dòng |
| BATCH_CODE_DUPLICATED | Inventory | Trùng mã lô trong cùng thuốc |
| INVALID_STATUS_TRANSITION | Prescription | Chuyển trạng thái sai quy trình |
| MEDICINE_HAS_BATCHES | Catalog/Inventory | Không thể xóa thuốc đã phát sinh lô |
| INVALID_INTERNAL_KEY | Common middleware | Sai hoặc thiếu khóa nội bộ |

Không thêm dependency ngoài bảng trên. Đặc biệt cấm Inventory gọi Prescription, Catalog gọi Inventory và mọi dependency vòng.
