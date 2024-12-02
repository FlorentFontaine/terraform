FROM gitlab.cicd.biz:5500/shared/php-server:7.4

ENV APACHE_SSL=1
ENV PHP_MEMORY_LIMIT=2048M
ENV PHP_POST_MAX_SIZE=20M
ENV PHP_MAX_INPUT_VARS=2000

ARG LIBRARIES_ACCESS_TOKEN

# Copy application files
COPY src/ /app

RUN composer config gitlab-token.gitlab.cicd.biz $LIBRARIES_ACCESS_TOKEN \
    && composer install \
    && composer dump-autoload -o \
    && rm auth.json
