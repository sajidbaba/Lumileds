#!/bin/sh
set -e

yarn install
./node_modules/.bin/encore dev --watch

exec "$@"
