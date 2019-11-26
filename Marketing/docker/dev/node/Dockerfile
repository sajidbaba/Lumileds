FROM node:10.15-alpine

COPY entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/bin/sh", "/entrypoint.sh"]

RUN apk add --no-cache git

WORKDIR "/opt/lumileds"
VOLUME "/opt/lumileds"

CMD ["node"]
