#!/bin/bash
clear
echo
echo "----------- Split logs files: ----------"
echo "-------------- www.magnusbilling.com ---------------"
echo "------------- info@magnusbilling.com ---------------"
echo
sleep 1 

DIA=`date +%d`
MES=`date +%m`
ANO=`date +%Y`

FECHA=$ANO$MES$DIA
LASTMONTH=`date -d "$(date +%Y-%m-%d) -1 month" +%Y%m`


echo
echo "----------- Split Asterisk logs files: ----------"
echo "-------------- www.magnusbilling.com ---------------"
echo "------------- info@magnusbilling.com ---------------"
echo
cp -rf /var/log/asterisk/messages /var/log/asterisk/messages-$FECHA
echo '' > /var/log/asterisk/messages

cp -rf /var/log/asterisk/fail2ban /var/log/asterisk/fail2ban-$FECHA
echo '' > /var/log/asterisk/fail2ban


rm -rf /var/log/asterisk/*-$LASTMONTH*


echo
echo "----------- Split httpd logs files: ----------"
echo "-------------- www.magnusbilling.com ---------------"
echo "------------- info@magnusbilling.com ---------------"
echo
cp -rf /var/log/httpd/deflate_log /var/log/httpd/deflate_log-$FECHA
echo '' > /var/log/httpd/deflate_log

cp -rf /var/log/httpd/error_log /var/log/httpd/error_log-$FECHA
echo '' > /var/log/httpd/error_log

rm -rf /var/log/httpd/*-$LASTMONTH*


echo
echo "----------- DELETE last motnh logs files: ----------"
echo "-------------- www.magnusbilling.com ---------------"
echo "------------- info@magnusbilling.com ---------------"
echo

rm -rf /var/log/*-$LASTMONTH*