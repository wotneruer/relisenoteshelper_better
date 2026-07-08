#!/usr/bin/env bash
set -euo pipefail
"$(dirname "$0")/_compose.sh" logs -f app worker web
