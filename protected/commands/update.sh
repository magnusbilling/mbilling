#!/bin/bash
clear
echo
echo
echo
echo "=======================WWW.MAGNUSBILLING.COM==========================";
echo "_      _                               ______ _ _ _ _  			 ";
echo "|\    /|                               | ___ (_) | (_) 			 ";
echo "| \  / | ___  ____ _ __  _   _   _____ | |_/ /_| | |_ _ __   __ _ 	 ";
echo "|  \/  |/   \/  _ \| '_ \| | | \| ___| | ___ \ | | | | '_ \ /  _ \	 ";
echo "| |\/| |  | |  (_| | | | | |_| ||____  | |_/ / | | | | | | |  (_| |	 ";
echo "|_|  |_|\___|\___  |_| | |_____|_____|  \___/|_|_|_|_|_| |_|\___  |	 ";
echo "                _/ |                                           _/ |	 ";
echo "               |__/                                           |__/ 	 ";
echo "														 ";
echo "============================== UPDATE =================================";
echo

sleep 2

if [[ -e /var/www/html/mbilling/protected/commands/update2.sh ]]; then
	/var/www/html/mbilling/protected/commands/update2.sh
	exit;
fi
rm -rf /var/www/html/mbilling/MBilling*.tar.gz
cd /var/www/html/mbilling
wget http://master.dl.sourceforge.net/project/magnusbilling/MBilling-5-current.tar.gz
rm -rf /var/www/html/mbilling/yii
tar xzf MBilling-5-current.tar.gz
rm -rf /var/www/html/mbilling/MBilling*.tar.gz
php /var/www/html/mbilling/cron.php UpdateMysql
rm -rf /var/www/html/mbilling/doc
chown -R asterisk:asterisk /var/lib/php/session/
chown -R asterisk:asterisk /var/spool/asterisk/outgoing/
chown -R asterisk:asterisk /etc/asterisk
chown -R asterisk:asterisk /var/www/html/mbilling
chmod -R 777 /tmp
chmod -R 555 /var/www/html/mbilling/
chmod -R 750 /var/www/html/mbilling/resources/reports 
chmod -R 774 /var/www/html/mbilling/protected/runtime/
chmod +x /var/www/html/mbilling/agi.php
if [ ! -d "/var/www/tmpmagnus" ]; then
mkdir /var/www/tmpmagnus
fi
chown -R asterisk:asterisk /var/www/tmpmagnus
chmod -R 777 /var/www/tmpmagnus
chmod 774 /var/www/html/mbilling/resources/ip.blacklist
chmod -R 655 /var/www/html/mbilling/tmp
chmod -R 750 /var/www/html/mbilling/resources/sounds
chmod -R 770 /var/www/html/mbilling/resources/images
