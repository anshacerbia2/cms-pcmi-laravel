@echo off
color 0B
echo =======================================================
echo  FIX MEMPALACE DATABASE (Bypass C: Drive Deep Freeze)
echo =======================================================
echo.
echo PENTING: Mohon TUTUP editor/IDE (VSCode/Cursor) Anda 
echo agar database MemPalace tidak dalam keadaan terkunci.
echo.
pause

echo.
echo [1/3] Mem-backup dan memindahkan data MemPalace dari C: ke D:...
robocopy "C:\Users\ATI-User\.mempalace" "D:\MemPalace_Data" /E /MOVE /IS

echo.
echo [2/3] Menghapus folder lama di C:...
rmdir /S /Q "C:\Users\ATI-User\.mempalace"

echo.
echo [3/3] Membuat jalur pintas (Junction) ke drive D:...
mklink /J "C:\Users\ATI-User\.mempalace" "D:\MemPalace_Data"

echo.
echo =======================================================
echo SELESAI! Database MemPalace juga sudah aman di drive D:
echo =======================================================
pause
