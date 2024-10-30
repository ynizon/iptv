## Goal

## Requirements
- Create a folder c:\iptv
- Copy file public\iptv_vlc.bat inside
- Launch public\iptv.reg 
- Install composer (https://getcomposer.org/download/)
- Install VLC (https://www.videolan.org/index.fr.html)

## Installation
- cp .env.example .env
- set your .env variables (user email, password, locale...) if needed
- composer install

Add your M3U playlist in .env on the M3U variable, 
then launch commands:
- php artisan migrate --seed
- php artisan refresh:playlist
