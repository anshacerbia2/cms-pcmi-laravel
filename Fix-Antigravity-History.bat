@echo off
color 0A
echo =======================================================
echo  FIX ANTIGRAVITY HISTORY (Bypass C: Drive Deep Freeze)
echo =======================================================
echo.
echo PENTING: Mohon TUTUP editor/IDE (VSCode/Cursor) Anda sebelum melanjutkan!
echo Jika tidak ditutup, beberapa file history yang sedang dipakai akan gagal dipindah.
echo.
pause

echo.
echo [1/3] Mem-backup dan memindahkan data history dari C: ke D:...
robocopy "C:\Users\ATI-User\.gemini\antigravity" "D:\Antigravity_Data" /E /MOVE /IS

:: Robocopy /MOVE memindahkan file, tapi folder asalnya mungkin masih nyangkut kalau ada yang locked
echo.
echo [2/3] Menghapus folder lama di C:...
rmdir /S /Q "C:\Users\ATI-User\.gemini\antigravity"

echo.
echo [3/3] Membuat jalur pintas (Junction) ke drive D:...
mklink /J "C:\Users\ATI-User\.gemini\antigravity" "D:\Antigravity_Data"

echo.
echo =======================================================
echo SELESAI! Anda bisa membuka kembali editor Anda. 
echo Mulai sekarang, semua history percakapan dan memori 
echo AI (MemPalace) akan aman tersimpan di drive D:
echo =======================================================
pause
