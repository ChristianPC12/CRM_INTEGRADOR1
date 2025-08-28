@echo off
if exist "C:\xampp\mysql\data-old" (
    rmdir /s /q "C:\xampp\mysql\data-old"
)
ren "C:\xampp\mysql\data" "data-old"
mkdir "C:\xampp\mysql\data"
xcopy "C:\xampp\mysql\backup\*" "C:\xampp\mysql\data" /s /e /y /i
del "C:\xampp\mysql\data\ibdata1"
copy "C:\xampp\mysql\data-old\ibdata1" "C:\xampp\mysql\data"
for /d %%D in ("C:\xampp\mysql\data-old\*") do (
    if /i not "%%~nxD"=="mysql" if /i not "%%~nxD"=="performance_schema" if /i not "%%~nxD"=="phpmyadmin" (
        xcopy "%%D" "C:\xampp\mysql\data\%%~nxD" /s /e /y /i
    )
)
@echo Proceso completado.
pause