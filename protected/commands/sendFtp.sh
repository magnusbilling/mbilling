#!/bin/bash
clear
echo
echo "----------- Send backup to FTP Server: ----------"
echo "-------------- www.magnusbilling.com ---------------"
echo "------------- info@magnusbilling.com ---------------"
echo
sleep 1 

echo "Conectando y autenticando con el servidor de FTP $1"
DIA=`date +%d`
MES=`date +%m`
ANO=`date +%Y`

FECHA=$DIA"-"$MES"-"$ANO

cd /usr/local/src

HOST=$1
USER=$2
PASSWD=$3
ftp -n $HOST << END_SCRIPT
quote USER $USER
quote PASS $PASSWD
binary
echo "backup_voip_Magnus.$FECHA.tgz"
#delete backup_voip_Magnus.$FECHAOLD.tgz

put backup_voip_Magnus.$FECHA.tgz
quit
END_SCRIPT
echo "Archivo enviado correctamente"
