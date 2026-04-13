FROM php:8.2-apache

# Включение необходимых модулей Apache
RUN a2enmod rewrite headers expires

# Копирование файлов проекта
COPY . /var/www/html/

# Установка рабочей директории
WORKDIR /var/www/html/

# Настройка Apache
RUN echo "ServerName localhost:80" >> /etc/apache2/apache2.conf

# Разрешение .htaccess
RUN sed -i 's/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# Открытие порта
EXPOSE 80
