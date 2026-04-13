FROM php:8.2-apache

# Установка PDO MySQL и других расширений
RUN docker-php-ext-install pdo pdo_mysql

# Включение необходимых модулей Apache
RUN a2enmod rewrite headers expires

# Копирование файлов проекта
COPY . /var/www/html/

# Установка прав на папки
RUN chown -R www-data:www-data /var/www/html/config
RUN chown -R www-data:www-data /var/www/html/logs
RUN chmod -R 755 /var/www/html/config
RUN chmod -R 755 /var/www/html/logs

# Настройка Apache
RUN echo "ServerName localhost:80" >> /etc/apache2/apache2.conf

# Разрешение .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Установка рабочей директории
WORKDIR /var/www/html/

# Открытие порта
EXPOSE 80
