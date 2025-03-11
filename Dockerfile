FROM php:8.4-apache
ARG SSH_PASSWORD
# Copy application and configuration files before executing RUN commands
COPY . /var/www/html/
COPY .tools/deployment/default.conf /etc/apache2/sites-available/000-default.conf
COPY .tools/deployment/php.ini /usr/local/etc/php/php.ini
COPY .tools/deployment/sshd_config /etc/ssh/sshd_config
COPY .tools/deployment/entrypoint.sh /usr/local/bin/entrypoint.sh

# Install required packages, configure PHP and services
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
       curl \
       dialog \
       libgmp-dev \
       libicu-dev \
       libssl-dev \
       openssh-server \
       sed \
       unzip \
       vim \
    && echo "root:${SSH_PASSWORD}" | chpasswd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql mysqli \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo "ServerSignature Off" >> /etc/apache2/apache2.conf \
    && echo "ServerTokens Prod" >> /etc/apache2/apache2.conf \
    && touch /var/log/php_errors.log \
    && chown www-data:www-data /var/log/php_errors.log \
    && chmod 644 /var/log/php_errors.log \
    && chown www-data:www-data /var/tmp \
    && chown www-data:www-data /var/www/html/.tools \
    && chown www-data:www-data /var/www/html/public/assets/images/profile \
    && chmod 755 /var/www/html \
    && chmod 1733 /var/tmp \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    && if [ -f vendor/erusev/parsedown/Parsedown.php ]; then \
        sed -i "s/\$class = 'language-'.\$language;/\$class = 'language-'.\$language . ' c0py';/g" vendor/erusev/parsedown/Parsedown.php; \
        sed -i "s/(\\\$Line, array \\\$Block = null)/(\\\$Line, array|null \\\$Block = null)/g" vendor/erusev/parsedown/Parsedown.php; \
    else \
        echo "File vendor/erusev/parsedown/Parsedown.php not found"; \
    fi \
    && a2enmod rewrite \
    && a2enmod headers \
    && mkdir /var/run/sshd \
    && chmod 600 /etc/ssh/sshd_config \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && rm -rf /var/lib/apt/lists/* \
    && service apache2 restart

EXPOSE 2222 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]