#!/usr/bin/env python3
"""Exercise the deployment hook with disposable local rsync directories."""
import os
from pathlib import Path
import re
import shlex
import shutil
import subprocess
import tempfile
import unittest

ROOT = Path(__file__).resolve().parent.parent
RSYNC = shutil.which('rsync')


@unittest.skipUnless(RSYNC, 'rsync is required')
class DeploymentFilters(unittest.TestCase):
    def setUp(self):
        self.temporary = tempfile.TemporaryDirectory(prefix='rpgdx deploy test ')
        self.addCleanup(self.temporary.cleanup)
        self.directory = Path(self.temporary.name)
        self.source = self.directory / 'source'
        self.destination = self.directory / 'destination'
        self.bin = self.directory / 'bin'
        for path in (self.source, self.destination, self.bin):
            path.mkdir()

        runtime = (
            'forums/phpbb/db/tools/tools.php',
            'forums/phpbb/install/installer.php',
            'forums/phpbb/install/module/install_database/task/create_schema_file.php',
        )
        for name in runtime:
            self.assertTrue((ROOT / name).is_file(), name)
        self.included = {
            'index.php', 'forums/index.php', '.htaccess',
            'forums/.htaccess', 'forums/includes/.htaccess',
            'forums/cache/.htaccess', 'forums/files/.htaccess',
            'forums/store/.htaccess', 'uploads/.htaccess',
            'forums/images/avatars/upload/.htaccess',
            'forums/styles/prosilver/template/overall_header.html',
            'forums/styles/rpgdx/theme/rpgdx.css',
            'forums/vendor/package/README.md',
            *runtime,
        }
        for directory in ('tools', 'install', 'sql', 'docker', 'deploy', 'scripts', 'tests'):
            self.included.add('public-component/' + directory + '/runtime.php')
        self.excluded = {
            '.git/config', '.env', '.htaccess_forwards', 'nested/.private',
            'readme.md', 'HOSTED', 'TODO', 'rpgdx.code-workspace',
            'sql/database.sql', 'install/install.php', 'install/.htaccess',
            'forums/install/app.php', 'forums/install/.htaccess', 'tools/.htaccess',
            'docker/apache/Dockerfile', 'docker-compose.yml',
            'docker-compose.migration.yml', 'docker-compose.override.yaml',
            'deploy/post-receive', 'tools/phpbb-install/phpbbcli.php',
            'scripts/migration/private.php', 'tests/private.php',
            'forums/config.php', 'forums/cache/production/container.php',
            'forums/files/attachment.dat', 'forums/store/database-backup.sql',
            'forums/images/avatars/upload/avatar.png',
            'uploads/screenshot.png', 'uploads/nested/screenshot.png',
        }
        for name in self.included | self.excluded:
            self.write(self.source, name, 'source content: ' + name)
        self.protected = {
            'forums/config.php', 'forums/cache/production/container.php',
            'forums/files/attachment.dat', 'forums/store/database-backup.sql',
            'forums/images/avatars/upload/avatar.png',
            'uploads/screenshot.png', 'uploads/nested/screenshot.png',
            '.htaccess_forwards', '.well-known/acme-challenge/token',
            'forums/images/avatars/legacy.gif',
            'forums/images/avatars/legacy.jpg',
            'forums/images/avatars/legacy.png',
            'images/guicomp/bjorn.png', 'images/tablet.png',
            'images/sponge.jpg', 'images/sourcesecure-120x90.gif',
            'templates/modern/images/rpgdx_banner.png',
        }
        for name in self.protected:
            self.write(self.destination, name, 'existing server content: ' + name)
        self.stale = {
            'retired.php', '.retired.php', '.well-known/retired.php',
            'forums/renamed-entry.php',
            'tools/retired.php', 'forums/images/avatars/retired.php',
            'forums/images/avatars/.retired.php', 'images/guicomp/retired.php',
            'images/guicomp/.retired.php', 'images/retired.php',
            'templates/modern/images/retired.php',
        }
        for name in self.stale:
            self.write(self.destination, name, 'obsolete server content: ' + name)
        self.write(self.destination, 'index.php', 'old website')
        self.before = self.snapshot()

        # Change only deployment paths, leaving the actual hook and filters intact.
        hook = (ROOT / 'deploy/post-receive').read_text()
        for variable, path in (('REPO', self.source), ('WWW_DATA', self.destination)):
            hook, count = re.subn(r'^' + variable + r'=.*$',
                                 variable + '=' + shlex.quote(str(path)), hook,
                                 flags=re.MULTILINE)
            self.assertEqual(count, 1, variable)
        self.hook = self.directory / 'post-receive'
        self.hook.write_text(hook)
        self.environment = dict(os.environ, PATH=str(self.bin) + os.pathsep + os.environ.get('PATH', ''))

    @staticmethod
    def write(directory, name, text):
        path = directory / name
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(text)

    def snapshot(self):
        return {path.relative_to(self.destination).as_posix(): path.read_bytes()
                for path in self.destination.rglob('*') if path.is_file()}

    def run_hook(self, *, dry_run=False, fail=False):
        wrapper = self.bin / 'rsync'
        if fail:
            wrapper.write_text('#!/bin/sh\necho "Intentional rsync failure" >&2\nexit 23\n')
        else:
            options = ' --dry-run' if dry_run else ''
            wrapper.write_text('#!/bin/sh\nexec ' + shlex.quote(RSYNC) +
                               options + ' --out-format=%n "$@"\n')
        wrapper.chmod(0o700)
        return subprocess.run(['bash', str(self.hook)], env=self.environment,
                              capture_output=True, text=True, check=False)

    def test_dry_run_keeps_runtime_and_excludes_private_paths(self):
        result = self.run_hook(dry_run=True)
        self.assertEqual(result.returncode, 0, result.stderr)
        listed = set(result.stdout.splitlines())
        self.assertFalse(self.included - listed, self.included - listed)
        self.assertFalse(self.excluded & listed, self.excluded & listed)
        self.assertEqual(self.snapshot(), self.before)

    def test_copy_removes_stale_files_and_preserves_server_data(self):
        result = self.run_hook()
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertIn('Website deployed!', result.stdout)
        after = self.snapshot()
        for name in self.included:
            self.assertEqual(after[name], (self.source / name).read_bytes(), name)
        for name in self.protected:
            self.assertEqual(after[name], self.before[name], name)
        self.assertFalse(self.stale & after.keys())
        self.assertFalse((self.excluded - self.protected) & after.keys())
        self.assertEqual(after.keys(), self.included | self.protected)

    def test_wiki_archive_symlink_survives_deployment(self):
        archive = self.directory / 'wiki-release'
        archive.mkdir()
        link = self.destination / 'wiki'
        link.symlink_to(archive, target_is_directory=True)
        result = self.run_hook()
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertEqual(link.readlink(), archive)

    def test_stale_symlink_is_removed_without_following_its_target(self):
        outside = self.directory / 'outside'
        outside.mkdir()
        self.write(outside, 'keep.php', 'outside deployment')
        link = self.destination / 'retired-link'
        link.symlink_to(outside, target_is_directory=True)
        result = self.run_hook()
        self.assertEqual(result.returncode, 0, result.stderr)
        self.assertFalse(link.is_symlink())
        self.assertEqual((outside / 'keep.php').read_text(), 'outside deployment')

    def test_rsync_failure_cannot_report_success(self):
        result = self.run_hook(fail=True)
        self.assertEqual(result.returncode, 23)
        self.assertIn('Intentional rsync failure', result.stderr)
        self.assertNotIn('Website deployed!', result.stdout)
        self.assertEqual(self.snapshot(), self.before)


if __name__ == '__main__':
    unittest.main()
