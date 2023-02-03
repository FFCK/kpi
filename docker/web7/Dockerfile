FROM newdeveloper/apache-php:24-04-2020

COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

COPY php.ini /etc/php/7.2/apache2/php.ini
COPY msmtprc /etc/msmtprc
RUN chmod 777 /etc/msmtprc
RUN touch /var/log/msmtp.log
RUN chmod 777 /var/log/msmtp.log

RUN apt-get update && DEBIAN_FRONTEND=noninteractive apt-get install php-mbstring mailutils msmtp -y \
    && a2enmod headers \
    && a2enmod expires \
    && a2enmod rewrite \
    && service apache2 restart
