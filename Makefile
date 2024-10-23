DDEV_PROJECT_NAME=laravel-api-demo
DDEV_PHP_VERSION=8.2
DDEV_DB_SERVER=mysql:8.0
DDEV_DOC_ROOT=public

ddev-init:
	@ddev config --database $(DDEV_DB_SERVER) --php-version $(DDEV_PHP_VERSION) --docroot $(DDEV_DOC_ROOT) --project-name $(DDEV_PROJECT_NAME) --project-type php
	@ddev start
	@make composer-install
	@make migrate
	@echo API Project should now be available at https://laravel-api-demo.ddev.site

ddev-start:
	@ddev start

ddev-stop:
	@ddev stop

ddev-destroy:
	@ddev delete --omit-snapshot
	@rm -rf .ddev vendor

ARG=
ddev-artisan:
	@ddev exec "php -f artisan $(ARG)"

composer-update:
	@ddev exec "composer update"

composer-install:
	@ddev exec "composer install"

migrate:
	@make ddev-artisan ARG='migrate'

rollback:
	@make ddev-artisan ARG='migrate:rollback'

db-seed:
	@make ddev-artisan ARG='db:seed'

db-reset:
	@make rollback
	@make migrate
	@make db-seed

run-queue:
	@make ddev-artisan ARG='queue:work'

run-tests:
	@make ddev-artisan ARG='test'

welcome-email-test:
	@make ddev-artisan ARG='app:test-verification-email 1'
	@make run-queue
