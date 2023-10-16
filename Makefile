# Try to pull the latest wordpress files from the server
wp-pull:
	./docker/wordpress/bin/pull-wp.sh

# Start all wordpress containers
wp-up:
	docker compose --profile test up --build

# Start all wordpress containers in detached mode for ci
wp-up-detach:
	docker compose --profile test up --build --detach

# Stop and remove all wordpress containers
wp-down:
	docker compose --profile test down -v

# Install wordpress and the test installation
# This command can be used when building from scratch
wp-install:
	docker exec wordpress-test-app install-wp.sh
	docker exec wordpress-test-app install-wp-tests

# Initialize the test installation and add admin user without installing
# This command can be used when building from a site backup or db dump
wp-init:
	docker exec wordpress-test-app install-wp-tests
	docker exec wordpress-test-app create-admin-user.sh

# Run tests
wp-test:
	docker exec wordpress-test-app composer test

# Read out debug log (ignore deprecated warnings)
wp-log:
	docker exec wordpress-test-app tail -999999 /var/www/html/wp-content/debug.log | grep -v 'deprecated' | less +G

# Dump the database into the mariadb init folder
wp-dump:
	docker exec wordpress-test-db /bin/bash -c 'mariadb-dump -u root -p"$$MYSQL_ROOT_PASSWORD" wordpress-test > /docker-entrypoint-initdb.d/dump.sql'

images-up:
	docker compose --profile images up --build

# Start all containers in dev mode
up:
	docker compose --profile all up --build

# Stop and remove all containers
down:
	docker compose down -v

pretty:
	npm run pretty
