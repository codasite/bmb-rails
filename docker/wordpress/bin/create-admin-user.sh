#!/bin/bash
# this script is meant to be called from inside the docker container
echo "Adding admin user..."
wp-cli user create ${WORDPRESS_ADMIN_USER} ${WORDPRESS_ADMIN_EMAIL} \
	--user_pass=${WORDPRESS_ADMIN_PASSWORD} \
	--role=administrator

echo "done"

