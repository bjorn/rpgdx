#!/usr/bin/env python3
"""Check vendored phpBB files against the official release manifest."""
import hashlib
import json
from pathlib import Path

root = Path(__file__).resolve().parent.parent
manifest = json.loads((root / 'tools/phpbb-upstream.json').read_text())
errors = []
for name, expected in manifest['files'].items():
    path = root / 'forums' / name
    # Keep the canonical installer outside the served forum after conversion.
    if name.startswith('install/') and not (root / 'forums/install').exists():
        path = root / 'tools/phpbb-install' / name.removeprefix('install/')
    if not path.is_file():
        errors.append('Missing: ' + name)
    elif hashlib.sha256(path.read_bytes()).hexdigest() != expected:
        errors.append('Modified: ' + name)
print('\n'.join(errors) if errors else 'phpBB ' + manifest['version'] + ': upstream files unchanged')
raise SystemExit(bool(errors))
