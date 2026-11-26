#!/usr/bin/env bash
# Menyiapkan VPS Ubuntu 24.04 baru: PHP 8.3, Nginx, Composer, Node.js 22, Certbot.
# Cukup dijalankan SEKALI per VPS, sebagai root:
#   bash siapkan-vps.sh
set -euo pipefail

if [ "$(id -u)" -ne 0 ]; then
  echo "Jalankan sebagai root (atau pakai sudo)." >&2
  exit 1
fi

# Swap 1 GB untuk VPS kecil, supaya build tampilan (npm) tidak kehabisan memori
if [ "$(swapon --show | wc -l)" -eq 0 ] && [ "$(awk '/MemTotal/ {print $2}' /proc/meminfo)" -lt 2000000 ]; then
  fallocate -l 1G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
  echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

export DEBIAN_FRONTEND=noninteractive
apt-get update
PAKET="git unzip curl nginx sqlite3 certbot python3-certbot-nginx \
  php8.3-fpm php8.3-cli php8.3-sqlite3 php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl"

# Beberapa VPS tidak punya IPv6. Konfigurasi bawaan Nginx mendengarkan [::]:80,
# sehingga Nginx gagal start dan apt berhenti dengan error. Hapus baris IPv6 itu
# lalu selesaikan instalasinya.
if ! apt-get install -y $PAKET; then
  if [ -f /etc/nginx/sites-available/default ]; then
    sed -i '/listen \[::\]/d' /etc/nginx/sites-available/default
  fi
  dpkg --configure -a
  apt-get install -y $PAKET
fi

# Composer
if ! command -v composer >/dev/null; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# Node.js 22 (untuk build tampilan Vue)
if ! command -v node >/dev/null || [ "$(node -v | cut -d. -f1 | tr -d v)" -lt 20 ]; then
  curl -fsSL https://deb.nodesource.com/setup_22.x | bash -
  apt-get install -y nodejs
fi

# Firewall sederhana: SSH + web
if command -v ufw >/dev/null; then
  ufw allow OpenSSH >/dev/null
  ufw allow 'Nginx Full' >/dev/null
  ufw --force enable >/dev/null
fi

rm -f /etc/nginx/sites-enabled/default
systemctl enable nginx >/dev/null 2>&1 || true
systemctl restart nginx

echo
echo "VPS siap. Lanjutkan dengan:"
echo "  bash pasang-demo.sh salon salon.domainkamu.com"
