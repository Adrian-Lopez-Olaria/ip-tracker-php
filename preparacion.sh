#!/bin/bash
set -euo pipefail

WWW_DIR="/var/www/html"
LOG_FILE="$WWW_DIR/ips.txt"

echo
echo "==============================================="
echo "SCRIPT: Preparar webroot para index.php"
echo "==============================================="

read -p "¿Confirmas que quieres limpiar /var/www/html? (YES para continuar): " CONF
if [ "$CONF" != "YES" ]; then
  echo "Abortado por el usuario."
  exit 1
fi

# 1) Backup y limpieza de /var/www/html
BACKUP_ROOT="/root/www_backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_ROOT"
if [ -d "$WWW_DIR" ]; then
    echo "[*] Backup de /var/www/html -> $BACKUP_ROOT/www_html.tar.gz"
    sudo tar -C /var/www -czf "$BACKUP_ROOT/www_html.tar.gz" "$(basename "$WWW_DIR")"
fi

echo "[*] Limpiando /var/www/html..."
sudo rm -rf "${WWW_DIR:?}/"* "${WWW_DIR:?}/".[!.]* 2>/dev/null || true
sudo mkdir -p "$WWW_DIR"

# 2) Crear ips.txt en /var/www/html
touch "$LOG_FILE"
sudo chown www-data:www-data "$LOG_FILE"
sudo chmod 664 "$LOG_FILE"

# 3) Instalar Apache + PHP si no existe
sudo apt update -y
sudo apt install -y apache2 php libapache2-mod-php

# 4) Reiniciar Apache
sudo systemctl restart apache2

echo
echo "==============================================="
echo "FINALIZADO"
echo " - /var/www/html limpio y listo"
echo " - Archivo de logs creado: $LOG_FILE"
echo "Ahora coloca tu index.php en $WWW_DIR/index.php"
echo "==============================================="
