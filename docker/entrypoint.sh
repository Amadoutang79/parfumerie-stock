#!/bin/sh
set -eu

echo "Démarrage de Parfumerie Stock..."

case "${PORT:-10000}" in
    ''|*[!0-9]*)
        echo "PORT doit être un numéro valide." >&2
        exit 1
        ;;
esac

sed -ri \
    -e "s/Listen [0-9]+/Listen ${PORT}/" \
    /etc/apache2/ports.conf
sed -ri \
    -e "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" \
    /etc/apache2/sites-available/000-default.conf

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link --force

exec apache2-foreground
