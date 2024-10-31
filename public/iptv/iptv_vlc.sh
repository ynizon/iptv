#!/bin/bash

# Prend en argument l'URL complète avec le temps de démarrage (ex : http://domaine/video.mp4#3600)
full_url="$1"

# Supprime la partie 'iptv://' pour ne garder que l'URL http et ajoute le : qui manque
clean_url="${full_url#iptv://}"
clean_url="${clean_url/https\//https:/}"
clean_url="${clean_url/http\//http:/}"

# Extraire la partie avant le '#' (l'URL de la vidéo)
video_url="${clean_url%%#*}"

# Extraire la partie après le '#' (le temps en secondes)
start_time="${full_url##*#}"

# Lancer VLC avec l'URL de la vidéo et le temps de démarrage
vlc "$video_url" --start-time="$start_time"
