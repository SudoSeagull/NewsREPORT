<?php if (!defined('ABSPATH')) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<script>
// Apply saved theme ASAP
(function(){
  try {
    var saved = localStorage.getItem('drudge_theme');
    if (saved) document.documentElement.setAttribute('data-theme', saved);
  } catch(e) {}
})();
</script>
</head>
<body <?php body_class('container-fluid'); ?>>
<header class="container py-3">
  <div class="d-flex justify-content-between align-items-center">
    <a class="navbar-brand h3 m-0 text-decoration-none" href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
    <div class="d-flex align-items-center gap-2">
      <?php if (is_active_sidebar('ad_header')) { dynamic_sidebar('ad_header'); } ?>
      <button id="themeToggle" class="theme-toggle btn btn-sm" type="button" aria-label="Toggle theme">🌓</button>
    </div>
  </div>
</header>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var btn = document.getElementById('themeToggle');
  if (!btn) return;
  btn.addEventListener('click', function(){
    var current = document.documentElement.getAttribute('data-theme');
    var next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    try { localStorage.setItem('drudge_theme', next); } catch(e) {}
  });
});
</script>
<main class="container my-3">
