# syntax=docker/dockerfile:1

ARG DOCKER_PROXY=docker.io/wearestancer
ARG PRESTASHOP_VERSION=1.7.3
ARG PHP_VERSION=7.1

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

USER $TARGET_USER
