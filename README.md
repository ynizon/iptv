## Prerequis
Creer un dossier c:\iptv
Copier le fichier iptv_vlc.bat dedans
Double cliquer sur iptv.reg 
Installer composer (https://getcomposer.org/download/)
Installer VLC (https://www.videolan.org/index.fr.html)

## Installation
Dans le dossier du site web, faire
- composer install
- php artisan key:generate

Soit vous ajouter votre playlist M3U dans 
- dans database/seeder/ ligne 56
 puis vous Lancer les commandes:
- php artisan migrate --seed
- php artisan app:refresh


Soit vous lancez les commandes 
- php artisan migrate
- vous  ajouter votre playlist M3U depuis l'interface
- php artisan app:refresh
