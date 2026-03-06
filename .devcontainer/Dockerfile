# syntax=docker/dockerfile:1

ARG DOCKER_PROXY=docker.io/wearestancer
ARG PRESTASHOP_VERSION=9.0
ARG PHP_VERSION=8.1


FROM ${DOCKER_PROXY}/php:8.2 AS scoper

ARG SCOPER_VERSION="0.18.7"

RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
  --mount=type=cache,target=/var/lib/apt,sharing=locked apt update \
  && apt upgrade -y \
  && apt install -y --no-install-recommends gpg

ADD "https://github.com/humbug/php-scoper/releases/download/${SCOPER_VERSION}/php-scoper.phar" /scoper.phar
ADD "https://github.com/humbug/php-scoper/releases/download/${SCOPER_VERSION}/php-scoper.phar.asc" /scoper.phar.asc

RUN gpg --keyserver hkps://keys.openpgp.org --recv-keys 74A754C9778AA03AA451D1C1A000F927D67184EE

RUN gpg --verify /scoper.phar.asc /scoper.phar



FROM ${DOCKER_PROXY}/prestashop:${PRESTASHOP_VERSION}-php${PHP_VERSION}

ARG TARGET_USER=vscode

# Install sudo
RUN --mount=type=cache,target=/var/cache/apt,sharing=locked \
  --mount=type=cache,target=/var/lib/apt,sharing=locked apt update \
  && apt upgrade -y \
  && apt install -y --no-install-recommends sudo vim \
  && sed -i 's/%sudo.*/%sudo   ALL=(ALL:ALL) NOPASSWD: ALL/' /etc/sudoers

# Create user
RUN useradd --create-home --shell /bin/bash --uid 1000 --groups sudo,www-data $TARGET_USER

# Ensure target folder is available
RUN mkdir -p /var/www/html/modules/stancer

COPY ./fs/post-attach /post-attach
RUN chmod +x /post-attach

COPY --from=scoper --chmod=755 /scoper.phar /usr/local/bin/scoper
USER $TARGET_USER
