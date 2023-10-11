# Try to pull the latest wordpress files from the server
wp-pull:
	./docker/wordpress/bin/pull-wp.sh

# Start all wordpress containers
wp-up:
	docker compose --profile test up --build

# Stop and remove all wordpress containers
wp-down:
	docker compose --profile test down -v

# Install wordpress and the test installation
wp-install:
	docker exec wordpress-test-app install-wp.sh

# Run tests
wp-test:
	docker exec wordpress-test-app composer test

# Start all containers in dev mode
up:
	docker compose up --build

# Stop and remove all containers
down:
	docker compose down -v