#!/bin/bash
# this script is meant to be called from inside a docker container
echo "install wordpress..."
wp-cli core install \
	--url=${WORDPRESS_URL} \
	--title=${WORDPRESS_TITLE} \
	--admin_user=${WORDPRESS_ADMIN_USER} \
	--admin_password=${WORDPRESS_ADMIN_PASSWORD} \
	--admin_email=${WORDPRESS_ADMIN_EMAIL}

# installs and activates all plugins in /tmp/plugins
find /tmp/plugins -type f -name '*.zip' | xargs -I {} wp-cli plugin install {} --activate
wp-cli plugin activate wp-bracket-builder

echo "done"

