#!/usr/bin/env bash

# get the folder of this script
SHELL_FOLDER=$(pwd)

echo "[*] ------------"
echo "[*] Checking necessary working directories."
if [ ! -d "${SHELL_FOLDER}/../.docker" ]; then
    echo "[*] Creating .docker folder.."
    mkdir ${SHELL_FOLDER}/../.docker
fi

echo "[*] ------------"
echo "[*] Geting the MariaDB image"
docker pull mariadb:10.1.37

echo "[*] ------------"
echo "[*] Creating the local container for mysql"
sudo docker run \
    --name jeedom-mysql \
    -v ${SHELL_FOLDER}/../.docker/mysql:/var/lib/mysql \
    -e MYSQL_ROOT_PASSWORD=root \
    -d mariadb:10.1.37

echo "[*] ------------"
echo "[*] Geting the latest Jeedom image"
docker pull jeedom/jeedom

echo "[*] ------------"
echo "[*] Creating the local container for Jeedom"
sudo docker run \
    --name jeedom-server \
    --link jeedom-mysql:mysql \
    --privileged \
    -v ${SHELL_FOLDER}/../.docker/server:/var/www/html \
    -e ROOT_PASSWORD=root \
    -p 9080:80 \
    -p 9022:22 jeedom/jeedom

# echo "[*] ------------"
# echo "[*] Enter the mysql container"
# echo "[>]     docker exec -it jeedom-mysql /bin/bash"
# echo "[*] ------------"
# echo "[*] Log into mysql"
# echo "[>]     mysql -p"
# echo "[*] ------------"
# echo "[*] Create jeedom user"
# echo "[*]     CREATE USER 'jeedom'@'%' IDENTIFIED WITH mysql_native_password BY 'jeedom';"
# echo "[*] ------------"
# echo "[*] Create jeedom datebase"
# echo "[>]     CREATE DATABASE jeedom;"
# echo "[*] ------------"
# echo "[*] Asign jeedom user to jeedom database"
# echo "[>]     GRANT ALL PRIVILEGES ON jeedom.* TO 'jeedom'@'%';"
# echo "[*] ------------"
# echo "[*] Get mysql ip address"
# echo "[>]     more /etc/hosts"
# echo "[*] ------------"
# echo "[*] Access \"localhost:9080\" to install jeedom"