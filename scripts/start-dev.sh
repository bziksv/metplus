#!/bin/sh
set -e
cd "$(dirname "$0")/.."
PROJECT="$(pwd)"
PHP83=/opt/homebrew/opt/php@8.3
NGINX=/opt/homebrew/bin/nginx
RUN_DIR="$PROJECT/.local/run"

mkdir -p "$RUN_DIR"

if mysqladmin ping -h 127.0.0.1 --silent 2>/dev/null; then
  if [ ! -f "$PROJECT/bitrix/php_interface/dbconn.local.php" ]; then
    "$PROJECT/scripts/apply-local-db-config.sh"
  fi
  TABLES=$(mysql -h 127.0.0.1 -u test_metplus -p'08USA~kW+oi|fi;k' -N -e \
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='test_metplus'" 2>/dev/null || echo 0)
  if [ "$TABLES" -lt 50 ]; then
    if [ -f "$RUN_DIR/mysql-import.pid" ] && kill -0 "$(cat "$RUN_DIR/mysql-import.pid")" 2>/dev/null; then
      echo "MySQL import in progress — tail -f $RUN_DIR/mysql-import.log"
    else
      echo "WARN: DB empty ($TABLES tables). Run: ./scripts/setup-local-db.sh"
    fi
  else
    echo "Local MySQL: test_metplus ($TABLES tables)"
  fi
else
  echo "WARN: MySQL not running — brew services start mysql"
fi

"$PROJECT/scripts/stop-dev.sh" 2>/dev/null || true
sleep 1

USER_NAME="$(whoami)"
USER_GROUP="$(id -gn)"

sed "s|PROJECT_ROOT|$PROJECT|g; s|RUN_DIR|$RUN_DIR|g" \
  "$PROJECT/.local/nginx/nginx.conf" > "$RUN_DIR/nginx.conf"
sed "s|RUN_DIR|$RUN_DIR|g" \
  "$PROJECT/.local/php/fpm.conf" > "$RUN_DIR/fpm.conf"
sed "s|USER_NAME|$USER_NAME|g; s|USER_GROUP|$USER_GROUP|g" \
  "$PROJECT/.local/php/pools.conf" > "$RUN_DIR/pools.conf"
cp "$PROJECT/.local/php/php.ini" "$RUN_DIR/php.ini"

export PHPRC="$RUN_DIR/php.ini"

"$PHP83/sbin/php-fpm" -y "$RUN_DIR/fpm.conf" &
FPM_PID=$!
sleep 1

if ! kill -0 "$FPM_PID" 2>/dev/null; then
  echo "php-fpm failed — see $RUN_DIR/php-fpm.log"
  exit 1
fi

"$NGINX" -c "$RUN_DIR/nginx.conf"
sleep 1

HTTP=$(curl -sS -o /tmp/metplus-check.html -w '%{http_code}' --max-time 60 http://localhost:8086/ || echo 000)

echo "http://localhost:8086/ → HTTP $HTTP"
echo "stop: ./scripts/stop-dev.sh"

if [ "$HTTP" = "000" ]; then
  echo "WARN: site not responding — check $RUN_DIR/nginx-error.log"
  tail -10 "$RUN_DIR/nginx-error.log" 2>/dev/null || true
  exit 1
fi
