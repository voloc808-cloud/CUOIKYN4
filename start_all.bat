@echo off
cd /d "%~dp0"
echo Khoi dong he thong PIMS Microservices...

start "PIMS-Auth-8001" cmd /k "php -S 127.0.0.1:8001 -t services\auth-service\public services\auth-service\public\index.php"
start "PIMS-Catalog-8002" cmd /k "php -S 127.0.0.1:8002 -t services\catalog-service\public services\catalog-service\public\index.php"
start "PIMS-Inventory-8003" cmd /k "php -S 127.0.0.1:8003 -t services\inventory-service\public services\inventory-service\public\index.php"
start "PIMS-Prescription-8004" cmd /k "php -S 127.0.0.1:8004 -t services\prescription-service\public services\prescription-service\public\index.php"
start "PIMS-Report-8005" cmd /k "php -d extension=zip -d extension=gd -d extension=fileinfo -S 127.0.0.1:8005 -t services\report-service\public services\report-service\public\index.php"
start "PIMS-Gateway-8080" cmd /k "php -S 127.0.0.1:8080 -t gateway\public gateway\public\index.php"

echo Da bat 6 cua so service!
echo Truy cap tai: http://localhost:8080
