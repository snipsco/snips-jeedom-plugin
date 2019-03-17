#! /bin/bash

echo "Start to install dependances"

touch /tmp/snips_dep
echo 0 > /tmp/snips_dep
apt-get -y install lsb-release php-pear
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

echo 10 > /tmp/snips_dep

apt-get update
echo 30 > /tmp/snips_dep
echo "Installing mqtt dependances"
apt-get -y install mosquitto mosquitto-clients libmosquitto-dev

echo 80 > /tmp/snips_dep

if [[ -d "/etc/php5/" ]]; then
    echo "PHP5 is detected"
    apt-get -y install php5-dev
    if [[ -d "/etc/php5/cli/" && ! `cat /etc/php5/cli/php.ini | grep "mosquitto"` ]]; then
        echo "" | pecl install Mosquitto-alpha
        echo 80 > /tmp/snips_dep
        echo "extension=mosquitto.so" | tee -a /etc/php5/cli/php.ini
    fi
    if [[ -d "/etc/php5/fpm/" && ! `cat /etc/php5/fpm/php.ini | grep "mosquitto"` ]]; then
        echo "extension=mosquitto.so" | tee -a /etc/php5/fpm/php.ini
        service php5-fpm restart
    fi
    if [[ -d "/etc/php5/apache2/" && ! `cat /etc/php5/apache2/php.ini | grep "mosquitto"` ]]; then
        echo "extension=mosquitto.so" | tee -a /etc/php5/apache2/php.ini
        service apache2 restart
    fi
    echo "PHP5 Mosquitto has been installed"
fi

if [[ -d "/etc/php/7.0" ]]; then
    echo "PHP7 is detected"
    apt-get -y install php7.0-dev
    if [[ -d "/etc/php/7.0/cli/" && ! `cat /etc/php/7.0/cli/php.ini | grep "mosquitto"` ]]; then
        echo "" | pecl install Mosquitto-alpha
        echo 80 > /tmp/snips_dep
        echo "extension=mosquitto.so" | tee -a /etc/php/7.0/cli/php.ini
    fi
    if [[ -d "/etc/php/7.0/apache2/" && ! `cat /etc/php/7.0/apache2/php.ini | grep "mosquitto"` ]]; then
        echo "extension=mosquitto.so" | tee -a /etc/php/7.0/apache2/php.ini
        service apache2 restart
    fi
    echo "PHP7 Mosquitto has been installed"
fi

rm /tmp/snips_dep

mkdir ../config_running
mkdir ../config_backup

echo "Dependances installation is done"