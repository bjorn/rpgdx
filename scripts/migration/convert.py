#!/usr/bin/env python3
"""Drive the unmodified phpBB web converter. Never log source records."""
import argparse
import html
import http.cookiejar
import json
import os
import re
import urllib.parse
import urllib.request
from pathlib import Path


def main():
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--url", default="http://localhost:8083/forums/install/app.php")
    parser.add_argument("--source-db", default="rpgdx_legacy")
    parser.add_argument("--source-host", default="db")
    parser.add_argument("--source-user", default="username")
    parser.add_argument("--source-path", default="../legacy-forums")
    parser.add_argument("--resume", action="store_true")
    args = parser.parse_args()
    password = os.environ.get("MIGRATION_DB_PASSWORD")
    if password is None:
        parser.error("set MIGRATION_DB_PASSWORD")
    opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
    origin = urllib.parse.urlsplit(args.url)

    def fail(body, message):
        path = Path('/tmp/rpgdx-converter-error.html')
        descriptor = os.open(path, os.O_WRONLY | os.O_CREAT | os.O_TRUNC, 0o600)
        os.fchmod(descriptor, 0o600)
        with os.fdopen(descriptor, 'w') as output:
            output.write(body)
        raise RuntimeError(message + '; inspect ' + str(path) + ' locally')

    def request(url, data=None):
        if urllib.parse.urlsplit(url).netloc != origin.netloc:
            raise RuntimeError("Converter tried to leave the configured host")
        req = urllib.request.Request(url, data=data)
        if data is not None:
            req.add_header("X-Requested-With", "XMLHttpRequest")
        with opener.open(req, timeout=300) as response:
            body = response.read().decode("utf-8", errors="replace")
        return body

    base = args.url.rstrip("/")
    if args.resume:
        url = base + "/convert/in_progress/phpbb20"
    else:
        body = request(base + "/convert/settings/phpbb20", urllib.parse.urlencode({
            "submit": "1", "src_dbms": "mysqli", "src_dbhost": args.source_host,
            "src_dbport": "", "src_dbuser": args.source_user, "src_dbpasswd": password,
            "src_dbname": args.source_db, "src_table_prefix": "phpbb_",
            "forum_path": args.source_path, "refresh": "1",
        }).encode())
        # AJAX sends one JSON document per line, sometimes preceded by padding.
        objects = []
        for line in body.splitlines():
            try:
                objects.append(json.loads(line))
            except (json.JSONDecodeError, ValueError):
                pass
        redirects = [item["redirect"]["url"] for item in objects if isinstance(item, dict) and item.get("redirect")]
        if not redirects:
            fail(body, 'Converter rejected settings')
        url = urllib.parse.urljoin(base, html.unescape(redirects[-1]))

    for step in range(1000):
        body = request(url)
        if "/convert/finished" in urllib.parse.urlsplit(url).path:
            print("Official phpBB2 conversion completed")
            return
        if re.search(r'class="errorbox"|id="error-container"|Fatal error|SQL ERROR', body, re.I):
            fail(body, 'Converter reported an error')
        refresh = re.search(r'<meta[^>]+http-equiv="refresh"[^>]+content="[^"]*url=([^\"]+)', body, re.I)
        if refresh:
            target = refresh.group(1)
        else:
            links = re.findall(r'<a\b[^>]*href="([^"]+)"[^>]*class="[^"]*button1', body, re.I)
            links = [link for link in links if "/convert/" in link]
            if not links:
                fail(body, 'No converter continuation found')
            target = links[-1]
        url = urllib.parse.urljoin(url, html.unescape(target))
        print(f"Converter batch {step + 1}", flush=True)
    raise RuntimeError("Converter exceeded 1000 batches")


if __name__ == "__main__":
    main()
