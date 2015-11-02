<?php

if (file_exists(dirname(__FILE__)."../../../assets/cache/siteManager.php")) {
    include_once(dirname(__FILE__)."../../../assets/cache/siteManager.php");
}else{
    define('MGR_DIR', 'manager');
}


define('DOCUMENT_ROOT',$_SERVER["DOCUMENT_ROOT"]);
define('PAYKEEPER_PATH', DOCUMENT_ROOT."/assets/snippets/paykeeper/");
define('MODX_MANAGER_PATH', "../../../".MGR_DIR."/");
define('MODX_API_MODE', true);

require_once(MODX_MANAGER_PATH . 'includes/config.inc.php');
require_once(MODX_MANAGER_PATH . 'includes/protect.inc.php');
require_once(MODX_MANAGER_PATH . 'includes/document.parser.class.inc.php');
require_once(MODX_MANAGER_PATH . "includes/controls/class.phpmailer.php");


$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();

//System configuration
$site_name = $modx->config['site_name'];
$site_url = "http://".$_SERVER['HTTP_HOST']."/";//$modx->config['site_url'];
$dbname = $modx->db->config['dbase'];
$manager_language = $modx->config['manager_language'];
$charset = $modx->config['modx_charset'];
$dbname = $modx->db->config['dbase'];
$tb_prefix = $modx->db->config['table_prefix'];

$mod_tab_items = $tb_prefix."paykeeper_items";
$mod_tab_payments = $tb_prefix."paykeeper_payments";
$SHK_mod_table = $tb_prefix."manager_shopkeeper";
$SHK_mod_config_table = $tb_prefix."manager_shopkeeper_config";

$currencyArray = array(
  "rub" => array('WMR','рубли','руб.','р.'),
  "usd" => array('WMZ','USD','usd','dollars'),
  "eur" => array('WME','EUR','eur','euro'),
  "hryv" => array('WMU','гривны','украинские гривны','hryv','hryvnia')
);

function recursive_array_search($needle,$haystack){
  foreach($haystack as $key => $value){
    $current_key = $key;
    if($needle === $value || (is_array($value) && recursive_array_search($needle,$value) !== false)){
      return $current_key;
    }
  }
  return false;
}

function savePayment($dbname, $mod_tab_payments, $mod_tab_items, $SHK_mod_table, $ord_data=array()){
  global $modx;
  if(count($ord_data)==0) return false;
  extract($ord_data, EXTR_OVERWRITE);
  //update payment record
  $query = "UPDATE $mod_tab_payments SET state='S'";
  if(!empty($trans_no)) $query .= ", sys_trans_no = '$trans_no'";
  if(!empty($payer_purse)) $query .= ", payer_purse = '$payer_purse'";
  if(!empty($payer_id)) $query .= ", payer_id = '$payer_id'";
  $query .= " WHERE id = '$payment_no'";
  $result = $modx->db->query($query);
  $result2 = $modx->db->update(array('paid'=>'Y'), $mod_tab_items, "payid = '$payment_no' AND content = '$item_id'");
  if($modx->db->getAffectedRows()){
    //change order status
    if ($modx->db->getRecordCount($modx->db->query("show tables from $dbname like '$SHK_mod_table'"))>0){
      $change_status = $modx->db->update(array("status" => 6), $SHK_mod_table, "id = '$item_id'");
      
      $evtOut = $modx->invokeEvent('OnSHKChangeStatus',array('order_id'=>$item_id,'status'=>6));
    }
  }
  
}


if(file_exists(PAYKEEPER_PATH."lang/$manager_language.php")){
  require_once (PAYKEEPER_PATH."lang/$manager_language.php");
}elseif(file_exists(PAYKEEPER_PATH."lang/russian-$charset.php")){
  require_once (PAYKEEPER_PATH."lang/russian-$charset.php");
}else{
  require_once (PAYKEEPER_PATH."lang/russian.php");
}

if(!get_magic_quotes_gpc() && isset($_POST)){
  function addslashes_to_array($value){
    return addslashes($value);
  };
  $_POST = array_map('addslashes_to_array', $_POST);
}


function sendMailToAdmin($subject,$email,$body){
  global $modx;
  $mail = new PHPMailer();
  $mail->IsMail();
  $mail->IsHTML(false);
  $mail->From	= $modx->config['emailsender'];
  $mail->FromName	= $_SERVER['SERVER_NAME'];
  $mail->Subject	= $subject;
  $mail->Body	= $body;
  $mail->AddAddress($email);
  if(!$mail->send()){
    //echo $mail->ErrorInfo;
  }
};

