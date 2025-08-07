FROM php:8.4-apache

# Copy application and configuration files before executing RUN commands
COPY . /var/www/html/
COPY .tools/deployment/default.conf /etc/apache2/sites-available/000-default.conf
COPY .tools/deployment/php.ini /usr/local/etc/php/php.ini
COPY .tools/deployment/sshd_config /etc/ssh/sshd_config
COPY .tools/deployment/entrypoint.sh /usr/local/bin/entrypoint.sh

# Install required packages and configure PHP
RUN --mount=type=cache,target=/var/cache/apt \
    apt-get update \
    && apt-get install -y --no-install-recommends \
    curl \
    dialog \
    libgmp-dev \
    libicu-dev \
    libssl-dev \
    openssh-server \
    iputils-ping \
    dnsutils \
    redis-tools \
    net-tools \
    telnet \
    curl \
    wget \
    sed \
    unzip \
    vim \
    && rm -rf /var/lib/apt/lists/* \
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
    && a2enmod rewrite \
    && a2enmod headers

# SSH-specific and final setup
RUN mkdir /var/run/sshd \
    && chmod 600 /etc/ssh/sshd_config \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && rm -rf /var/lib/apt/lists/* \
    \
    # Enable additional access log for logrotate
    && touch /var/log/apache2/custom_access.log \
    && chown www-data:adm /var/log/apache2/custom_access.log \
    && chmod 644 /var/log/apache2/custom_access.log \
    && chown www-data:www-data /var/log/apache2/custom_access.log \
    && echo "/var/log/apache2/custom_access.log {" > /etc/logrotate.d/apache2-custom \
    && echo "    daily" >> /etc/logrotate.d/apache2-custom \
    && echo "    rotate 7" >> /etc/logrotate.d/apache2-custom \
    && echo "    compress" >> /etc/logrotate.d/apache2-custom \
    && echo "    missingok" >> /etc/logrotate.d/apache2-custom \
    && echo "    notifempty" >> /etc/logrotate.d/apache2-custom \
    && echo "    create 0640 www-data adm" >> /etc/logrotate.d/apache2-custom \
    && echo "}" >> /etc/logrotate.d/apache2-custom \
    && service apache2 restart \
    && rm -rf /var/www/html/.tools/deployment \
    && rm -rf /var/www/html/.dockerignore

EXPOSE 2222 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]