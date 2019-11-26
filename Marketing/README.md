# Lumileds

Symfony 3.3.*, Mysql, Nginx

## Requirements
 - [Docker][1]
 - [Docker Compose][2] (>= 1.11.0)

## Development

### Installation
```bash
git clone ssh://git@git.pentalog.fr:29418/PLUM01/Lumileds.git
cd Lumileds
make up
```

### Open site
1. open [http://0.0.0.0:80][4]

### Enter in container
```bash
# for php container
make enter_php

# for node container
make enter_node

# for another container
docker-compose exec CONTAINER_NAME bash
```
Instead of CONTAINER_NAME can use: php, node, webserver, mysql

### Enter in RabbitMQ Management UI
1. open [http://0.0.0.0:15672][3]
2. use logging and password from `.env` file

### Rebuild containers
If some changes appeared in docker-compose.yml file or docker folder then rebuild images with next command:
```bash
make rebuild
```

### Enter in MailHog
Open [http://0.0.0.0:15672][5]

## Production

Build image:

```bash
docker build -t marketingmodel .
```

Tag it:

```bash
docker tag marketingmodel:latest 615590900630.dkr.ecr.eu-west-1.amazonaws.com/marketingmodel:latest
```

Login:

```bash
$(aws ecr get-login --no-include-email --region eu-west-1)
```

Push to registry:

```bash
docker push 615590900630.dkr.ecr.eu-west-1.amazonaws.com/marketingmodel:latest
```

Run this command if you want to test built image locally:

```bash
docker run --env-file ./.env -p 82:80 --network lumileds_default 615590900630.dkr.ecr.eu-west-1.amazonaws.com/marketingmodel
```

[1]: https://docs.docker.com/engine/installation/linux/docker-ce/ubuntu/
[2]: https://docs.docker.com/compose/install/
[3]: http://0.0.0.0:15672
[4]: http://0.0.0.0:80
[5]: http://0.0.0.0:15672
