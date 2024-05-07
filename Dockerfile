FROM php:8.2-apache

# Copy script to generate .env file
COPY generate-env-file.sh /usr/local/bin/

# Make the script executable
RUN chmod +x /usr/local/bin/generate-env-file.sh

# Generate .env file
RUN /usr/local/bin/generate-env-file.sh

# Install necessary packages
RUN apt-get update && apt-get install -y \
    git \
    libpq-dev \
    libzip-dev \
    libjpeg-dev \
    libpng-dev \
    libonig-dev \
    && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy composer.json and composer.lock
COPY composer.json composer.lock ./

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Install PHP extensions
RUN docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    zip \
    mbstring \
    gd

# Set the Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Enable Apache modules
RUN a2enmod rewrite setenvif
RUN a2disconf other-vhosts-access-log

# Set Composer environment variables
ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /tmp
ENV PATH "$PATH:/root/.composer/vendor/bin"

# Install application dependencies
RUN composer install 

# Copy the rest of the application code
COPY . .

RUN composer dump-autoload
# Run Composer scripts

# Copy .env file into the container
COPY .env /var/www/html/.env

# Change the permissions of the .env file
RUN chmod 644 /var/www/html/.env

# Add index.php to the default document root
COPY ./public/index.php /var/www/html/public/

# Expose ports
EXPOSE 80
EXPOSE 443

# Start Apache in the foreground
CMD ["apache2ctl", "-D", "FOREGROUND"]
