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
docker.build.psalm:
	docker build . --tag ${repoPsalm}:${version} -f .docker/Dockerfile.psalm

image.psalm.run:
	docker run --rm -it ${repoPsalm}:${version}