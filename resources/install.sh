#! /bin/bash
PROGRESS_FILE=/tmp/jeedom/snips/dependance
SHELL_FOLDER=$(dirname $(readlink -f "$0"))
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
echo "[jeedom-plugin-snips]"
echo "--------------------------------"
echo "[*] Start to install dependencies."

touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}

echo "--------------------------------"
echo "[*] Checking necessary working directories."
if [ ! -d "${SHELL_FOLDER}/../config_running" ]; then
    echo "[*] Creating config_running folder.."
    mkdir ${SHELL_FOLDER}/../config_running
    chmod +775 ${SHELL_FOLDER}/../config_running
    chown www-data ${SHELL_FOLDER}/../config_running
    chgrp www-data ${SHELL_FOLDER}/../config_running
fi

if [ ! -d "${SHELL_FOLDER}/../config_backup" ]; then
    echo "[*] Creating config_backup folder.."
    mkdir ${SHELL_FOLDER}/../config_backup
    chmod +775 ${SHELL_FOLDER}/../config_backup
    chown www-data ${SHELL_FOLDER}/../config_backup
    chgrp www-data ${SHELL_FOLDER}/../config_backup
fi
echo 5 > ${PROGRESS_FILE}

echo "--------------------------------"
echo "[*] Installing lsb-release, php-pear"
apt-get -y install lsb-release php-pear
echo 10 > ${PROGRESS_FILE}

echo "--------------------------------"
echo "[*] Checking system architecture.."
archi=`lscpu | grep Architecture | awk '{ print $2 }'`
if [ "$archi" == "x86_64" ]; then
    if [ `lsb_release -i -s` == "Debian" ]; then
        wget http://repo.mosquitto.org/debian/mosquitto-repo.gpg.key
        apt-key add mosquitto-repo.gpg.key
        cd /etc/apt/sources.list.d/
        if [ `lsb_release -c -s` == "jessie" ]; then
            if [ ! -e "./mosquitto-jessie.list" ]; then
                rm mosquitto-jessie.list
            fi
            wget http://repo.mosquitto.org/debian/mosquitto-jessie.list
        fi
        if [ `lsb_release -c -s` == "stretch" ]; then
            if [ ! -e "./mosquitto-stretch.list" ]; then
                rm mosquitto-stretch.list
            fi
            wget http://repo.mosquitto.org/debian/mosquitto-stretch.list
        fi
    fi
fi
echo 20 > ${PROGRESS_FILE}

echo "--------------------------------"
echo "[*] Updating package list.."
apt-get update
echo 30 > ${PROGRESS_FILE}

echo "--------------------------------"
echo "[*] Installing mqtt dependencies.."
apt-get -y install mosquitto mosquitto-clients libmosquitto-dev
echo 50 > ${PROGRESS_FILE}

echo "--------------------------------"
echo "[*] Checking PHP version.."
if [[ -d "/etc/php5/" ]]; then
    echo "[*] PHP5 is detected"
    echo "[*] Configuring PHP5 extension.."
    apt-get -y install php5-dev
    if [[ -d "/etc/php5/cli/" && ! `cat /etc/php5/cli/php.ini | grep "mosquitto"` ]]; then
        echo "" | pecl install Mosquitto-alpha
        echo "extension=mosquitto.so" | tee -a /etc/php5/cli/php.ini
    fi
    if [[ -d "/etc/php5/fpm/" && ! `cat /etc/php5/fpm/php.ini | grep "mosquitto"` ]]; then
        echo "extension=mosquitto.so" | tee -a /etc/php5/fpm/php.ini
        service php5-fpm restart
    fi
    if [[ -d "/etc/php5/apache2/" && ! `cat /etc/php5/apache2/php.ini | grep "mosquitto"` ]]; then
        echo "extension=mosquitto.so" | tee -a /etc/php5/apache2/php.ini
    fi
    echo "[*] PHP5 Mosquitto has been installed"
fi
echo 80 > ${PROGRESS_FILE}

if [[ -d "/etc/php/7.0" ]]; then
    echo "[*] PHP7.0 is detected"
    echo "[*] Configuring PHP7.0 extension.."
    apt-get -y install php7.0-dev
    if [[ -d "/etc/php/7.0/cli/" && ! `cat /etc/php/7.0/cli/php.ini | grep "mosquitto"` ]]; then
        echo "" | pecl install Mosquitto-alpha
        echo "extension=mosquitto.so" | tee -a /etc/php/7.0/cli/php.ini
    fi
    if [[ -d "/etc/php/7.0/apache2/" && ! `cat /etc/php/7.0/apache2/php.ini | grep "mosquitto"` ]]; then
        echo "extension=mosquitto.so" | tee -a /etc/php/7.0/apache2/php.ini
    fi
    echo "[*] PHP7 Mosquitto has been installed"
fi
echo 100 > ${PROGRESS_FILE}

echo "--------------------------------"
echo "[*] Installation is done"
rm ${PROGRESS_FILE}
sudo service apache2 restart