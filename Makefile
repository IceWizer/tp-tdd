SHELL := /bin/bash

tests:
	docker compose exec php php bin/console doctrine:database:drop --force --env=test || true
	docker compose exec php php bin/console doctrine:database:create --env=test
	docker compose exec php php bin/console doctrine:migrations:migrate -n --env=test
	docker compose exec php php bin/console doctrine:fixtures:load -n --env=test --group=test
	docker compose exec php php bin/phpunit --coverage-html public/test-report
.PHONY: tests