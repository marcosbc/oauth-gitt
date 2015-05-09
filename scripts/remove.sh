#!/bin/bash
. /opt/bitnami/scripts/setenv.sh

if [ "$(id -u)" != "0" ]
then
	echo "This script must be run as root" 1>&2
	exit
fi

# install dependencies via composer
echo "oauth-gitt: REMOVING DEPENDENCIES..."
cd /opt/bitnami/hosted/oauth-gitt/service/
rm -rf vendor composer.lock

# create db structure and insert sample data
echo "oauth-gitt: DROPPING DATABASE..."
php /opt/bitnami/hosted/oauth-gitt/scripts/service-install.php remove
