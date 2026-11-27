#!/bin/sh
set -e

echo "🚀 Memulai SIMAU Production Container..."

# Tunggu database siap jika DB_HOST diset
if [ -n "$DB_HOST" ]; then
    echo "Menunggu koneksi database di $DB_HOST:$DB_PORT..."
    until nc -z -v -w30 "$DB_HOST" "${DB_PORT:-3306}" 2>/dev/null; do
        echo "Database belum siap, mencoba kembali dalam 2 detik..."
        sleep 2
    done
    echo "✅ Database terhubung!"
fi

# Generate APP_KEY jika belum ada
if [ -z "$APP_KEY" ]; then
    echo "Generating Application Key..."
    php artisan key:generate --force
fi

# Jalankan migrasi database
echo "Menjalankan migrasi database..."
php artisan migrate --force

# Optimasi cache Laravel untuk production
echo "Mengoptimalkan cache konfigurasi dan rute..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Pastikan permission folder storage dan bootstrap cache benar
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Sesuaikan port Apache dengan env PORT dari Railway/platform (default 80)
APP_PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${APP_PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:${APP_PORT}>/" /etc/apache2/sites-available/000-default.conf

echo "🌟 SIMAU siap beroperasi di port ${APP_PORT}!"
exec "$@"
