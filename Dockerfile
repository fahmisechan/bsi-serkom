# Gunakan image resmi PHP dengan Apache
FROM php:8.2-apache

# Salin semua file dari direktori lokal ke direktori Apache
COPY . /var/www/html/

# Aktifkan mod_rewrite jika perlu (opsional, tergantung kebutuhan .htaccess)
RUN a2enmod rewrite

# Buka port default Apache
EXPOSE 80
