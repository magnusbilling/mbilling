#!/bin/bash
clear
echo
echo "----------- Send backup to another SSH Server: ----------"
echo "-------------- www.magnusbilling.com ---------------"
echo "------------- info@magnusbilling.com ---------------"
echo
sleep 1 

echo "Connecting ssh server $1"
DIA=`date +%d`
MES=`date +%m`
ANO=`date +%Y`

FECHA=$DIA"-"$MES"-"$ANO

cd /usr/local/src

passwordMysql=$2

ssh -t  $1 "cd /tmp/* "
scp backup_voip_Magnus.$FECHA.tgz $1:/tmp
ssh -t  $1 "cd /tmp && mysql -u root -p$passwordMysql -e 'DROP DATABASE IF EXISTS mbilling' && tar xzvf backup_voip_Magnus.$FECHA.tgz && mysql -u root -p$passwordMysql -e 'CREATE DATABASE mbilling' && mysql -u root -p$passwordMysql mbilling < tmp/base.sql"

#backup protect mbilling
echo "Backup send correctly"
