repoBase=ghcr.io/legion112/discriminator-default-normalizer/base
repoCI=ghcr.io/legion112/discriminator-default-normalizer/ci
repoCIDependency=ghcr.io/legion112/discriminator-default-normalizer/ci/cache

#composerLockHash := $(shell #echo whatever)
HASH:=$(shell md5 -q composer.lock)

github.registry.login:
	cat secrets.json | jq .CR_PAT -r | docker login ghcr.io -u Legion112 --password-stdin
docker.build.base:
	docker build . --tag ${repoBase}:${version} -f .docker/Dockerfile.base
docker.push.base:
	docker push ${repoBase}:${version}
docker.build.ci: docker.build.ci.dependency
	docker build . --tag ${repoCI}:${version} -f .docker/Dockerfile.ci \
		--cache-from=${repoCIDependency}:$(HASH)
docker.build.ci.dependency:
	docker build . --tag ${repoCIDependency}:$(HASH) -f .docker/Dockerfile.ci \
		--build-arg COMPOSER_LOCK_HASH=$(HASH) \
		--cache-from=${repoCIDependency}:$(HASH)
docker.run.psalm:
	docker-compose run -it cli psalm
docker.run.phpunit:
	docker-compose run -it cli vendor/bin/phpunit  --coverage-php .output/coverage.cov
docker.run.coverage.diff: docker.run.phpunit
	docker-compose run -it git diff HEAD^1 -- "**/*.php"  > .output/patch.txt
	docker-compose run -it coverage patch-coverage --path-prefix /app .output/coverage.cov .output/patch.txt