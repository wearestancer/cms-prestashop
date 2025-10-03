#! /usr/bin/bash

set -eu

scoper_version="0.18.7" # Last version to date running on PHP 8.1

# Exit now if the command exists
exists=$(which scoper &>/dev/null; echo $?)

if [ "$exists" = 0 ]; then
  exit 0;
fi

curl -Lso scoper.phar "https://github.com/humbug/php-scoper/releases/download/${scoper_version}/php-scoper.phar"
curl -Lso scoper.phar.asc "https://github.com/humbug/php-scoper/releases/download/${scoper_version}/php-scoper.phar.asc"

gpg --keyserver hkps://keys.openpgp.org --recv-keys 74A754C9778AA03AA451D1C1A000F927D67184EE

gpg --verify scoper.phar.asc scoper.phar

rm scoper.phar.asc

mv scoper.phar /usr/bin/scoper
chmod +x /usr/bin/scoper
