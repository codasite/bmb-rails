# https://marioyepes.com/wordpress-plugin-tdd-with-docker-phpunit/
FROM wordpress:php8.2

ARG PLUGIN_NAME

# Set up the os
RUN apt-get -qq update && \
        apt-get -y install vim unzip curl sudo subversion mariadb-client \
        && apt-get autoclean \
        && chsh -s /bin/bash www-data

# Install wp-cli
RUN curl https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar > /usr/local/bin/wp-cli.phar \
        && echo "#!/bin/bash" > /usr/local/bin/wp-cli \
        && echo "su www-data -c \"/usr/local/bin/wp-cli.phar --path=/var/www/html \$*\"" >> /usr/local/bin/wp-cli \
        && chmod 755 /usr/local/bin/wp-cli* \
        && echo "*** wp-cli command installed"

# Install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
        && php composer-setup.php \
        && php -r "unlink('composer-setup.php');" \
        && mv composer.phar /usr/local/bin/ \
        && echo "#!/bin/bash" > /usr/local/bin/composer \
        && echo "su www-data -c \"/usr/local/bin/composer.phar --working-dir=/var/www/html/wp-content/plugins/${PLUGIN_NAME} \$*\"" >> /usr/local/bin/composer \
        && chmod ugo+x /usr/local/bin/composer \
        && echo "*** composer command installed"

# Create testing environment
COPY --chmod=755 docker/wordpress/bin/* /usr/local/bin/

# Create a script to install the testing environment
RUN echo "#!/bin/bash" > /usr/local/bin/install-wp-tests \
        && echo "su www-data -c \"install-wp-tests.sh \${WORDPRESS_DB_NAME}_test root root \${WORDPRESS_DB_HOST} latest\"" >> /usr/local/bin/install-wp-tests \
        && chmod ugo+x /usr/local/bin/install-wp-test* \
        # && su www-data -c "/usr/local/bin/install-wp-tests.sh ${WORDPRESS_DB_NAME}_test root root '' latest true" \
        && echo "*** install-wp-tests installed"

# COPY --chmod=755 docker/wordpress/downloads /tmp/downloads

# the default docker image will copy files from /usr/src/wordpress to /var/www/html
# we can copy an existing wordpress installation there and then delete wp-config.php so that the docker default is used

# If the file exists, unzip it, else continue.
# RUN if [ -f /tmp/downloads/wordpress.zip ]; then \
#         echo "*** wordpress.zip exists. Unzipping"; \  
#         unzip -qq /tmp/downloads/wordpress.zip -d /tmp/from_site; \
#         rm -f /tmp/downloads/wordpress.zip; \
#     else \
#         echo "*** wordpress.zip does not exist. Using default image"; \
#     fi

# Check if the directory exists before performing further operations.
RUN if [ -d /tmp/from_site ]; then \
        echo "*** Deleting wp-config.php and copying to /usr/src/wordpress"; \
        rm -f /tmp/from_site/wp-config.php; \
        cp -r /tmp/from_site/* /usr/src/wordpress; \
        rm -rf /tmp/from_site; \
    fi; \
    echo "*** wordpress downloaded"
