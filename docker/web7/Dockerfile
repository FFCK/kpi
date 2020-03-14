FROM newdeveloper/apache-php

COPY 000-default.conf /etc/apache2/sites-enabled/000-default.conf

RUN apt-get update && apt-get install php-mbstring -y \
    && a2enmod headers \
    && a2enmod expires \
    && service apache2 restart
