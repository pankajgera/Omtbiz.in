#!/bin/sh
set -eu

if [ -n "${PASSPORT_KEYS_PATH:-}" ]; then
    install -d -o www-data -g www-data -m 700 "$PASSPORT_KEYS_PATH"
    install -o www-data -g www-data -m 600 storage/oauth-private.key "$PASSPORT_KEYS_PATH/oauth-private.key"
    install -o www-data -g www-data -m 600 storage/oauth-public.key "$PASSPORT_KEYS_PATH/oauth-public.key"
fi

exec docker-php-entrypoint "$@"
