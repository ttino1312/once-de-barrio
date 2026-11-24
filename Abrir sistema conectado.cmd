@echo off
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0abrir-once.ps1"
if errorlevel 1 pause
