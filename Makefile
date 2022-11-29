repoBase=ghcr.io/legion112/discriminator-default-normalizer/base
repoCI=ghcr.io/legion112/discriminator-default-normalizer/ci
repoPsalm=ghcr.io/legion112/discriminator-default-normalizer/psalm
github.registry.login:
	cat secrets.json | jq .CR_PAT -r | docker login ghcr.io -u Legion112 --password-stdin
docker.build.base:
	docker build . --tag ${repoBase}:${version} -f .docker/Dockerfile.base
docker.push.base:
	docker push ${repoBase}:${version}
docker.build.ci:
	docker build . --tag ${repoCI}:${version} -f .docker/Dockerfile.ci
docker.run.psalm:
	docker-compose run -it cli psalm
docker.run.phpunit:
	docker-compose run -it cli vendor/bin/phpunit  --coverage-php .output/coverage.cov
docker.run.coverage.diff: docker.run.phpunit
	docker-compose run -it git diff HEAD^1 > .output/patch.txt
	docker-compose run -it coverage patch-coverage --path-prefix /app .output/coverage.cov .output/patch.txt