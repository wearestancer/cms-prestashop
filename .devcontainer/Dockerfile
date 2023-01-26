
ARG VARIANT="8.1-apache-bullseye"

FROM mcr.microsoft.com/vscode/devcontainers/php:0-${VARIANT}

ARG NODE_VERSION="none"
RUN if [ "${NODE_VERSION}" != "none" ]; then su vscode -c "umask 0002 && . /usr/local/share/nvm/nvm.sh && nvm install ${NODE_VERSION} 2>&1"; fi

RUN sed -i 's/9000/9003/' /usr/local/etc/php/conf.d/xdebug.ini \
 && sed -i 's/^xdebug\.start_with_request = yes/xdebug.start_with_request = trigger/' /usr/local/etc/php/conf.d/xdebug.ini

RUN npm install --global pnpm

RUN composer global require squizlabs/php_codesniffer
