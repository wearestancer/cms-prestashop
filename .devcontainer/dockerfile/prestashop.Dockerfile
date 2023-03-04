# syntax = docker/dockerfile:1.2

ARG IMAGE_TAG

FROM prestashop/prestashop:${IMAGE_TAG}

ARG MODULE
ARG VSCODE_USER=vscode

EXPOSE 80
EXPOSE 443

RUN --mount=type=cache,target=/var/lib/apt/lists apt update \
 && apt install -y git sudo \
 && sed -i 's/%sudo.*/%sudo   ALL=(ALL:ALL) NOPASSWD: ALL/' /etc/sudoers

RUN --mount=type=cache,target=/var/lib/apt/lists curl -sL https://deb.nodesource.com/setup_lts.x | bash -
RUN --mount=type=cache,target=/var/lib/apt/lists apt update && apt install -y nodejs
RUN npm install --global pnpm


RUN php -r "copy('https://getcomposer.org/installer', '/tmp/composer-setup.php');" \
 && php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer \
 && rm -rf /tmp/composer-setup.php


RUN mkdir -p /etc/apache2/ssl
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"


RUN pecl install xdebug && docker-php-ext-enable xdebug
# RUN sed -i 's/9000/9003/' /usr/local/etc/php/conf.d/xdebug.ini \
#  && sed -i 's/^xdebug\.start_with_request = yes/xdebug.start_with_request = trigger/' /usr/local/etc/php/conf.d/xdebug.ini


COPY ./ssl/localhost.cnf /etc/ssl/
COPY ./ssl/renew-certs.sh /usr/local/bin/renew-certs
RUN chmod +x /usr/local/bin/renew-certs && renew-certs

COPY ./etc/apache/localhost.conf /etc/apache2/sites-enabled/localhost.conf

COPY ./scripts/post-install.sh /tmp/post-install-scripts/post-install.sh
RUN chmod +x /tmp/post-install-scripts/post-install.sh

COPY ./scripts/start.sh /etc/docker_start
RUN chmod +x /etc/docker_start


RUN a2enmod rewrite && a2enmod ssl
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && echo "Mutex posixsem" >> /etc/apache2/apache2.conf


RUN useradd --create-home --shell /bin/bash --groups www-data,sudo $VSCODE_USER
