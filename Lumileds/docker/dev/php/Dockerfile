FROM phpdockerio/php71-fpm:latest

# Install selected extensions and other stuff
RUN apt-get update \
    && apt-get -y --no-install-recommends install \
        php7.1-mysql \
        php7.1-intl \
        php-xdebug \
        php7.1-bcmath \
        netcat \
        git \
        supervisor \
        nano \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

# Install Composer
RUN mkdir -p /usr/local/bin
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Link MailHog
RUN curl -Lsf 'https://storage.googleapis.com/golang/go1.8.3.linux-amd64.tar.gz' | tar -C '/usr/local' -xvzf -
ENV PATH /usr/local/go/bin:$PATH
RUN go get github.com/mailhog/mhsendmail
RUN cp /root/go/bin/mhsendmail /usr/bin/mhsendmail

# Copy php ini conf
COPY php-ini-overrides.ini /etc/php/7.1/fpm/conf.d/99-overrides.ini
COPY php-ini-overrides.ini /etc/php/7.1/cli/conf.d/99-overrides.ini

# Copy crontab config
COPY crontab /etc/crontab

# Copy and setup entrypoint.sh
COPY entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/bin/bash", "/entrypoint.sh"]

WORKDIR "/opt/lumileds"
VOLUME "/opt/lumileds"
EXPOSE 9000

CMD ["php-fpm"]