//define payment system
if(isset($_POST['LMI_PAYMENT_NO'])){
  $pay_method = "webmoney";
  
}else if(isset($_POST["InvId"]) && isset($_POST["Shp_item"])){
  $pay_method = "robokassa";
  
}else{
  exit();
  
}


switch ($pay_method){

####################################################
# WebMoney Merchant
####################################################
  case "webmoney":
    
    require_once (PAYKEEPER_PATH."webmoney/config.php");
    
    //get parameters
    $payment_value = isset($_POST["LMI_PAYMENT_AMOUNT"]) ? $modx->db->escape($_POST["LMI_PAYMENT_AMOUNT"]) : '';
    $payment_no = isset($_POST["LMI_PAYMENT_NO"]) ? $modx->db->escape($_POST["LMI_PAYMENT_NO"]) : '';
    $payment_orderid = isset($_POST["OrderID"]) ? $modx->db->escape($_POST["OrderID"]) : '';
    $payment_signature = isset($_POST["SignatureValue"]) ? $modx->db->escape($_POST["SignatureValue"]) : '';
    $payment_purse = isset($_POST['LMI_PAYEE_PURSE']) ? $modx->db->escape($_POST['LMI_PAYEE_PURSE']) : '';
    $payment_currency = isset($_POST['LMI_PAYEE_PURSE']) ? "WM".substr($payment_purse,0,1) : '';
    $payment_payer_purse = isset($_POST['LMI_PAYER_PURSE']) ? $modx->db->escape($_POST['LMI_PAYER_PURSE']) : '';
    $payment_desc = isset($_POST['LMI_PAYMENT_DESC']) ? $modx->db->escape($_POST['LMI_PAYMENT_DESC']) : '';
    $payer_id = isset($_POST["LMI_PAYER_WM"]) ? $modx->db->escape($_POST["LMI_PAYER_WM"]) : '';
    $payment_chkstring = isset($_POST['LMI_HASH']) ? $modx->db->escape($_POST['LMI_HASH']) : '';
    $payment_trans_date = isset($_POST['LMI_SYS_TRANS_DATE']) ? $modx->db->escape($_POST['LMI_SYS_TRANS_DATE']) : '';
    $payment_sys_no = isset($_POST['LMI_SYS_INVS_NO']) ? $modx->db->escape($_POST['LMI_SYS_INVS_NO']) : '';
    $payment_trans_no = isset($_POST['LMI_SYS_TRANS_NO']) ? $modx->db->escape($_POST['LMI_SYS_TRANS_NO']) : '';
    $payment_secret_key = isset($_POST['LMI_SECRET_KEY']) ? $modx->db->escape($_POST['LMI_SECRET_KEY']) : '';
    if(empty($payment_secret_key)) $payment_secret_key = $wm_secret_key;
    $pay_purse_type = substr($payment_currency,-1);
    
    $payment_value = number_format(floatval($payment_value),2,'.','');
    $payment_item_type = '';
    
    $crc = $payment_signature;
    $crc_start = md5("$site_url:$payment_value:$pay_purse_type:$payment_no:$payment_secret_key:Shp_item=$payment_orderid");
    
    $pay_curr = recursive_array_search($payment_currency,$currencyArray);
    $pay_to_purse = $purse[$pay_curr];
    
    //Prerequest
    if(isset($_POST['LMI_PREREQUEST']) && $_POST['LMI_PREREQUEST'] == 1){
    
      if(isset($_POST['LMI_PAYMENT_NO']) && preg_match('/^\d+$/',$_POST['LMI_PAYMENT_NO']) == 1){
        
        $query_where = "pitem.payid = ppay.id "
        ."AND pitem.content = '$payment_orderid' "
        ."AND pitem.payid = $payment_no "
        ."AND pitem.content = ppay.orderid "
        ."AND pitem.price = $payment_value "
        ."AND pitem.unit='R' "
        ."AND pitem.state='Y' "
        ."AND pitem.paid='N' "
        ."AND ((pitem.reserved IS NULL) OR (pitem.reserved + INTERVAL 3 MINUTE > NOW())) "
        ."AND ppay.state='I' "
        ."AND ppay.signature='$crc_start'";
        
        $item_res = $modx->db->select("pitem.id, pitem.price, pitem.unit", "$mod_tab_items pitem, $mod_tab_payments ppay", $query_where);
        if($modx->db->getRecordCount($item_res) >= 1 && $payment_signature == $crc_start){
          
          $result1 = $modx->db->update(array('state'=>'R'), $mod_tab_payments, "id='$payment_no'");
          $query2 = "UPDATE $mod_tab_items SET reserved = NOW() WHERE content = '$payment_orderid'";
          $result2 = $modx->db->query($query2);
          
          if($modx->db->getAffectedRows()){
            echo "YES";
          }else{
            echo "NO3";
            exit();
          }
        }else{
          echo "NO2";
          exit();
        }
          
      }else{
        echo "NO1";
        exit();
      }
      
      exit();
    
    //Notification
    }else{
    
      if(isset($_POST['LMI_PAYMENT_NO']) && preg_match('/^\d+$/',$_POST['LMI_PAYMENT_NO']) == 1){
        
        $query_where = "pitem.payid = ppay.id "
        ."AND pitem.content = '$payment_orderid' "
        ."AND pitem.payid = $payment_no "
        ."AND pitem.content = ppay.orderid "
        ."AND pitem.price = $payment_value "
        ."AND pitem.unit='R' "
        ."AND pitem.state='Y' "
        ."AND pitem.paid='N' "
        ."AND pitem.reserved + INTERVAL 3 MINUTE > NOW() "
        ."AND ppay.state='R' "
        ."AND ppay.signature='$crc_start'";
        
        $item_res = $modx->db->select("pitem.id, pitem.price, pitem.unit", "$mod_tab_items pitem, $mod_tab_payments ppay", $query_where);
        if($modx->db->getRecordCount($item_res) >= 1 && $payment_signature == $crc_start){
        	
      	  $ord_data = array(
            "trans_date" => $payment_trans_date,
            "sys_no" => $payment_sys_no,
            "trans_no" => $payment_trans_no,
            "payer_purse" => $payment_payer_purse,
            "payer_id" => $payer_id,
            "item_id" => $payment_orderid,
            "payment_value" => $payment_value,
            "payment_no" => $payment_no
          );
          
          savePayment($dbname, $mod_tab_payments, $mod_tab_items, $SHK_mod_table, $ord_data);
          
    	  }
    	  
      }
      
      exit();
      
    }
  
  break;
####################################################
# Robokassa
####################################################
  case "robokassa":
    
    require_once (PAYKEEPER_PATH."robokassa/config.php");
    
    //get parameters
    $payment_value = isset($_POST["OutSum"]) ? $modx->db->escape($_POST["OutSum"]) : '';
    $payment_no = isset($_POST["InvId"]) ? $modx->db->escape($_POST["InvId"]) : '';
    $payment_orderid = isset($_POST["Shp_item"]) ? $modx->db->escape($_POST["Shp_item"]) : '';
    $payment_signature = isset($_POST["SignatureValue"]) ? $modx->db->escape($_POST["SignatureValue"]) : '';
    
    $crc = strtoupper($payment_signature);
    $my_crc = strtoupper(md5("$payment_value:$payment_no:$mrh_pass2:Shp_item=$payment_orderid"));
    
    // check signature
    if ($my_crc != $crc){
      echo "bad sign\n";
      exit();
    }
    
    
    $pay_val = number_format($payment_value,2,'.',''); //$pay_val = number_format($payment_value,2);
    
    $crc_start = md5("$mrh_login:$pay_val:$payment_no:$mrh_pass1:Shp_item=$payment_orderid");
    
    
    // success
    echo "OK$payment_no\n";
    
    $query_where = "pitem.payid = ppay.id "
    ."AND pitem.content = '$payment_orderid' "
    ."AND pitem.content = ppay.orderid "
    ."AND pitem.price = $payment_value "
    ."AND pitem.unit='RKR' "
    ."AND pitem.state='Y' "
    ."AND pitem.paid='N' "
    ."AND ppay.signature='$crc_start'";
    
    $item_res = $modx->db->select("pitem.id, pitem.price, pitem.unit", "$mod_tab_items pitem, $mod_tab_payments ppay", $query_where);
    if($modx->db->getRecordCount($item_res) >= 1){
      
      $ord_data = array(
        "item_id" => $payment_orderid,
        "payment_no" => $payment_no
      );
      savePayment($dbname, $mod_tab_payments, $mod_tab_items, $SHK_mod_table, $ord_data);
      
    }
    
  break;
####################################################
# Null
####################################################
  default:
	
	break;
}


?>