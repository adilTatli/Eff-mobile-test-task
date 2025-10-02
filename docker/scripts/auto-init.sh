#!/bin/sh
set -e

echo "set permissions.."
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache || true

if [ ! -d vendor ]; then
    echo "composer install.."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
    echo "artisan key:generate.."
    php artisan key:generate --force
fi

: ${DB_HOST:=db}
: ${DB_PORT:=3306}
: ${DB_DATABASE:=eff_mobile}
: ${DB_USERNAME:=user}
: ${DB_PASSWORD:=12345678}

echo "⏳ [auto-init] Wait MySQL ${DB_HOST}:${DB_PORT} (db=${DB_DATABASE})"
until php -r '
$h=getenv("DB_HOST"); $p=getenv("DB_PORT"); $u=getenv("DB_USERNAME"); $pw=getenv("DB_PASSWORD"); $db=getenv("DB_DATABASE");
$dsn="mysql:host={$h};port={$p};dbname={$db}";
try { new PDO($dsn,$u,$pw,[PDO::ATTR_TIMEOUT=>2]); exit(0); } catch(Exception $e){ exit(1); }
'; do
  sleep 2
done

echo "artisan migrate.."
php artisan migrate --force

echo "artisan db:seed.."
php artisan db:seed --force

if [ ! -f config/l5-swagger.php ]; then
  echo "publish l5-swagger config"
  php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider" --force || true
fi

mkdir -p storage/api-docs
chown -R www-data:www-data storage || true
chmod -R ug+rw storage || true

echo "generate swagger"
php artisan l5-swagger:generate

php artisan optimize:clear || true


echo "php-fpm.."
exec php-fpm
