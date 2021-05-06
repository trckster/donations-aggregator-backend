FROM nginx/unit:1.23.0-php8.0

ARG user
ARG uid

RUN apt update && apt install -y git zip unzip

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user && \
    chown -R $user:$user /var/www

WORKDIR /var/www

USER $user

COPY . .

CMD php artisan serve
