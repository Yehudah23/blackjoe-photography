#!/usr/bin/env bash
set -euo pipefail

# Start the Laravel dev server safely on 127.0.0.1:8000
# - kills any previous background artisan serve started by this script
# - writes PID to storage/dev-server.pid and logs to storage/laravel-serve.log

BASE_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$BASE_DIR"

PID_FILE="${BASE_DIR}/storage/dev-server.pid"
LOG_FILE="${BASE_DIR}/storage/laravel-serve.log"

if [ -f "$PID_FILE" ]; then
  OLD_PID=$(cat "$PID_FILE" || echo "")
  if [ -n "$OLD_PID" ] && kill -0 "$OLD_PID" 2>/dev/null; then
    echo "Found existing dev server PID $OLD_PID — killing it first"
    kill "$OLD_PID" || true
    sleep 0.5
  else
    rm -f "$PID_FILE"
  fi
fi

echo "Starting php artisan serve on 127.0.0.1:8000"
nohup php artisan serve --host=127.0.0.1 --port=8000 > "$LOG_FILE" 2>&1 &
NEW_PID=$!
echo "$NEW_PID" > "$PID_FILE"
echo "Started artisan serve (PID $NEW_PID). Logs: $LOG_FILE"
