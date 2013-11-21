<?php
  define('REQUIRE_POST_TOKEN', false);
  define('SEO_REDIRECT', false);
  require_once('includes/app_header.inc.php');
  
  header('X-Robots-Tag: noindex');
  
  $shipping = new shipping();
  
  $payment = new payment();
  
  $order = new ctrl_order('resume');
  
  if (count($shipping->options()) > 0) {
    if (empty($shipping->data['selected'])) trigger_error('No shipping selected', E_USER_ERROR);
    //list($shipping_module_id, $shipping_option_id) = explode(':', $shipping->data['selected']['id']);
  }
  
  if (count($payment->options()) > 0) {
    if (empty($payment->data['selected'])) trigger_error('No payment selected', E_USER_ERROR);
    //list($payment_module_id, $payment_option_id) = explode(':', $payment->data['selected']['id']);
    
    if ($payment_error = $payment->run('pre_check')) {
      $system->notices->add('errors', $payment_error);
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'));
      exit;
    }
    
    if (isset($_POST['confirm_order'])) {
    
      if (!empty($_POST['comments'])) {
        $order->data['comments']['session']['text'] = $_POST['comments'];
      }
      
      if ($gateway = $payment->transfer()) {
      
        if (!empty($gateway['error'])) {
          $system->notices->add('errors', $gateway['error']);
          header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'));
          exit;
        }
        
        var_dump($gateway);exit;
        switch (strtolower($gateway['method'])) {
          
          case 'post':
            echo '<p><img src="'. WS_DIR_IMAGES .'icons/16x16/loading.gif" width="16" height="16" /> '. $system->language->translate('title_redirecting', 'Redirecting') .'...</p>' . PHP_EOL
               . '<form name="gateway_form" method="post" action="'. $gateway['action'].'">' . PHP_EOL;
            if (is_array($gateway['fields'])) {
              foreach ($gateway['fields'] as $key => $value) echo '  ' . $system->functions->form_draw_hidden_field($key, $value) . PHP_EOL;
            } else {
              echo $gateway['fields'];
            }
            echo '</form>' . PHP_EOL
               . '<script language="javascript">' . PHP_EOL;
            if (!empty($gateway['delay'])) {
              echo '  var t=setTimeout(function(){' . PHP_EOL
                 . '    document.forms["gateway_form"].submit();' . PHP_EOL
                 . '  }, '. ($gateway['delay']*1000) .');' . PHP_EOL;
            } else {
              echo '  document.forms["gateway_form"].submit();' . PHP_EOL;
            }
            echo '</script>';
            exit;
            
          case 'html':
            echo $gateway['content'];
            require_once(FS_DIR_HTTP_ROOT . WS_DIR_INCLUDES . 'app_footer.inc.php');
            exit;
          
          case 'get':
          default:
            header('Location: '. (!empty($gateway['action']) ? $gateway['action'] : $system->document->link()));
            exit;
        }
      }
    }
    
  // Verify transaction
    $result = $payment->run('verify');
    
  // If payment error
    if (!empty($result['error'])) {
      $system->notices->add('errors', $result['error']);
      header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'checkout.php'));
      exit;
    }
    
  // Set order status id
    if (isset($result['order_status_id'])) $order->data['order_status_id'] = $result['order_status_id'];
    
  // Set transaction id
    if (isset($result['transaction_id'])) $order->data['payment_transaction_id'] = $result['transaction_id'];
  }
  
// Save order
  $order->save();
  
// Reset cart
  $system->cart->reset();
  
// Send e-mails
  $order->email_order_copy($order->data['customer']['email']);
  foreach (explode(';', $system->settings->get('email_order_copy')) as $email) {
    $order->email_order_copy($email);
  }
  
// Run after process operations
  $shipping->run('after_process');
  $payment->run('after_process');
  
  header('Location: '. $system->document->link(WS_DIR_HTTP_HOME . 'order_success.php'));
  exit;
?>