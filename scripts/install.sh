#!/bin/bash
. /opt/bitnami/scripts/setenv.sh

if [ "$(id -u)" != "0" ]
then
	echo "This script must be run as root" 1>&2
	exit
fi

# install dependencies via composer
echo "oauth-gitt: INSTALLING DEPENDENCIES..."
cd /opt/bitnami/hosted/oauth-gitt/service/
composer install

# patch oauth2-server
echo "oauth-gitt: APPLYING PATCHES..."
patch -p1 -d /opt/bitnami/hosted/oauth-gitt/service/vendor/league/oauth2-server/src/ --input /opt/bitnami/hosted/oauth-gitt/scripts/oauth2-server.patch

# create db structure and insert sample data
echo "oauth-gitt: CREATING DATABASE AND INSERTING SAMPLE DATA..."
php /opt/bitnami/hosted/oauth-gitt/scripts/service-install.php
