<?php

/*
 * Example usage:
 *
 * $link = new ent_link('//domain.tld/path/to/file');
 * $link->host = 'newdomain.tld';
 * echo $link;
 */

  class ent_link implements \JsonSerializable {

    private $_components;
    private $_serialized;

    public function __construct(string $link='') {

      $this->reset();

      $components = is_array($link) ? $link : parse_url($link);

      foreach ($components as $component => $value) {
        $this->$component = $value;
      }

      return $this;
    }

    public function __isset($component) {
      return $this->__get($component);
    }

    public function __unset($component) {
      return $this->__get($component);
    }

    public function __get($component) {

      if (!isset($this->_components[$component])) {
        trigger_error("Unknown link component ($component)", E_USER_WARNING);
        return null;
      }

      if (!empty($this->_components[$component])) return $this->_components[$component];

    // Set defaults
      if (in_array($component, array('scheme', 'host', 'port', 'path'))) {
        $this->$component = '';
      }

      return $this->_components[$component];
    }

    public function __set($component, $value) {

      if (!isset($this->_components[$component])) {
        trigger_error("Unknown link component ($component)", E_USER_WARNING);
        return $this;
      }

      switch($component) {

        case 'scheme':

          if (empty($value)) {
            if ($this->host == $_SERVER['HTTP_HOST']) {
              if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
                $value = 'https';
              } else {
                $value = 'http';
              }
            } else {
              $value = 'http';
            }
          }

          break;

        case 'host':

          if (empty($value)) {
            $value = $_SERVER['HTTP_HOST'];
          }

          //if (function_exists('idn_to_ascii')) {
          //  $value = idn_to_ascii($value, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
          //}

          break;

        case 'path':

        // Relative to absolute path
          if (substr($value, 0, 1) != '/') {
            if (substr($this->scheme, 0, 4) == 'http' && $this->host == $_SERVER['HTTP_HOST']) {
              $value = WS_DIR_APP . $value;
            }
          }

        // Pop path
          if (strpos($value, '..') !== false) {
            $parts = array_filter(explode('/', $value), 'strlen');
            $absolutes = array();
            foreach ($parts as $part) {
              if ('.' == $part) continue;
              if ('..' == $part) {
                array_pop($absolutes);
              } else {
                $absolutes[] = $part;
              }
            }
            $value = '/' . implode('/', $absolutes);
          }

          break;

        case 'query':

          if (!is_array($value)) {
            parse_str($value, $value);
          }

          break;
      }

      if ($this->_components[$component] != $value) {
        $this->_components[$component] = $value;
        $this->_serialized = '';
      }

      return $this;
    }

    public function __toString() {

      if (!empty($this->_serialized)) return $this->_serialized;

      $output = $this->scheme .'://';

      if (!empty($this->user)) {
        $output .= $this->user;

        if (!empty($this->pass)) {
          $output .= ':'.$this->pass;
        }

        $output .= '@';
      }

      $output .= $this->host;

      if (!empty($this->port)) {
        if ($this->scheme == 'https' && $this->port != 443) {
          $output .= ':'.$this->port;
        } else if ($this->scheme == 'http' && $this->port != 80) {
          $output .= ':'.$this->port;
        } else {
          $output .= ':'.$this->port;
        }
      }

      $output .= $this->path;

      if (!empty($this->_components['query'])) {
        $output .= '?'.http_build_query($this->_components['query'], '', '&');
      }

      if (!empty($this->_components['fragment'])) {
        $output .= '#'.$this->_components['fragment'];
      }

      return $output;
    }

    public function jsonSerialize() {
      return (string)$this;
    }

  // Workaround as overloaded array items cannot be set
    public function set_query($name, $value) {

      $this->_components['query'][$name] = $value;

      return $this;
    }

  // Workaround as overloaded array items cannot be unset
    public function unset_query($name) {

      unset($this->_components['query'][$name]);

      return $this;
    }

    public function reset() {
      $this->_components = array(
        'scheme' => '',
        'host' => '',
        'user' => '',
        'pass' => '',
        'port' => '',
        'path' => '',
        'query' => array(),
        'fragment' => '',
      );
    }
  }
