# NRFC Fixtures images for:
# Fast CGI Prod: ghcr.io/tobybatch/nrfc-fixtures:fpm-prod
# Apache Prod:   ghcr.io/tobybatch/nrfc-fixtures:apache-prod
# Fast CGI Dev:  ghcr.io/tobybatch/nrfc-fixtures:fpm-dev
# Apache Dev:    ghcr.io/tobybatch/nrfc-fixtures:apache-dev
# ---------------------------------------------------------------------
# For local testing by maintainer:
# cp .docker/sample.dev.env .docker/dev.env
# docker compose -f .docker/compose.dev.yml up -d
# docker exec -ti nrfrc-fixtures-apache-app /bin/bash
# docker exec -ti nrfrc-fixtures-apache-app symfony serve --port=7000 --listen-ip=0.0.0.0

# For dev
# docker build -t ghcr.io/tobybatch/nrfc-fixtures:apache-dev --target=dev --build-arg BASE=apache .
# For testing prod
# docker build -t ghcr.io/tobybatch/nrfc-fixtures:fpm-prod .
# cp .docker/sample.prod.env .docker/prod.env
# docker compose -f .docker/compose.prod.yml up -d
# ---------------------------------------------------------------------

# Source base, one of: fpm, apache
ARG BASE="fpm"
# nrfrc-fixtures branch/tag to run
ARG VERSION="main"
# Timezone for images
ARG TIMEZONE="Europe/Berlin"

###########################
# Shared tools
###########################

# composer base image
FROM composer:latest AS composer

###########################
# PHP extensions
###########################

# fpm alpine php extension base
FROM php:8.3-fpm-alpine AS fpm-php-ext-base
RUN apk add --no-cache \
    # build-tools
    autoconf \
    dpkg \
    dpkg-dev \
    file \
    g++ \
    gcc \
    icu-dev \
    libatomic \
    libc-dev \
    libgomp \
    libmagic \
    m4 \
    make \
    mpc1 \
    mpfr4 \
    musl-dev \
    perl \
    re2c \
    # postgres
    libpq-dev \
    postgresql \
    # gd
    freetype-dev \
    libpng-dev \
    # icu
    icu-dev \
    icu-data-full \
    # ldap
    openldap-dev \
    libldap \
    # zip
    libzip-dev \
    # xsl
    libxslt-dev

# apache debian php extension base
FROM php:8.3-apache-bookworm AS apache-php-ext-base
RUN apt-get update && \
    apt-get install -y \
        libldap2-dev \
        libicu-dev \
        libpng-dev \
        libzip-dev \
        libxslt1-dev \
        libfreetype6-dev \
        libpq-dev \
        postgresql

# php extension gd - 13.86s
FROM ${BASE}-php-ext-base AS php-ext-gd
RUN docker-php-ext-configure gd \
        --with-freetype && \
    docker-php-ext-install -j$(nproc) gd

# php extension intl : 15.26s
FROM ${BASE}-php-ext-base AS php-ext-intl
RUN docker-php-ext-install -j$(nproc) intl

# php extension ldap : 8.45s
FROM ${BASE}-php-ext-base AS php-ext-ldap
RUN docker-php-ext-configure ldap && \
    docker-php-ext-install -j$(nproc) ldap

# php extension pdo_mysql : 6.14s
FROM ${BASE}-php-ext-base AS php-ext-pdo_pgsql
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql && \
    docker-php-ext-install -j$(nproc) pgsql pdo pdo_pgsql

# php extension zip : 8.18s
FROM ${BASE}-php-ext-base AS php-ext-zip
RUN docker-php-ext-install -j$(nproc) zip

# php extension xsl : ?.?? s
FROM ${BASE}-php-ext-base AS php-ext-xsl
RUN docker-php-ext-install -j$(nproc) xsl

# php extension opcache
FROM ${BASE}-php-ext-base AS php-ext-opcache
RUN docker-php-ext-install -j$(nproc) opcache

## php extension xdebug
#FROM ${BASE}-php-ext-base AS php-ext-xdebug
#RUN docker-php-ext-install -j$(nproc) xdebug

# Build stage for Xdebug
FROM ${BASE}-php-ext-base AS php-ext-xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

###########################
# fpm base build
###########################

FROM php:8.3-fpm-alpine AS fpm-base
ARG TIMEZONE
# TODO get rid of git from the final image, we'll need to the compose install and stuff in a new image
RUN apk add --no-cache \
        bash \
        coreutils \
        freetype \
        git \
        haveged \
        icu \
        icu-data-full \
        libldap \
        libpng \
        libpq-dev \
        libzip \
        libxslt-dev \
        fcgi \
        npm \
        nodejs \
        tzdata \
        yarn && \
    touch /use_fpm && \
    sed -i "s/;ping.path/ping.path/g" /usr/local/etc/php-fpm.d/www.conf && \
    sed -i "s/;access.suppress_path\[\] = \/ping/access.suppress_path\[\] = \/ping/g" /usr/local/etc/php-fpm.d/www.conf

EXPOSE 9000

