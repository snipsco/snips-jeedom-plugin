#!/usr/bin/env bash

set -e

OPT=$1
NEW_VERSION=$2
TIME=$(date "+%b-%d-%Y")

if [ -z $OPT ]; then
    echo "Usage: $0 [-s, -b] CURRENT_VERSION"
    exit 1
fi

if [ -z $NEW_VERSION ]; then
    echo "Usage: $0 [-s, -b] CURRENT_VERSION"
    exit 1
fi

SPLIT_VERSION=( ${NEW_VERSION//./ } )
if [ ${#SPLIT_VERSION[@]} -ne 3 ]; then
  echo "Version number is invalid (must be of the form x.y.z)"
  exit 1
fi

if [ $1 == "-s" ]; then
    let SPLIT_VERSION[2]+=1
fi

if [ $1 == "-b" ]; then
    let SPLIT_VERSION[1]+=1
fi

NEXT_NEW_VERSION="${SPLIT_VERSION[0]}.${SPLIT_VERSION[1]}.${SPLIT_VERSION[2]}"

perl -p -i -e "s/version-.*-brightgreen.svg/version-$NEXT_NEW_VERSION-brightgreen.svg/g" ../README.md
perl -p -i -e "s/<legend>\{\{Snips Voice Assistant\}\} - .*<\/legend>/<legend>\{\{Snips Voice Assistant\}\} - $NEXT_NEW_VERSION<\/legend>/g" ../desktop/php/snips.php
perl -p -i -e "s/\"version\" : \".*\",/\"version\" : \"$NEXT_NEW_VERSION\",/g" ../plugin_info/info.json

echo -e "New version bumped to \033[1;32;32m$NEXT_NEW_VERSION\033[m"