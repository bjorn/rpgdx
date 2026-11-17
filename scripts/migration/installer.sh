#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
canonical=tools/phpbb-install
case "${1:-}" in
    disable)
        if [[ -d forums/install ]]; then
            if [[ ! -d "$canonical" ]]; then
                mv forums/install "$canonical"
            else
                python3 tools/check-phpbb-core.py
                rm -rf forums/install
            fi
        fi
        ;;
    enable)
        if [[ ! -d forums/install ]]; then
            if [[ ! -d "$canonical" ]]; then
                echo 'Missing canonical installer in tools/phpbb-install.' >&2
                exit 1
            fi
            cp -a "$canonical" forums/install
        fi
        ;;
    *) echo "Usage: $0 enable|disable" >&2; exit 2 ;;
esac
