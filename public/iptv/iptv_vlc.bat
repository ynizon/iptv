@echo off

rem Nettoyer l'URL pour retirer le préfixe "iptv://"
set "url=%~1"
set "url=%url:iptv://=%"
set "url=%url:http//=http://%"

for /f "tokens=1,2 delims=#" %%a in ("%url%") do (
    set "url=%%a"             rem URL sans le start-time
    set "start_time=%%b"      rem Start-time extrait
)

rem Lancer VLC avec l'URL nettoyée
"C:\Program Files (x86)\VideoLAN\VLC\vlc.exe " "--one-instance" "--start-time=%start_time%" "%url%"
