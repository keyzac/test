.DEFAULT_GOAL := help

## GENERAL ##
OWNER 			= neoauto
SERVICE_NAME 	= searchs
VERSION         = v2
PATH_PREFIX 	= "/v2"

## DEV ##
TAG_DEV			= dev
TAG_CLI 		= cli
LOCAR_REGISTRY 	= local.neo.registry:5000
DOCKER_NETWORK 	= neo_network

## DEPLOY ##
ENV 			?= dev
BUILD_NUMBER 	?= 000001
BUILD_TIMESTAMP ?= 20181004
DEPLOY_REGION 	?= eu-west-1
ACCOUNT_ID		?= 929226109038
DESIRED_COUNT 	?= 1
MIN_SCALING		?= 1
MAX_SCALING		?= 2
HTTP_PRIORITY 	?= 11
HTTPS_PRIORITY 	?= 22
MEMORY_SIZE 	?= 256
CONTAINER_PORT 	?= 80
INFRA_BUCKET 	?= infraestructura.dev

## RESULT_VARS ##
PROJECT_NAME	= ${OWNER}-${ENV}-${SERVICE_NAME}${VERSION}
CONTAINER_NAME 	= ${PROJECT_NAME}_backend
IMAGE_DEV		= ${PROJECT_NAME}:${TAG_DEV}
IMAGE_CLI		= ${PROJECT_NAME}:${TAG_CLI}
TAG_DEPLOY		= ${BUILD_TIMESTAMP}.${BUILD_NUMBER}
IMAGE_DEPLOY	= ${PROJECT_NAME}:${TAG_DEPLOY}
CLUSTER 		= ${OWNER}-dev
DEPLOY_REGISTRY = ${ACCOUNT_ID}.dkr.ecr.${DEPLOY_REGION}.amazonaws.com
STACK_PATH		= ${INFRA_BUCKET}/build/cloudformation/${OWNER}/${ENV}/${PROJECT_NAME}

## Commons ##

composer: ## Update dependencies from packagist and other private resources: make composer
	docker run --rm -t -v $$PWD/app:/app -v $$HOME/.ssh:/root/.ssh ${IMAGE_CLI} composer update

build: ## build image to dev and cli: make build
	docker build -f docker/cli/Dockerfile -t ${IMAGE_CLI} docker/cli/
	docker build -f docker/dev/Dockerfile -t ${IMAGE_DEV} docker/dev/

## Function Dev ##
pull: ## pull docker images from local registery: make pull
	docker pull $(LOCAL_REGISTRY)/${IMAGE_DEV}
	docker tag $(LOCAL_REGISTRY)/${IMAGE_DEV} ${IMAGE_DEV}
	docker rmi $(LOCAL_REGISTRY)/${IMAGE_DEV}

push: ## push docker images to local registry: make push
	docker tag ${IMAGE_DEV} ${LOCAL_REGISTRY}/${IMAGE_DEV}
	docker push ${LOCAL_REGISTRY}/${IMAGE_DEV}
	docker rmi ${LOCAL_REGISTRY}/${IMAGE_DEV}
	docker images

up: ## up docker containers: make up
	@make verify_network &> /dev/null
	@IMAGE_DEV=${IMAGE_DEV} \
	CONTAINER_NAME=${CONTAINER_NAME} \
	docker-compose -p $(SERVICE_NAME)$(VERSION) up -d backend
	docker-compose -p $(SERVICE_NAME)$(VERSION) ps

down: ## Stops and removes the docker containers: make down
	@IMAGE_DEV=${IMAGE_DEV} \
	IMAGE_CLI=${IMAGE_CLI} \
	CONTAINER_NAME=${CONTAINER_NAME} \
	docker-compose -p $(SERVICE_NAME)$(VERSION) down

status: ## Show containers status: make status
	docker-compose -p $(SERVICE_NAME)$(VERSION) ps

ssh: ## Connect to conainer for ssh protocol
	docker exec -it $(SERVICE_NAME)$(VERSION) bash

artisan: ## exec artisan commands, exec "make artisan-help" for show availables commands: make artisan COMMANND=my_command
	docker run --rm -t --network="host" -v $$PWD/app:/app ${IMAGE_CLI} bash -c "php artisan ${COMMAND}"

