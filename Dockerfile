FROM nginx/unit:1.23.0-php8.0

ARG user
ARG uid

RUN apt-get update
RUN apt-get install -y git zip unzip postgresql libpq-dev sudo && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user && \
    chown -R $user:$user /var/www

WORKDIR /var/www

USER $user

COPY composer.* artisan ./

RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --no-scripts --optimize-autoloader; \
    composer clearcache

ADD --chown=$user:$uid . .

RUN composer dump-autoload -o

USER root

RUN unitd --no-daemon --control unix:/var/run/control.unit.sock & \
    sleep 1; \
    curl -XPUT --data-binary @unit.json --unix-socket /var/run/control.unit.sock http://localhost/config;
