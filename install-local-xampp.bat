@echo off
chcp 65001 >nul
cls

echo ==============================================
echo  🔧 Установка проекта на локальный сервер XAMPP
echo ==============================================
echo.

echo ✅ Создание необходимых папок...
if not exist "logs" mkdir logs
if not exist "uploads" mkdir uploads
if not exist "uploads\recordings" mkdir uploads\recordings
if not exist "uploads\avatars" mkdir uploads\avatars

echo ✅ Установка прав доступа (Windows)...
icacls logs /grant Everyone:F /T /Q >nul
icacls uploads /grant Everyone:F /T /Q >nul

echo ✅ Папки созданы успешно!
echo.
echo 📌 Далее выполните вручную:
echo.
echo 1️⃣  Откройте XAMPP Control Panel
echo 2️⃣  Запустите Apache и MySQL
echo 3️⃣  Откройте phpMyAdmin: http://localhost/phpmyadmin
echo 4️⃣  Создайте базу данных с именем: testplatform
echo 5️⃣  Импортируйте файлы из папки database/migrations/ по порядку:
echo     - 001_initial.sql
echo     - 002_create_recordings.sql
echo     - 003_fix_logs_enum_add_recording.sql
echo.
echo 6️⃣  После импорта откройте сайт: http://localhost/test-platform/
echo.
echo ✅ Установка завершена!
echo.
pause