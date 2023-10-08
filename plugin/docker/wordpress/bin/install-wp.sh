#!/bin/bash
wp-cli core install \
	--url=${WORDPRESS_URL} \
	--title=${WORDPRESS_TITLE} \
	--admin_user=${WORDPRESS_ADMIN_USER} \
	--admin_password=${WORDPRESS_ADMIN_PASSWORD} \
	--admin_email=${WORDPRESS_ADMIN_EMAIL}

# wp-cli plugin install /tmp/oxygen.zip --activate
# wp-cli plugin install /tmp/oxygen-woo.zip --activate
wp-cli plugin activate ${PLUGIN_NAME}