HEALTHCHECK --interval=20s --timeout=10s --retries=3 \
    CMD \
    SCRIPT_NAME=/ping \
    SCRIPT_FILENAME=/ping \
    REQUEST_METHOD=GET \
    cgi-fcgi -bind -connect 127.0.0.1:9000 || exit 1

###########################
# apache base build
###########################

FROM php:8.3-apache-bookworm AS apache-base
ARG TIMEZONE
# TODO get rid of git from the final image, we'll need to the compose install and stuff in a new image
RUN apt-get update && \
    apt-get install -y \
        bash \
        git \
        haveged \
        libicu72 \
        libldap-common \
        libpng16-16 \
        libzip4 \
        libxslt1.1 \
        libfreetype6 \
        libpq5 \
        npm \
        nodejs \
        unzip \
        yarnpkg && \
    ln -s /usr/bin/yarnpkg /usr/bin/yarn && \
    echo "Listen 8001" > /etc/apache2/ports.conf && \
    a2enmod rewrite && \
    touch /use_apache

COPY .docker/000-default.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 8001

HEALTHCHECK --interval=20s --timeout=10s --retries=3 \
    CMD curl -f http://127.0.0.1:8001/health || exit 1

###########################
# global base build
###########################

FROM ${BASE}-base AS php-base
ARG TIMEZONE

ENV TIMEZONE=${TIMEZONE}
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone && \
    # make composer home dir
    mkdir /composer
# copy composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# copy php extensions

# PHP extension xsl
COPY --from=php-ext-xsl /usr/local/etc/php/conf.d/docker-php-ext-xsl.ini /usr/local/etc/php/conf.d/docker-php-ext-xsl.ini
COPY --from=php-ext-xsl /usr/local/lib/php/extensions/no-debug-non-zts-20230831/xsl.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/xsl.so
# PHP extension pdo_pgsql
COPY --from=php-ext-pdo_pgsql /usr/local/etc/php/conf.d/docker-php-ext-pdo_pgsql.ini /usr/local/etc/php/conf.d/docker-php-ext-pdo_pgsql.ini
COPY --from=php-ext-pdo_pgsql /usr/local/etc/php/conf.d/docker-php-ext-pgsql.ini /usr/local/etc/php/conf.d/docker-php-ext-pgsql.ini
COPY --from=php-ext-pdo_pgsql /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo_pgsql.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo_pgsql.so
COPY --from=php-ext-pdo_pgsql /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pgsql.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pgsql.so
COPY --from=php-ext-pdo_pgsql /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/pdo.so
# PHP extension zip
COPY --from=php-ext-zip /usr/local/etc/php/conf.d/docker-php-ext-zip.ini /usr/local/etc/php/conf.d/docker-php-ext-zip.ini
COPY --from=php-ext-zip /usr/local/lib/php/extensions/no-debug-non-zts-20230831/zip.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/zip.so
# PHP extension ldap
COPY --from=php-ext-ldap /usr/local/etc/php/conf.d/docker-php-ext-ldap.ini /usr/local/etc/php/conf.d/docker-php-ext-ldap.ini
COPY --from=php-ext-ldap /usr/local/lib/php/extensions/no-debug-non-zts-20230831/ldap.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/ldap.so
# PHP extension gd
COPY --from=php-ext-gd /usr/local/etc/php/conf.d/docker-php-ext-gd.ini /usr/local/etc/php/conf.d/docker-php-ext-gd.ini
COPY --from=php-ext-gd /usr/local/lib/php/extensions/no-debug-non-zts-20230831/gd.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/gd.so
# PHP extension intl
COPY --from=php-ext-intl /usr/local/etc/php/conf.d/docker-php-ext-intl.ini /usr/local/etc/php/conf.d/docker-php-ext-intl.ini
COPY --from=php-ext-intl /usr/local/lib/php/extensions/no-debug-non-zts-20230831/intl.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/intl.so
# PHP extension opcache
COPY --from=php-ext-opcache /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini  /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

###########################
# fetch nrfrc-fixtures sources
###########################

FROM alpine:latest AS git-prod
ARG VERSION
ARG TIMEZONE
# the convention in the nrfrc-fixtures repository is: tags are always version numbers, branch names always start with a letter
# if the nrfrc-fixtures variable starts with a number (e.g. 2.24.0) we assume its a tag, otherwise its a branch
RUN [[ $VERSION =~ ^[0-9] ]] && export REF='tags' || export REF='heads' && \
    wget -O "/opt/nrfcfixtures.tar.gz" "https://github.com/tobybatch/nrfc-fixtures/archive/refs/${REF}/${VERSION}.tar.gz" && \
    tar -xpzf /opt/nrfcfixtures.tar.gz -C /opt/ && \
    mv /opt/nrfc-fixtures-${VERSION} /opt/nrfcfixtures

###########################
# global base build
###########################

FROM php-base AS base
ARG VERSION
ARG TIMEZONE

