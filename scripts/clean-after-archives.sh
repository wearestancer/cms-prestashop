#! /usr/bin/bash

set -eu

current_dir=$(dirname $(realpath $0))
search_dir=$(dirname "$current_dir")

# We ignore those path for performance
files=$(find -name "*.php.old" -not -path "*/vendor/*" -not -path "*/node_module/*")
# We get our old unchanged file and erase the new one.
for file in $files; do
  mv -f "$file" ${file/%.php.old/.php}
done

# We keep sure that our vendors is still followed by git
touch "${search_dir}/vendors/.gitkeep"
