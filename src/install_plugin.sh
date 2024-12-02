#!/bin/bash

#########################
## Nicolas DEROUET
## 22/09/2020
#########################

shopt -s dotglob

red='\033[1;31m'
green='\033[0;32m'
clean='\033[0;m'

# Usage
if [ "$#" -lt 2 ]; then
    echo -e "${red}Le nombre d'arguments est invalide${clean}"
fi

# Si le plugin existe
if [ ! -d vendor/"$1" ]
then
    echo -e "${red}$1 not installed${clean}"
    exit 126
fi

# Installation du prog
if [ -d public/"$2" ] && [ -d vendor/"$1"/public ]
then
    if [ -f public/"$2"/.env.override ] || [ -f public/"$2"/config.env.php ] || ([ "$#" -eq 3 ] && [ "$3" = 'force-install' ])
    then
        find public/"$2"/* -type d -not \( -name '.gitignore' -or -name '.env.override' -or -name 'config.env.php' \) -exec rm -Rf {} + 2> /dev/null
        find public/"$2"/* -type f -not \( -name '.gitignore' -or -name '.env.override' -or -name 'config.env.php' \) -exec rm -f {} + 2> /dev/null

        mv vendor/"$1"/public/* public/"$2"/
        mv vendor/"$1"/composer.json public/"$2"/.metadata.json

        echo -e "Installation de ${green}$1${clean} => ${green}$2${clean}"
    else
        echo -e "${red}/!\ Pas d'installation de $1 (aucun fichier .env.override ou config.env.php dans $2)${clean}"
    fi
fi

rm -Rf vendor/"$1"
