  </div><!-- /.content -->
</div><!-- /.main-area -->
</div><!-- /.app-wrap -->

<script>
// Highlight active nav link
document.querySelectorAll('.sb-link').forEach(link => {
  if (window.location.pathname.endsWith(link.getAttribute('href').split('/').pop())) {
    link.classList.add('active');
  }
});
</script>
</body>
</html>
