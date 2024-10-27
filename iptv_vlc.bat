@echo off

rem Nettoyer l'URL pour retirer le préfixe "iptv://"
set "url=%~1"
set "url=%url:iptv://=%"
set "url=%url:http//=http://%"

rem Lancer VLC avec l'URL nettoyée
"C:\Program Files (x86)\VideoLAN\VLC\vlc.exe --one-instance" "%url%"