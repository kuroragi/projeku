#!/usr/bin/env bash
# Dijalankan di server: bash ~/kds-pm/deploy/deploy.sh
set -euo pipefail

APP="${APP:-$HOME/kds-pm}"
DOCROOT="${DOCROOT:-$HOME/domains/<domain-induk>/public_html/projeku}"
PHP="${PHP:-/usr/bin/php}"            # ganti dengan biner PHP 8.4 dari `php -v`
COMPOSER="${COMPOSER:-$(command -v composer)}"
DB_NAME="${DB_NAME:-<nama-db>}"
STAMP="$(date +%F-%H%M)"
BACKUP="$APP/storage/backups/db-$STAMP.sql.gz"
STEP="awal"

cd "$APP"

"$PHP" -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' \
  || { echo "PHP di bawah 8.4, berhenti"; exit 1; }

mkdir -p storage/backups

if [ -n "${DB_NAME:-}" ] && [ "$DB_NAME" != "<nama-db>" ]; then
    echo "Membuat cadangan basis data sebelum migrasi..."
    mysqldump --single-transaction --no-tablespaces "$DB_NAME" | gzip > "$BACKUP"
    test -s "$BACKUP" || { echo "Cadangan kosong, berhenti"; exit 1; }
fi

"$PHP" artisan down --retry=60 || true

on_fail() {
  echo "GAGAL di langkah: $STEP. Aplikasi dibiarkan dalam mode pemeliharaan."
  if [ -f "$BACKUP" ]; then
    echo "Bila migrasi sempat jalan, pulihkan: gunzip < $BACKUP | mysql $DB_NAME"
  fi
}
trap on_fail ERR

# Satu perintah per baris: kegagalan di dalam rantai && tidak menghentikan skrip.
STEP="git pull";         git pull --ff-only
STEP="composer install"; "$PHP" "$COMPOSER" install --no-dev --optimize-autoloader --no-interaction
STEP="migrate";          "$PHP" artisan migrate --force
STEP="cache config";     "$PHP" artisan config:cache
STEP="cache route";      "$PHP" artisan route:cache
STEP="cache view";       "$PHP" artisan view:cache
STEP="cache event";      "$PHP" artisan event:cache

if [ -d "$DOCROOT" ]; then
    STEP="salin public";     rsync -a --delete --exclude='index.php' --exclude='.well-known' public/ "$DOCROOT/"
    STEP="salin index";      cp deploy/index.hostinger.php "$DOCROOT/index.php"
fi

"$PHP" artisan up

# Bersihkan cadangan lama, sisakan 14 berkas terbaru
ls -1t storage/backups/db-*.sql.gz 2>/dev/null | tail -n +15 | xargs -r rm -- || true

echo "Deploy Selesai: $STAMP"

