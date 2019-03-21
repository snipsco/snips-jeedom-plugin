#! /bin/bash

echo "[jeedom-plugin-snips]"
echo "--------------------------------"
echo "[*] Start to remove dependencies."

if [[ -d "/etc/php5/" ]]; then
    echo "[*] Removing extension from PHP5"
    if [[ -d "/etc/php5/cli/" && ! `cat /etc/php5/cli/php.ini | grep "mosquitto"` ]]; then
        sed -i '/extension=mosquitto.so/d' /etc/php5/cli/php.ini
    fi
    if [[ -d "/etc/php5/fpm/" && ! `cat /etc/php5/fpm/php.ini | grep "mosquitto"` ]]; then
        sed -i '/extension=mosquitto.so/d' /etc/php5/fpm/php.ini
        service php5-fpm restart
    fi
    if [[ -d "/etc/php5/apache2/" && ! `cat /etc/php5/apache2/php.ini | grep "mosquitto"` ]]; then
        sed -i '/extension=mosquitto.so/d' /etc/php5/apache2/php.ini
    fi
fi

if [[ -d "/etc/php/7.0/" ]]; then
    echo "[*] Removing extension from PHP7.0"
    if [[ -d "/etc/php/7.0/cli/" && `cat /etc/php/7.0/cli/php.ini | grep "mosquitto"` ]]; then
        sed -i '/extension=mosquitto.so/d' /etc/php/7.0/cli/php.ini
    fi
    if [[ -d "/etc/php/7.0/apache2/" && `cat /etc/php/7.0/apache2/php.ini | grep "mosquitto"` ]]; then
        sed -i '/extension=mosquitto.so/d' /etc/php/7.0/apache2/php.ini
    fi
fi

echo "--------------------------------"
echo "[*] Uninstallation is done."
service apache2 restart