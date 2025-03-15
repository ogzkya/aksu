#!/bin/bash
# mysqldump_backup.sh - Veritabanı yedekleme betiği

# Ayarlar
DB_USER="root"
DB_PASS=""
DB_NAME="emlak_db"
BACKUP_DIR="/path/to/backups"
DATE=$(date +"%Y-%m-%d_%H-%M-%S")
BACKUP_FILE="$BACKUP_DIR/$DB_NAME-$DATE.sql.gz"

# Yedek dizini yoksa oluştur
mkdir -p $BACKUP_DIR

# Veritabanını yedekle ve sıkıştır
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_FILE

# Başarı durumunu kontrol et
if [ $? -eq 0 ]; then
    echo "Backup completed successfully: $BACKUP_FILE"
else
    echo "Backup failed!"
    exit 1
fi

# 30 günden eski yedekleri temizle
find $BACKUP_DIR -name "$DB_NAME-*.sql.gz" -type f -mtime +30 -delete

# Yedeklerin listesini göster
echo "Current backups:"
ls -lh $BACKUP_DIR