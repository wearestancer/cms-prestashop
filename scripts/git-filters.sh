#! /bin/sh

set -eu

scripts_dir=$(dirname $(realpath "$0"))
main_dir=$(dirname "$scripts_dir")


find "$scripts_dir" -name 'smudge.sh' \
  | awk -F/ '{ name = NF - 1; print "git config filter." $name ".smudge \47./scripts/" $name "/" $NF " %f\47" }' | sh

find "$scripts_dir" -name 'clean.sh' \
  | awk -F/ '{ name = NF - 1; print "git config filter." $name ".clean ./scripts/" $name "/" $NF }' | sh
