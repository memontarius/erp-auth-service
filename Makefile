
ENV_PATH=./.env
ifneq ("$(wildcard $(ENV_PATH))","")
	 include $(ENV_PATH)
endif

cnn=$(APP_NAME)_app # Container name

#------ Setup
prepare-env:
	cp -n .env.example .env || true
	make key
	php artisan jwt:secret

install:
	composer install
	npm i
	npm run build

setup:
	sudo chown -R $(USER):www-data storage
	sudo chown -R $(USER):www-data bootstrap/cache
	sudo chmod 775 -R storage/
	sudo chmod 775 -R bootstrap/cache/
	make c-mig

#------ Helpers
key:
	php artisan key:generate


#------ Docker
up:
	docker compose up -d

dw:
	docker compose down

in:
	docker exec -it $(cnn) bash

c=DatabaseSeeder
#------ DB
mig:
	php artisan migrate

migr:
	php artisan migrate:rollback

seed:
	php artisan db:seed --class=$(c)

migrf:
	php artisan migrate:refresh


#------ Container
c-seed:
	docker exec $(APP_NAME)_app make seed c=$(c)

c-mig:
	docker exec $(APP_NAME)_app make mig

c-migr:
	docker exec $(APP_NAME)_app make migr

c-migrf:
	docker exec $(APP_NAME)_app make migrf