LABEL org.opencontainers.image.title="NRFC Fixtures" \
      org.opencontainers.image.description="NRFC Fixtures is a rugby club fixture manager application." \
      org.opencontainers.image.authors="Toby Batch <toby@nfn.org.uk>" \
      org.opencontainers.image.url="https://fixtures.norwichrugby.com/" \
      org.opencontainers.image.documentation="https://fixtures.norwichrugby.com/documentation/" \
      org.opencontainers.image.source="https://github.com/tobybatch/nrfc-fixtures" \
      org.opencontainers.image.version="${VERSION}" \
      org.opencontainers.image.vendor="Toby Batch" \
      org.opencontainers.image.licenses="AGPL-3.0"

ENV VERSION=${VERSION}
ENV TIMEZONE=${TIMEZONE}
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone && \
    mkdir -p /composer
# copy startup script & DB checking script
COPY .docker/dbtest.php /dbtest.php
COPY .docker/entrypoint.sh /entrypoint.sh
WORKDIR /opt/nrfcfixtures

ENV DATABASE_URL="mysql://app:app@127.0.0.1:3306/app?charset=utf8mb4&serverVersion=8.3"
ENV APP_SECRET=change_this_to_something_unique
# The default container name for nginx is nginx
ENV TRUSTED_PROXIES=nginx,localhost,127.0.0.1
ENV CORS_ALLOW_ORIGIN=nginx,localhost,127.0.0.1
ENV LOAD_FIXTURES="true"
ENV MAILER_FROM=no-reply@norwichrugby.com
ENV MESSENGER_TRANSPORT_DSN="doctrine://default?auto_setup=0"
ENV MAILER_DSN=""
ENV UX_MAP_DSN=leaflet://default
ENV ADMINPASS=""
ENV ADMINMAIL=""
ENV USER_ID=""
ENV GROUP_ID=""
# default values to configure composer behavior
ENV COMPOSER_MEMORY_LIMIT=-1
ENV COMPOSER_ALLOW_SUPERUSER=1

CMD [ "/entrypoint.sh" ]

###########################
# final builds
###########################

# development build
FROM base AS dev
# copy nrfcfixtures develop source
COPY --from=git-prod /opt/nrfcfixtures /opt/nrfcfixtures
COPY .docker /assets
# do the composer deps installation
RUN \
    export COMPOSER_HOME=/composer && \
    touch /opt/nrfcfixtures/.env && \
    cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini && \
    sed "s/128M/-1/g" /usr/local/etc/php/php.ini-development > /opt/nrfcfixtures/php-cli.ini && \
    sed -i "s/env php/env -S php -c \/opt\/nrfcfixtures\/php-cli.ini/g" /opt/nrfcfixtures/bin/console && \
    curl -sS https://get.symfony.com/cli/installer | bash && \
    rm -rf /opt/nrfcfixtures/var && \
    mv /root/.symfony5/bin/symfony /usr/local/bin/symfony
# Copy Xdebug files from the build stage
COPY --from=php-ext-xdebug /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY --from=php-ext-xdebug /usr/local/lib/php/extensions/no-debug-non-zts-20230831/xdebug.so /usr/local/lib/php/extensions/no-debug-non-zts-20230831/xdebug.so
ENV APP_ENV=dev
ENV DATABASE_URL=""

# the "prod" stage (production build) is configured as last stage in the file, as this is the default target in BuildKit
FROM base AS prod
# copy nrfcfixtures production source
COPY --from=git-prod /opt/nrfcfixtures /opt/nrfcfixtures
COPY .docker /assets
ENV APP_ENV=prod
WORKDIR /opt/nrfcfixtures
# do the composer deps installation
RUN export COMPOSER_HOME=/composer && \
    touch /opt/nrfcfixtures/.env && \
    composer --no-ansi install --working-dir=/opt/nrfcfixtures --no-dev --optimize-autoloader && \
    composer --no-ansi clearcache && \
    cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini && \
    sed -i "s/expose_php = On/expose_php = Off/g" /usr/local/etc/php/php.ini && \
    sed -i "s/;opcache.enable=1/opcache.enable=1/g" /usr/local/etc/php/php.ini && \
    sed -i "s/;opcache.memory_consumption=128/opcache.memory_consumption=256/g" /usr/local/etc/php/php.ini && \
    sed -i "s/;opcache.interned_strings_buffer=8/opcache.interned_strings_buffer=24/g" /usr/local/etc/php/php.ini && \
    sed -i "s/;opcache.max_accelerated_files=10000/opcache.max_accelerated_files=100000/g" /usr/local/etc/php/php.ini && \
    sed -i "s/opcache.validate_timestamps=1/opcache.validate_timestamps=0/g" /usr/local/etc/php/php.ini && \
    sed -i "s/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 604800/g" /usr/local/etc/php/php.ini && \
    mkdir -p /opt/nrfcfixtures/var/logs && chmod 777 /opt/nrfcfixtures/var/logs && \
    sed "s/128M/-1/g" /usr/local/etc/php/php.ini-development > /opt/nrfcfixtures/php-cli.ini && \
    yarn --cwd /opt/nrfcfixtures && \
    yarn --cwd /opt/nrfcfixtures build && \
    /opt/nrfcfixtures/bin/console nrfc:fixtures:version > /opt/nrfcfixtures/version.txt
ENV APP_ENV=prod
ENV DATABASE_URL=""

