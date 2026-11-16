#!/usr/bin/env python3
"""Flag upstream template changes that need review in RPGDX child overrides."""
import argparse
import hashlib
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent


def upstream_templates(root):
    child = root / 'forums/styles/rpgdx/template'
    parent = root / 'forums/styles/prosilver/template'
    templates = {}
    errors = []
    for path in sorted(child.rglob('*.html')):
        name = path.relative_to(child).as_posix()
        source = parent / name
        if not source.is_file():
            errors.append('Missing upstream source: ' + name)
        else:
            templates[name] = hashlib.sha256(source.read_bytes()).hexdigest()
    return templates, errors


def check_baseline(current, baseline):
    errors = []
    for name in sorted(current.keys() - baseline.keys()):
        errors.append('New override needs an upstream baseline: ' + name)
    for name in sorted(baseline.keys() - current.keys()):
        errors.append('Removed override still has an upstream baseline: ' + name)
    for name in sorted(current.keys() & baseline.keys()):
        if current[name] != baseline[name]:
            errors.append('Upstream changed; review the child override: ' + name)
    return errors


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument('--record', action='store_true',
                        help='Record native source hashes after reviewing the child overrides')
    args = parser.parse_args()
    templates, errors = upstream_templates(ROOT)
    manifest_path = ROOT / 'tools/phpbb-style-upstream.json'
    if errors:
        print('\n'.join(errors))
        return 1
    if args.record:
        version = json.loads((ROOT / 'tools/phpbb-upstream.json').read_text())['version']
        manifest = {'phpbb_version': version, 'templates': templates}
        manifest_path.write_text(json.dumps(manifest, indent=2) + '\n')
        print(f'Recorded {len(templates)} RPGDX template baselines for phpBB {version}')
        return 0
    if not manifest_path.is_file():
        print('Missing upstream template baseline; review overrides before using --record')
        return 1
    baseline = json.loads(manifest_path.read_text())
    errors = check_baseline(templates, baseline['templates'])
    print('\n'.join(errors) if errors else
          f'RPGDX: {len(templates)} overridden upstream templates unchanged')
    return int(bool(errors))


if __name__ == '__main__':
    raise SystemExit(main())
