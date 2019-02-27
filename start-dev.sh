#!/usr/bin/env bash

production=false

if [ "$2" = "production" ] || [ "$1" = "production" ]
then
    production=true
fi

CYAN='\033[00;36m'

WHITE='\033[01;37m'

echo " "
echo "----------------------------------------------------------"
echo "- SCRIPT Ruined world"
echo "- DERNIERE MAJ 27/02/2019"
echo "- MODE PRODUCTION ACTIVE : ${production}"
echo "----------------------------------------------------------"

setTitre() {
    echo -e ""
    echo -e "${CYAN}------------------------------------------------"
    echo -e $1
    echo -e "------------------------------------------------"
    echo -e "${WHITE}"
}

all() {
    composeupdate
    checkcache
    chmodfiles
    updatedb
}

install() {
    aptdep

    if [ "$1" = "dev" ]
    then
        setTitre "Installation lancée en mode DEV (des paramètres seront installés en plus -> cron, cronfig hosts local)"
    fi

    mkdir tmp
    mkdir data

    setTitre "Installation des dépendances PHP7.2"
    sudo apt-get -y install php7.2-mysql php7.2-mbstring

    setTitre "Ajout de permissions sur le binaire"
    sudo chmod +x bin/console

    if [ "$1" = "dev" ]
    then
        setTitre "mise en place de la configuration dans /etc/hosts"
        last_host=tail  /etc/hosts -n 1

        if grep --quiet ruined-world.local /etc/hosts
        then
            setTitre "la config lms est déjà présente dans /etc/hosts"
        else
            sudo -- sh -c -e 'echo "127.0.0.1  ruined-world.local\c" >> /etc/hosts'
        fi

        setTitre "mise en place de la configuration du cron"
        if sudo crontab -l | grep -q 'ruined-world.local'
        then
            setTitre "la config lms dans le cron roo est déjà présente"
        else
            line="*/1 * * * * curl ruined-world.local/cron"
            (sudo crontab -u root -l; echo "$line" ) | sudo crontab -u root -
        fi
    fi

    restartservice

    composeupdate
    checkcache
    chmodfiles
    updatedb
}

checkcache() {
    setTitre "Vidage du cache"
    sudo rm -rf var/cache/*
    sudo rm -rf var/logs/*
    php bin/console cache:clear --no-warmup
    HTTPDUSER=`ps aux | grep -E '[a]pache|[h]ttpd|[_]www|[w]ww-data|[n]ginx' | grep -v root | head -1 | cut -d\  -f1`
    sudo setfacl -R -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/logs var/sessions
    sudo setfacl -dR -m u:"$HTTPDUSER":rwX -m u:`whoami`:rwX var/cache var/logs var/sessions
    php bin/console cache:warmup
}

composeupdate() {
    setTitre "Composer update"
    composer update
}

chmodfiles() {
    setTitre "Ajout des permissions sur web, var, data et tmp"
    mkdir var/sessions
    mkdir var/sessions/dev
    sudo chmod -R 775 var/*
    sudo chmod -R 765 data
    sudo chmod -R 765 tmp
    sudo chown -R www-data:www-data data
    sudo chown -R www-data:www-data tmp
    sudo chown -R 1000:1000 public
}

restartservice() {
    setTitre "Reload php-fpm + apache2"
    sudo service php7.2-fpm reload
    sudo service apache2 reload
}

updatedb(){
    setTitre "Lancement de doctrine:schema:update"
    php bin/console doctrine:schema:update --force
}

helpermore(){
    setTitre "Commandes disponibles"
    echo "cache: Vide le cache"
    echo "update: Met a jour les packets Composer"
    echo "doctrine: met à jour les entités"
    echo "help: Affiche des informations sur les commandes disponibles"
}

if [ "$1" = "install" ]
then
    install $2
elif [ "$1" = "cache" ]
then
    checkcache
elif [ "$1" = "update" ]
then
    composeupdate
elif [ "$1" = "doctrine" ]
then
    updatedb
elif [ "$1" = "" ] || [ "$1" = "production" ]
then
    all
elif [ "$1" = "-h" ] || [ "$1" = "help" ] || [ "$1" = "--help" ]
then
    helpermore
else
    helpermore
fi
