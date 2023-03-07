#! /bin/sh

openssl req -config /etc/ssl/localhost.cnf -new -x509 -sha256 -newkey rsa:4096 -nodes -days 30 -keyout /etc/apache2/ssl/localhost.key -out /etc/apache2/ssl/localhost.crt
