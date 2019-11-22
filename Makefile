#!make
 
.DEFAULT_GOAL := help
.PHONY: help
help:
	@echo "\033[33mUsage:\033[0m\n  make [target] [arg=\"val\"...]\n\n\033[33mTargets:\033[0m"
	@grep -E '^[a-zA-Z0-9_-]+:.*?## .*$$' Makefile| sort | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[32m%-15s\033[0m %s\n", $$1, $$2}'

.PHONY: install
install: up ## Start containers and run doctrine migration as assets install
	docker-compose exec web composer install --no-interaction
	docker-compose exec web bin/console doctrine:database:create -nq --if-not-exists
	docker-compose exec web bin/console doctrine:migration:migrate --allow-no-migration --no-interaction

.PHONY: stop
stop: ## Shutdown all containers
	docker-compose stop

.PHONY: restart
restart: ## Reload all containers
	docker-compose restart

.PHONY: bash
bash: ## Connect into web container
	docker-compose exec web bash

.PHONY: purge
purge: ## Purge all containers and associated volumes
	docker-compose rm -sf

.PHONY: migrate
migrate: ## Generate and run migration
	docker-compose exec web bin/console doctrine:migration:migrate --allow-no-migration --no-interaction

.PHONY: up
up: ## Launch containers
	docker-compose up -d

.PHONY: force-db
force-db: ## Force DB Schema
	docker-compose exec web bin/console doctrine:schema:update --force

.PHONY: cache-clear
cache-clear: ## Clear cache
	docker-compose exec web bin/console cache:clear
