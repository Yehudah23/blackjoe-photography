#!/usr/bin/env bash
set -euo pipefail

# Stop the Laravel dev server started by scripts/dev-start.sh

BASE_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$BASE_DIR"

PID_FILE="${BASE_DIR}/storage/dev-server.pid"

if [ -f "$PID_FILE" ]; then
  PID=$(cat "$PID_FILE" || echo "")
  if [ -n "$PID" ]; then
    if kill -0 "$PID" 2>/dev/null; then
      echo "Stopping artisan serve (PID $PID)"
      kill "$PID" || true
      sleep 0.5
    else
      echo "PID $PID not running"
    fi
  fi
  rm -f "$PID_FILE"
  echo "Removed pidfile"
else
  echo "No pidfile found. Attempting to pkill any artisan serve processes."
  pkill -f "artisan serve" || true
fi
