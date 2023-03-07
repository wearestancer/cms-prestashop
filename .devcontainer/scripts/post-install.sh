#!/bin/sh

echo "\n* Install Stancer module...";
runuser -g www-data -u www-data -- php -d memory_limit=-1 bin/console prestashop:module install stancer

echo "\n* Clearing cache...";
runuser -g www-data -u www-data -- php -d memory_limit=-1 bin/console cache:clear
