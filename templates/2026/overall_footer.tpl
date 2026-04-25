    </div>
  </main>
</div>

<footer class="site-footer">
  <p>RPGDX &copy; 2002&ndash;2026 by Thorbj&oslash;rn Lindeijer</p>
  <p class="site-footer-meta">Powered by <a href="https://www.php.net/">PHP</a> and <a href="https://mariadb.org/">MariaDB</a></p>
</footer>

<script>
(function () {
  var toggle = document.querySelector('.nav-toggle');
  var nav = document.getElementById('primary-nav');
  if (!toggle || !nav) return;
  toggle.addEventListener('click', function () {
    var open = nav.classList.toggle('is-open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
})();
</script>
</body>
</html>
