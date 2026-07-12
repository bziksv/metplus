#!/bin/sh
set -e
cd "$(dirname "$0")/.."
PROJECT="$(pwd)"
ENV_FILE="$PROJECT/.local/db.env"
LOG_DIR="$PROJECT/.local/run"
LOG_FILE="$LOG_DIR/mysql-import.log"
PID_FILE="$LOG_DIR/mysql-import.pid"

. "$ENV_FILE"

SQL_PATH="$PROJECT/$SQL_DUMP"
if [ ! -f "$SQL_PATH" ]; then
  echo "Dump not found: $SQL_PATH"
  exit 1
fi

mkdir -p "$LOG_DIR"

if ! mysqladmin ping -h "$DB_HOST" --silent 2>/dev/null; then
  echo "MySQL is not running. Start: brew services start mysql"
  exit 1
fi

echo "Creating database and user..."
mysql -h "$DB_HOST" -u root <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB_NAME\`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_LOGIN'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON \`$DB_NAME\`.* TO '$DB_LOGIN'@'localhost';
GRANT SESSION_VARIABLES_ADMIN, SYSTEM_VARIABLES_ADMIN ON *.* TO '$DB_LOGIN'@'localhost';
FLUSH PRIVILEGES;
SQL

TABLES=$(mysql -h "$DB_HOST" -u root -N -e \
  "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME'" 2>/dev/null || echo 0)

if [ "$TABLES" -ge 50 ]; then
  echo "Database already has $TABLES tables — skip import."
  echo "To re-import: mysql -u root -e \"DROP DATABASE \\\`$DB_NAME\\\`;\" && $0"
else
  DUMP_SIZE=$(du -h "$SQL_PATH" | awk '{print $1}')
  echo "Importing $SQL_DUMP ($DUMP_SIZE) into $DB_NAME..."

  if [ "${1:-}" = "--background" ] || [ "${1:-}" = "-b" ]; then
    if [ -f "$PID_FILE" ] && kill -0 "$(cat "$PID_FILE")" 2>/dev/null; then
      echo "Import already running (pid $(cat "$PID_FILE")). tail -f $LOG_FILE"
      exit 0
    fi
    nohup sh -c "
      mysql -h '$DB_HOST' -u root '$DB_NAME' < '$SQL_PATH' && echo 'IMPORT OK' >> '$LOG_FILE'
    " >> "$LOG_FILE" 2>&1 &
    echo $! > "$PID_FILE"
    echo "Import started in background (pid $(cat "$PID_FILE"))."
    echo "Progress: tail -f $LOG_FILE"
    echo "When done: ./scripts/apply-local-db-config.sh && ./scripts/start-dev.sh"
    exit 0
  fi

  mysql -h "$DB_HOST" -u root "$DB_NAME" < "$SQL_PATH"
  echo "Import finished."
fi

"$PROJECT/scripts/apply-local-db-config.sh"

TABLES=$(mysql -h "$DB_HOST" -u "$DB_LOGIN" -p"$DB_PASSWORD" -N -e \
  "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='$DB_NAME'" 2>/dev/null)
echo "Tables in $DB_NAME: $TABLES"
echo "Start: ./scripts/start-dev.sh"
