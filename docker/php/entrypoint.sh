#!/bin/bash

source /var/www/html/.env


if [ "$1" != "" ]; then
	exec "$@";
	echo "Hey, I'm here!";
	exit 0;
fi

composer install

php bin/console d:m:m --no-interaction

if [ ! -f $JWT_SECRET_KEY ]; then
	php bin/console lexik:jwt:generate-keypair
else
	echo "JWT secret key already exists"
fi

php-fpm