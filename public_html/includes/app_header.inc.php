<?php
  define('PLATFORM_NAME', 'LiteCart');
  define('PLATFORM_VERSION', '2.2.0');

  if (!file_exists(__DIR__ . '/config.inc.php')) {
    header('Location: ./install/');
    exit;
  }

// Start redirecting output to the output buffer
  ob_start();

// Get config
  require_once __DIR__ . '/config.inc.php';

// Compatibility and Polyfills
  require_once FS_DIR_APP . 'includes/compatibility.inc.php';

// Virtual Modifications System
  require_once FS_DIR_APP . 'includes/library/lib_vmod.inc.php';

// Autoloader
  require_once vmod::check(FS_DIR_APP . 'includes/autoloader.inc.php');

// 3rd party autoloader (If present)
  if (is_file(FS_DIR_APP . 'vendor/autoload.php')) {
    require_once FS_DIR_APP . 'vendor/autoload.php';
  }

// Set error handler
  require_once vmod::check(FS_DIR_APP . 'includes/error_handler.inc.php');

// Jump-Start some library modules
  class_exists('compression');
  class_exists('notices');
  class_exists('stats');
  class_exists('security');
  if (file_get_contents('php://input')) {
    class_exists('form');
  }

// Run operations before capture
  event::fire('before_capture');