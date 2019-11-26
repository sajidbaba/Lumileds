FROM ubuntu:16.04

ENV LANG="en_US.UTF-8" \
    LC_ALL="en_US.UTF-8" \
    LANGUAGE="en_US.UTF-8" \
    TERM="xterm" \
    DEBIAN_FRONTEND="noninteractive" \
    COMPOSER_ALLOW_SUPERUSER=1 \
    SYMFONY_ENV="prod"

WORKDIR /app
EXPOSE 80 443

RUN apt-get update -q && \
    apt-get install -qy software-properties-common language-pack-en-base curl apt-transport-https && \
    add-apt-repository -y ppa:ondrej/php && \
    curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    curl -s https://deb.nodesource.com/gpgkey/nodesource.gpg.key | apt-key add - && \
    curl -sL https://deb.nodesource.com/setup_10.x | bash && \
    apt-get update && \
    apt-get install -y \
        vim mc apache2 openssl apt-transport-https \
        php7.1 php7.1-ldap php7.1-mysql php7.1-mbstring php7.1-soap php7.1-xml php7.1-cli php7.1-gd php7.1-curl \
        php7.1-imap php7.1-json php7.1-opcache php7.1-zip php7.1-dev php7.1-bz2 php7.1-xmlrp php7.1-bcmath supervisor \
        nodejs yarn && \
    a2enmod rewrite && \
    curl -sS https://getcomposer.org/installer | \
    php -- --install-dir=/usr/bin/ --filename=composer && \
    apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY . /app

COPY docker/prod/php-ini-overrides.ini /etc/php/7.1/apache2/conf.d/99-overrides.ini
COPY docker/prod/php-ini-overrides.ini /etc/php/7.1/cli/conf.d/99-overrides.ini
COPY docker/prod/000-default.conf /etc/apache2/sites-enabled/000-default.conf
COPY app/config/parameters.yml.dist /app/app/config/parameters.yml

COPY docker/prod/entrypoint.sh /usr/local/bin/entrypoint.sh
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

RUN chmod +x /usr/local/bin/entrypoint.sh && \
    yarn install && \
    yarn run prod && \
    yarn cache clean && \
    SYMFONY_ENV=prod composer install --optimize-autoloader --no-interaction --no-ansi --no-dev --no-scripts && \
    rm -rf docker node_modules

CMD ["/usr/sbin/apache2ctl", "-D", "FOREGROUND"]
