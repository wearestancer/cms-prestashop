#! /bin/sh

cat <&0 \
  | sed -E "s/^header\('Last-Modified.+\)/header('Last-Modified: \$[current-date]')/"
