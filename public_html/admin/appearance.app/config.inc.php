<?php

  return $app_config = array(
    'name' => language::translate('title_appearance', 'Appearance'),
    'default' => 'template',
    'priority' => 0,
    'theme' => array(
      'color' => '#ff2a72',
      'icon' => 'fa-adjust',
    ),
    'menu' => array(
      array(
        'title' => language::translate('title_template', 'Template'),
        'doc' => 'template',
        'params' => array(),
      ),
      array(
        'title' => language::translate('title_logotype', 'Logotype'),
        'doc' => 'logotype',
        'params' => array(),
      ),
    ),
    'docs' => array(
      'logotype' => 'logotype.inc.php',
      'template' => 'template.inc.php',
      'template_catalog_settings' => 'template_catalog_settings.inc.php',
      'template_admin_settings' => 'template_admin_settings.inc.php',
    ),
  );
