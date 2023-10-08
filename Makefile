
wp-up:
	docker compose --profile test up --build

wp-down:
	docker compose --profile test down -v

wp-install:
	docker exec wordpress-test-app install-wp.sh

wp-test:
	docker exec wordpress-test-app composer test