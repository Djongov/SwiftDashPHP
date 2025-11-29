FROM php:8.5.0-apache

# Copy application and configuration files before executing RUN commands
COPY . /var/www/html/
COPY .tools/deployment/default.conf /etc/apache2/sites-available/000-default.conf
COPY .tools/deployment/php.ini-development /usr/local/etc/php/php.ini
COPY .tools/deployment/sshd_config /etc/ssh/sshd_config
COPY .tools/deployment/entrypoint.sh /usr/local/bin/entrypoint.sh

# Single RUN layer with organized sections for better caching and smaller image size
RUN --mount=type=cache,target=/var/cache/apt \
    # ========================================
    # Package Installation & System Updates
    # ========================================
    apt-get update \
    && apt-get install -y --no-install-recommends \
    # Core utilities
    curl \
    dialog \
    dnsutils \
    iputils-ping \
    libgmp-dev \
    libicu-dev \
    libssl-dev \
    net-tools \
    openssh-server \
    redis-tools \
    sed \
    telnet \
    unzip \
    vim \
    wget \
    && rm -rf /var/lib/apt/lists/* \
    \
    # ========================================
    # PHP Extensions Configuration
    # ========================================
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) intl pdo_mysql mysqli \
    \
    # ========================================
    # Apache Configuration
    # ========================================
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
    && echo "ServerSignature Off" >> /etc/apache2/apache2.conf \
    && echo "ServerTokens Prod" >> /etc/apache2/apache2.conf \
    && a2enmod rewrite \
    && a2enmod headers \
    \
    # ========================================
    # SSH Server Configuration
    # ========================================
    && mkdir -p /var/run/sshd \
    && chmod 600 /etc/ssh/sshd_config \
    \
    # ========================================
    # Composer Installation
    # ========================================
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chmod +x /usr/local/bin/composer \
    && composer install --no-dev --optimize-autoloader --no-interaction \
    \
    # ========================================
    # File Permissions & Directory Setup
    # ========================================
    && chmod +x /usr/local/bin/entrypoint.sh \
    && touch /var/log/php_errors.log \
    && chown www-data:www-data /var/log/php_errors.log \
    && chmod 644 /var/log/php_errors.log \
    && chown www-data:www-data /var/tmp \
    && chown www-data:www-data /var/www/html/.tools \
    && chown www-data:www-data /var/www/html/public/assets/images/profile \
    && chmod 755 /var/www/html \
    && chmod 1733 /var/tmp \
    # ========================================
    # Apache Custom Log Setup & Logrotate
    # ========================================
    && touch /var/log/apache2/custom_access.log \
    && chown www-data:adm /var/log/apache2/custom_access.log \
    && chmod 644 /var/log/apache2/custom_access.log \
    && echo "/var/log/apache2/custom_access.log {" > /etc/logrotate.d/apache2-custom \
    && echo "    daily" >> /etc/logrotate.d/apache2-custom \
    && echo "    rotate 7" >> /etc/logrotate.d/apache2-custom \
    && echo "    compress" >> /etc/logrotate.d/apache2-custom \
    && echo "    missingok" >> /etc/logrotate.d/apache2-custom \
    && echo "    notifempty" >> /etc/logrotate.d/apache2-custom \
    && echo "    create 0640 www-data adm" >> /etc/logrotate.d/apache2-custom \
    && echo "}" >> /etc/logrotate.d/apache2-custom \
    && echo "export ACCESS_LOG='/var/log/apache2/custom_access.log'" >> /etc/apache2/envvars \
    \
    # ========================================
    # Cleanup
    # ========================================
    && rm -rf /var/www/html/.tools/deployment \
    && rm -rf /var/www/html/.dockerignore

EXPOSE 2222 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]