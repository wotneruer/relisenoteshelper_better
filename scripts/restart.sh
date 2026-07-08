#!/usr/bin/env bash
set -euo pipefail
"$(dirname "$0")/_compose.sh" down
"$(dirname "$0")/_compose.sh" up -d
"$(dirname "$0")/_compose.sh" ps
