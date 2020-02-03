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
