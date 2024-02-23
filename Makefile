# Try to pull the latest wordpress files from the server
wp-pull:
	./docker/wordpress/bin/pull-wp.sh

# Start all wordpress containers
wp-up:
	docker compose --profile wp up --build --remove-orphans;

# Start all wordpress containers in detached mode for ci
wp-up-detach:
	docker compose --profile wp up --build --detach --remove-orphans

# Stop and remove all wordpress containers
wp-down:
	docker compose --profile wp down -v

# Install wordpress and the test installation
# This command can be used when building from scratch
wp-install:
	docker exec wp-dev install-wp.sh
	docker exec wp-dev install-wp-tests

# Initialize the test installation and add admin user without installing
# This command can be used when building from a site backup or db dump
wp-init:
	docker exec wp-dev install-wp-tests
	docker exec wp-dev create-admin-user.sh

# Run tests
# run a single test like this: make wp-test args="--filter NotificationRepoTest"

# Run all tests in plugin/tests/unit. Uses WP_Mock to mock wordpress functions
wp-unit:
	docker exec wp-dev composer test-unit -- $(args)

wp-unit-update-snapshots:
	make wp-unit args="-d --update-snapshots"

# Run all tests in plugin/tests/integration. Loads the full wordpress environment.
wp-integration:
	docker exec wp-dev composer test-integration -- $(args)

wp-integration-update-snapshots:
	make wp-integration args="-d --update-snapshots"

# Run all tests 
wp-test:
	make wp-integration wp-unit

# Run tests with coverage
wp-cover:
	docker exec wp-dev composer coverage

# Read out debug log (ignore deprecated warnings)
wp-log:
	docker exec wp-dev tail -999999 /var/www/html/wp-content/debug.log | grep -v 'deprecated' | less +G

wp-log-watch:
	docker exec wp-dev tail -f /var/www/html/wp-content/debug.log | grep -v 'deprecated'

# Dump the database into the mariadb init folder
wp-dump:
	docker exec wp-dev-db /bin/bash -c 'mariadb-dump -u root -p"$$MYSQL_ROOT_PASSWORD" wordpress > /docker-entrypoint-initdb.d/dump.sql'

# Run sql query
wp-sql:
	docker exec wp-dev-db /bin/bash -c 'mariadb -u root -p"$$MYSQL_ROOT_PASSWORD" wordpress -e "$(query)"'

images-up:
	docker compose --profile images up --build --remove-orphans

images-down:
	docker compose --profile images down -v

# Start all containers in dev mode
up:
	docker compose --profile all up --build --remove-orphans

# Stop and remove all containers
down:
	docker compose down -v

test:
	make wp-test
	make react-test

pretty:
	npm run pretty

react-start:
	cd react-bracket-builder && npm run start:plugin

react-build:
	cd react-bracket-builder && npm run build:plugin

react-install:
	cd react-bracket-builder && npm install

react-test:
	cd react-bracket-builder && npm run test $(args)

react-test-update-snapshots:
	cd react-bracket-builder && npm run test -- -u

composer-install:
	cd plugin && composer install

phpstan:
	cd plugin && vendor/bin/phpstan analyse -c phpstan.neon --memory-limit 2G .

phpstan-ci:
	cd plugin && vendor/bin/phpstan analyse -c phpstan.neon.dist --memory-limit 2G .

phpstan-generate-baseline:
	cd plugin && vendor/bin/phpstan analyse -c phpstan.neon.dist --memory-limit 2G . --generate-baseline

prod-pull:
	docker compose -f compose.yaml -f compose.prod.yaml --profile all pull

prod-up:
	docker compose -f compose.yaml -f compose.prod.yaml --profile all -p wpbb up -d --no-build --remove-orphans --force-recreate --pull always

prod-build-plugin:
	docker compose -f compose.yaml -f compose.prod.yaml --profile plugin -p wpbb build

prod-build-images:
	docker compose -f compose.yaml -f compose.prod.yaml --profile images -p wpbb build

prod-build:
	docker compose -f compose.yaml -f compose.prod.yaml --profile all -p wpbb build

prod-push-plugin:
	docker compose -f compose.yaml -f compose.prod.yaml --profile plugin -p wpbb push

prod-push-images:
	docker compose -f compose.yaml -f compose.prod.yaml --profile images -p wpbb push

prod-push:
	docker compose -f compose.yaml -f compose.prod.yaml --profile all -p wpbb push
	
prod-down:
	docker compose -f compose.yaml -f compose.prod.yaml -p wpbb down -v
