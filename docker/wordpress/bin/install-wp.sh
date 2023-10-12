#!/bin/bash
# this script is meant to be called from inside a docker container
echo "install wordpress..."
wp-cli core install \
	--url=${WORDPRESS_URL} \
	--title=${WORDPRESS_TITLE} \
	--admin_user=${WORDPRESS_ADMIN_USER} \
	--admin_password=${WORDPRESS_ADMIN_PASSWORD} \
	--admin_email=${WORDPRESS_ADMIN_EMAIL}

# wp-cli plugin install /tmp/plugins/oxygen4.5.zip --activate
# wp-cli plugin install /tmp/plugins/oxygen-woocommerce.zip --activate

echo "done"

