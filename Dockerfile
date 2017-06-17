FROM ubuntu:16.10
MAINTAINER bugbear <mulberry10th@gmail.com>
RUN apt-get update && apt-get install -my \
  curl \
  git \
  wget \
  php7.0-dev \
  php7.0-mbstring \
  composer \
  php-xdebug
ENV APP_DIR=/usr/local/webapp
WORKDIR $APP_DIR
RUN wget https://github.com/swoole/swoole-src/archive/v1.9.13.tar.gz
RUN tar -zxvf v1.9.13.tar.gz
WORKDIR  $APP_DIR/swoole-src-1.9.13
RUN phpize
RUN ./configure
RUN make && make install
RUN echo "extension=swoole.so" >> /etc/php/7.0/cli/conf.d/swoole.ini
WORKDIR $APP_DIR
RUN git clone https://github.com/racecourse/courser
WORKDIR $APP_DIR/courser
RUN composer install -vvv










