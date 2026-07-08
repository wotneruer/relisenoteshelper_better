#!/usr/bin/env bash
set -euo pipefail

ROOT="/home/akirpichnikov/utilities/RLH"

docker compose \
  --env-file "$ROOT/env/rlh.env" \
  -f "$ROOT/env/docker-compose.yml" \
  "$@"