artisan-help: ## Show availables artisan commands: make artisan-help
	@echo migrate
	@echo rollback
	@echo db:seed --class=ClientTableSeeder
	@echo db:seed --class=RoleTableSeeder
	@echo db:seed --class=TestClientTableSeeder
	@echo db:seed oauth:keys

verify_network: ## Verify the local network was created in docker, normaly created before up container service: make verify_network
	@if [ -z $$(docker network ls | grep $(DOCKER_NETWORK) | awk '{print $$2}') ]; then\
		(docker network create $(DOCKER_NETWORK));\
	fi

## Deploy ##
sync-cloudformation: ## Sync additional cloudformation resources in S3 before to push image to registry in aws: make sync-cloudformation
	aws s3 sync ./cloudformation/stacks s3://${STACK_PATH}

sync-config: ## Sync configs files from S3 before to push image to registry in aws: make sync-config
	aws s3 sync s3://${INFRA_BUCKET}/config/container/${OWNER}/${ENV}/${SERVICE_NAME}${VERSION}/ app/config/

push-config: ## Sync configs files to push: make sync-config
	aws s3 sync app/config/ s3://${INFRA_BUCKET}/config/container/${OWNER}/${ENV}/${SERVICE_NAME}${VERSION}/

update-service: ## Deploy service with cloudformation: make update-service
	@make sync-cloudformation
	aws cloudformation deploy \
	--template-file ./cloudformation/master.yml \
	--stack-name ${PROJECT_NAME}-service \
	--parameter-overrides \
		S3Path=${STACK_PATH} \
		HttpListenerPriority=${HTTP_PRIORITY} \
		HttpsListenerPriority=${HTTPS_PRIORITY} \
		DesiredCount=${DESIRED_COUNT} \
		MaxScaling=${MAX_SCALING} \
		MinScaling=${MIN_SCALING} \
		Image=${DEPLOY_REGISTRY}/${IMAGE_DEPLOY} \
		ServiceName=${SERVICE_NAME} \
		Version=${VERSION} \
		Env=${ENV} \
		Owner=${OWNER} \
		PathPrefix=${PATH_PREFIX} \
		ContainerPort=${CONTAINER_PORT} \
		MemorySize=${MEMORY_SIZE} \
	--region ${DEPLOY_REGION} \
	--capabilities CAPABILITY_NAMED_IAM

create-registry: ## Create registry in aws ECR service: make create-registry
	aws cloudformation deploy \
	--template-file ./cloudformation/registry.yml \
	--stack-name ${PROJECT_NAME}-registry \
	--parameter-overrides \
		ProjectName=$(PROJECT_NAME) \
	--region $(DEPLOY_REGION) \
	--capabilities CAPABILITY_IAM

install: ## Exec composer-update un cli image: make install
	@make build composer

deploy: ## Exec all step to deploy microservice in aws: make deploy
	@make sync-config install build-latest publish update-service

build-latest: ## Build image to push to aws ECR: make build-latest
	docker build -f docker/latest/Dockerfile --no-cache --build-arg IMAGE=${IMAGE_DEV} -t ${IMAGE_DEPLOY} .

publish: ## Push image to aws ECR: make publish
	docker tag ${IMAGE_DEPLOY} ${DEPLOY_REGISTRY}/${IMAGE_DEPLOY}
	aws --region ${DEPLOY_REGION} ecr get-login --no-include-email | sh
	docker push ${DEPLOY_REGISTRY}/${IMAGE_DEPLOY}

## Help ##

help:
	@printf "\033[31m%-16s %-59s %s\033[0m\n" "Target" "Help" "Usage"; \
	printf "\033[31m%-16s %-59s %s\033[0m\n" "------" "----" "-----"; \
	grep -hE '^\S+:.*## .*$$' $(MAKEFILE_LIST) | sed -e 's/:.*##\s*/:/' | sort | awk 'BEGIN {FS = ":"}; {printf "\033[32m%-16s\033[0m %-58s \033[34m%s\033[0m\n", $$1, $$2, $$3}'