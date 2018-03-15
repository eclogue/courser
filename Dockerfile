FROM php:7.0-cli
MAINTAINER bugbear <mulberry10th@gmail.com>
RUN apt-get update && apt-get install -y curl git wget
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN rm -f composer-setup.php
RUN mv composer.phar /usr/local/bin/composer
ENV APP_DIR=/usr/local/webapp
WORKDIR $APP_DIR
#RUN wget https://github.com/swoole/swoole-src/archive/v1.9.13.tar.gz
#RUN tar -zxvf v1.9.13.tar.gz
#WORKDIR  $APP_DIR/swoole-src-1.9.13
#RUN phpize
#RUN ./configure
#RUN make && make install
#RUN echo "extension=swoole.so" >> /etc/php/7.0/cli/conf.d/swoole.ini
WORKDIR $APP_DIR
RUN git clone https://github.com/eclogue/courser
WORKDIR $APP_DIR/courser
RUN git checkout develop && git pull origin develop
RUN composer install --prefer-dist










