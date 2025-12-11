#! /usr/bin/bash

set -eu

current_dir=$(dirname $(realpath $0))
search_dir=$(dirname "$current_dir")
# We find all the files with a dependancy to PSR.
files=$(grep -REl '^use Psr' "$search_dir/vendor/stancer")

# We create a new file for our archive with the dependency scoped
for file in $files; do
  sed -i'.old' -re 's/use Psr\b/use Stancer\\Scoped\\Isolated\\Psr/g' "$file"
done

rm -rf vendor/psr
mv build/* vendor
rm -rf build
