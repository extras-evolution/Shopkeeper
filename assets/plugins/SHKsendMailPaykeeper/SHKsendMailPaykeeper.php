<?php

/*
 *
 * SHKsendMailPaykeeper plugin for Shopkeeper 0.9.6
 * 
 * System event: OnSHKMailApprovedForPayment
 * 
 * Configuration:
 * &mail_tpl=Mail template;string;@FILE:assets/plugins/SHKsendMailPaykeeper/mail_body.tpl &pay_doc=Paykeeper doc ID;int;1 &user_orders_doc=User orders doc ID;int;1 &mail_user_text=Text for user;string;<p>Вы можете оплатить заказ в <a [+pay_link_href+]>личном кабинете</a>.</p>
 * 
 */

defined('IN_MANAGER_MODE') or die();

if(!isset($mail_tpl)) $mail_tpl = '@FILE:assets/plugins/SHKsendMailPaykeeper/mail_body.tpl';
if(!isset($mail_user_text)) $mail_user_text = '<p>Вы можете оплатить заказ в <a [+pay_link_href+]>личном кабинете</a>.</p>';
if(!isset($pay_doc)) $pay_doc = 1;
if(!isset($user_orders_doc)) $user_orders_doc = 1;

$e = &$modx->Event;

$output = "";

if ($e->name == 'OnSHKMailApprovedForPayment'){
  
  $order_id = isset($order_id) ? $order_id : '';
  $price = isset($price) ? $price : 0;
  $currency = isset($currency) ? $currency : 0;
  $status = isset($status) ? $status : 0;
  $userid = isset($userid) ? $userid : 0;
  
  $mod_tab_items = $modx->getFullTableName('paykeeper_items');
  $mod_tab_payments = $modx->getFullTableName('paykeeper_payments');
  
  $site_url = $modx->config['site_url'];
  $site_name = $modx->config['site_name'];
  $pay_url = $modx->makeUrl($pay_doc, '', '', 'full');
  $phase = array("","Новый","Принят к оплате","Отправлен","Выполнен","Отменен","Оплата получена");
  $status_name = $phase[$status];
  
  if($status!=2) return false;
  
  if(!function_exists('fetchTpl')){
  function fetchTpl($tpl){
  	global $modx;
  	$template = "";
  	if(substr($tpl, 0, 6) == "@FILE:"){
  	  $tpl_file = MODX_BASE_PATH . substr($tpl, 6);
  		$template = file_get_contents($tpl_file);
  	}else if(substr($tpl, 0, 6) == "@CODE:"){
  		$template = substr($tpl, 6);
  	}else if($modx->getChunk($tpl) != ""){
  		$template = $modx->getChunk($tpl);
  	}else{
  		$template = false;
  	}
  	return $template;
  }
  }
  
  //if UNregistered user
  if($userid==0){
    
    $pay_res = $modx->db->select("ppay.id AS ppay_id, ppay.signature, ppay.email, pitem.unit","$mod_tab_payments ppay, $mod_tab_items pitem","ppay.id = pitem.payid AND ppay.orderid = '$order_id' AND ppay.state = 'I' AND pitem.paid = 'N'","","1");
    
    if($modx->db->getRecordCount($pay_res)){
      
      $row = $modx->db->getRow($pay_res);
      
      $pay_id = $row['ppay_id'];
      $pay_signature = $row['signature'];
      $pay_user_email = $row['email'];
      $pay_unit = $row['unit'];
      $pay_link = strpos($pay_url, "?")===false ? $pay_url."?pay=".$pay_signature : $pay_url."&amp;pay=".$pay_signature;
      $payment_method = $pay_unit=='RKR' ? 'robokassa' : 'webmoney';
      $payment_value = number_format(floatval($price),2,'.','');
      
      $config_file = MODX_BASE_PATH."assets/snippets/paykeeper/".$payment_method."/config.php";
      
      if(file_exists($config_file))
        require($config_file);
      else
        return;
      
      if($payment_method=='robokassa'){
        $crc = md5("$mrh_login:$payment_value:$pay_id:$mrh_pass1:Shp_item=$order_id");
      }else{
        $crc = md5("$site_url:$payment_value:$pay_unit:$pay_id:$wm_secret_key:Shp_item=$order_id");
      }
      
      $modx->db->update("reserved = NULL, price='$payment_value'", $modx->getFullTableName('paykeeper_items'), "payid='$pay_id' AND paid='N'");
      $modx->db->update("date = NOW(), signature='$crc'", $modx->getFullTableName('paykeeper_payments'), "orderid = '$order_id' AND state = 'I'");
      
      $mail_body = fetchTpl($mail_tpl);
      
      foreach (array(
          'order_id'=>$order_id,
          'status_name'=>$status_name,
          'pay_link'=>$pay_link,
          'site_name'=>$site_name
        ) as $key => $value){
        $mail_body = str_replace('[+'.$key.'+]', $value, $mail_body);
      }
      
      $output = $mail_body;
    
    }
  
  //if registered user
  }else{
    
    $pay_link_href = $modx->makeUrl($user_orders_doc, '', '', 'full');//.'?act=purchase&pid='.$order_id;
    $output = str_replace('pay_link_href',' href="'.$pay_link_href.'"',$mail_user_text);
    
  }
  
  $e->output($output);

}


?>