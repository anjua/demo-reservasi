#!/usr/bin/env bash
# Memasang (atau memperbarui) satu versi demo di VPS.
#
# Pemakaian (sebagai root):
#   bash pasang-demo.sh <nama> <domain> [preset] [email-untuk-ssl]
#
# Contoh:
#   bash pasang-demo.sh salon   salon.domainkamu.com   salon   kamu@email.com
#   bash pasang-demo.sh klinik  klinik.domainkamu.com  klinik  kamu@email.com
#   bash pasang-demo.sh bengkel bengkel.domainkamu.com bengkel kamu@email.com
#
# <preset> = nama file di deploy/presets/ (tanpa .env). Default sama dengan <nama>.
# Menjalankan ulang perintah yang sama = update ke kode terbaru di GitHub.
set -euo pipefail

NAMA="${1:?Isi nama demo, mis. salon}"
DOMAIN="${2:?Isi domain, mis. salon.domainkamu.com}"
PRESET="${3:-$NAMA}"
EMAIL="${4:-}"
REPO="${REPO:-https://github.com/anjua/demo-reservasi.git}"
DIR="/var/www/$NAMA"
PHP_SOCK="${PHP_SOCK:-/run/php/php8.3-fpm.sock}"

if [ "$(id -u)" -ne 0 ]; then
  echo "Jalankan sebagai root (atau pakai sudo)." >&2
  exit 1
fi

echo "==> Mengambil kode ke $DIR"
if [ -d "$DIR/.git" ]; then
  git -C "$DIR" pull --ff-only
else
  git clone "$REPO" "$DIR"
fi
cd "$DIR"

PRESET_FILE="deploy/presets/$PRESET.env"
[ -f "$PRESET_FILE" ] || { echo "Preset $PRESET_FILE tidak ditemukan." >&2; exit 1; }

echo "==> Install dependency"
composer install --no-dev --optimize-autoloader --no-interaction
npm ci --no-audit --no-fund
npm run build

# set_env KUNCI NILAI -> ganti/isi baris KUNCI=NILAI di .env
set_env() {
  local key="$1" val="$2" esc
  esc=$(printf '%s' "$val" | sed 's/[&|\\]/\\&/g')
  if grep -q "^$key=" .env; then
    sed -i "s|^$key=.*|$key=$esc|" .env
  else
    printf '%s=%s\n' "$key" "$val" >> .env
  fi
}

echo "==> Menyiapkan .env (preset: $PRESET)"
if [ ! -f .env ]; then
  cp .env.example .env
  php artisan key:generate --force
fi
set_env APP_ENV production
set_env APP_DEBUG false
set_env APP_URL "https://$DOMAIN"
set_env DEMO_MODE true
set_env DB_CONNECTION sqlite
set_env DB_DATABASE "$DIR/database/database.sqlite"
while IFS= read -r line || [ -n "$line" ]; do
  case "$line" in ''|\#*) continue ;; esac
  set_env "${line%%=*}" "${line#*=}"
done < "$PRESET_FILE"

echo "==> Database"
# Isi data contoh kalau database belum ada atau masih kosong (mis. pemasangan
# sebelumnya gagal di tengah jalan). Kalau sudah berisi, cukup migrate.
BARU=0
[ -s database/database.sqlite ] || BARU=1
touch database/database.sqlite
php artisan config:clear
if [ "$BARU" = 1 ]; then
  php artisan migrate:fresh --seed --force
else
  php artisan migrate --force
fi
php artisan config:cache
php artisan route:cache
php artisan view:cache

chown -R www-data:www-data storage bootstrap/cache database

echo "==> Nginx untuk $DOMAIN"
cat > "/etc/nginx/sites-available/$NAMA" <<NGINX
server {
    listen 80;
    server_name $DOMAIN;
    root $DIR/public;
    index index.php;
    charset utf-8;
    client_max_body_size 10M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:$PHP_SOCK;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
NGINX
ln -sf "/etc/nginx/sites-available/$NAMA" "/etc/nginx/sites-enabled/$NAMA"
nginx -t
systemctl reload nginx

echo "==> Jadwal reset data demo (cron)"
echo "* * * * * www-data cd $DIR && php artisan schedule:run >> /dev/null 2>&1" > "/etc/cron.d/demo-$NAMA"

if [ -n "$EMAIL" ] && [ ! -d "/etc/letsencrypt/live/$DOMAIN" ]; then
  echo "==> SSL (https) dari Let's Encrypt"
  certbot --nginx -d "$DOMAIN" --non-interactive --agree-tos -m "$EMAIL" --redirect \
    || echo "!! SSL gagal. Pastikan DNS $DOMAIN sudah mengarah ke IP VPS, lalu jalankan ulang skrip ini."
fi

echo
echo "Selesai! Buka: http${EMAIL:+s}://$DOMAIN"
echo "Admin: http${EMAIL:+s}://$DOMAIN/admin  (admin@demo.test / password)"
