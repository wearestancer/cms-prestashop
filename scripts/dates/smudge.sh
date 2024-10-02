#! /bin/bash

current_date=`date +'%a, %d %b %Y %H:%M:%S %Z'`

# output
cat <&0 \
  | sed "s/\$\[current-date\]/${current_date}/g"
