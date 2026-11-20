#!/bin/sh
set -eu

cd "$(dirname "$0")/.."
test -f public/build/manifest.json || { echo "Run npm run build first" >&2; exit 1; }
test -f vendor/autoload.php || { echo "Run composer install first" >&2; exit 1; }

target=.wasmer/app
rm -rf "$target"
mkdir -p "$target/database" "$target/bootstrap/cache" \
    "$target/storage/framework/cache/data" "$target/storage/framework/sessions" \
    "$target/storage/framework/views" "$target/storage/logs" "$target/storage/app/private"

for directory in app config resources routes vendor; do
    cp -R "$directory" "$target/$directory"
done
cp bootstrap/app.php bootstrap/providers.php "$target/bootstrap/"
cp -R database/migrations "$target/database/migrations"
cp -R public "$target/public"
rm -f "$target/public/hot"
if [ -L "$target/public/storage" ]; then
    rm "$target/public/storage"
elif [ -d "$target/public/storage" ]; then
    rm -rf "$target/public/storage"
fi
cp artisan composer.json composer.lock "$target/"
cp docker/wasmer-router.php "$target/wasmer-router.php"
find "$target" -name .DS_Store -delete
echo "Wasmer package prepared (no .env, local database or user uploads)."
