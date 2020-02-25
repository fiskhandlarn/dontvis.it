install:
	composer install
	npm install

build: build%prod

build%dev:
	npm run dev

build%prod:
	npm run prod

watch:
	npm run watch

up:
	docker-compose up -d
	xdg-open "https://localhost:3000/"

down:
	docker-compose down --remove-orphans

logs:
	docker-compose logs

ssl%create:
	mkdir -p .docker/.ssl
	openssl req -x509 -nodes -days 3650 -newkey rsa:2048 -keyout .docker/.ssl/server.key -out .docker/.ssl/server.pem

test:
	./vendor/bin/phpunit tests

docker%test:
	if [ ! `docker-compose exec php echo 'up'` ]; then echo -en "\e[0m"; docker-compose up -d; fi
	docker-compose run php sh -c "/app/vendor/bin/phpunit /app/tests"
