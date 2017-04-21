<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/*******************************************
* http://modx-shopkeeper.ru/
* ----------------------------------
* Paykeeper 1.5.2
* Snippet for payments in MODx + Shopkeeper
*   Webmoney (http://webmoney.ru/rus/index.shtml),
*   Robokassa (http://www.robokassa.ru/)
*******************************************/

defined('IN_PARSER_MODE') or die();

//System configuration
$site_name = $modx->config['site_name'];
$site_url = $modx->config['site_url'];
$dbname = $modx->db->config['dbase'];
$tb_prefix = $modx->db->config['table_prefix'];
$base_dir = $modx->config['rb_base_dir'];
$site_url = $modx->config['site_url'];
$manager_language = $modx->config['manager_language'];
$charset = $modx->config['modx_charset'];
$userLogged =  isset($_SESSION['webValidated']) ? true : false;
$thisPage = $modx->makeUrl($modx->documentIdentifier, '', '', 'full');
$thisURL = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
$qs = strpos($thisURL, "?")===false ? '?' : '&amp;';

$debug = false;

$mod_tab_items = $tb_prefix."paykeeper_items";
$mod_tab_payments = $tb_prefix."paykeeper_payments";
$SHK_mod_table = $tb_prefix."manager_shopkeeper";
$SHK_mod_config_table = $tb_prefix."manager_shopkeeper_config";

define('PAYKEEPER_PATH', $base_dir."snippets/paykeeper/");

$lang = isset($lang) ? $lang : $manager_language;

if(file_exists(PAYKEEPER_PATH."lang/".$lang."-".str_replace('-','',$charset).".php")){
  $s_lang = $lang."-".str_replace('-','',$charset);
}elseif(file_exists(PAYKEEPER_PATH."lang/".$lang.".php")){
  $s_lang = $lang;
}else{
  $s_lang = "russian";
}


require PAYKEEPER_PATH."lang/".$s_lang.".php";


//Snippet configuration
$paymentDesc = isset($paymentDesc) ? $paymentDesc : $langTxt['payment_desc'];
$paymentButton = isset($paymentButton) ? $paymentButton : $langTxt['payment_button'];
$resultURL = isset($resultURL) ? $resultURL : '';//$site_url."assets/snippets/paykeeper/result.php";
$successDoc = isset($successDoc) ? $successDoc : $modx->documentIdentifier;
$successMethod = isset($successMethod) ? $successMethod : "1";
$failDoc = isset($failDoc) ? $failDoc : $modx->documentIdentifier;
$failMethod = isset($failMethod) ? $failMethod : "1";
$sim_mode = isset($sim_mode) ? $sim_mode : false;
$payment_creditdays = isset($payment_creditdays) ? $payment_creditdays : false;
$payTest = isset($payTest) ? $payTest : 'false';
$successURL = $modx->makeUrl($successDoc, '', '', 'full');
$fail_url = $modx->makeUrl($failDoc, '', '', 'full');
$payment_reserve = isset($payment_reserve) ? $payment_reserve : 'false';
$reservingDays = isset($reservingDays) ? intval($reservingDays) : 2;
$skipEmailStep = isset($skipEmailStep) ? $skipEmailStep : false;

//Templates options
$template['webmoney']['startpay'] = isset($WMstartPayTpl) ? $WMstartPayTpl : '@FILE:assets/snippets/paykeeper/tpl/WMstartPayTpl.tpl';
$template['webmoney']['pay'] = isset($WMpayTpl) ? $WMpayTpl : '@FILE:assets/snippets/paykeeper/tpl/WMkpayTpl.tpl';
$template['robokassa']['startpay'] = isset($RKstartPayTpl) ? $RKstartPayTpl : '@FILE:assets/snippets/paykeeper/tpl/RKstartPayTpl.tpl';
$template['robokassa']['pay'] = isset($RKpayTpl) ? $RKpayTpl : '@FILE:assets/snippets/paykeeper/tpl/RKpayTpl.tpl';

if(!function_exists('numberFormat')){
function numberFormat($number,$excepDigitGroup=true){
    $output = $number;
    if($excepDigitGroup){
      $output = number_format($number,(floor($number) == $number ? 0 : 2),'.',' ');
    }
    return $output;
}
}

function recursive_array_search($needle,$haystack){
  foreach($haystack as $key => $value){
    $current_key = $key;
    if($needle === $value || (is_array($value) && recursive_array_search($needle,$value) !== false)){
      return $current_key;
    }
  }
  return false;
}


function getSignature($login,$password,$payment_value,$payment_orderid,$payment_no=''){
  global $modx;
  if(empty($payment_no)){
    $pn_select = $modx->db->select("id",$modx->getFullTableName("paykeeper_payments"),"orderid = '$payment_orderid'");
    if($modx->db->getRecordCount($pn_select)){
      $payment_no = $modx->db->getValue($pn_select);
    }else{
      $last_id = $modx->db->getValue($modx->db->select("MAX(id)", $modx->getFullTableName("paykeeper_payments")));
      $payment_no = $last_id+1;
    }
  }
  $signature = "$login:$payment_value:$payment_no:$password:Shp_item=$payment_orderid";
  return md5($signature);
}

function checkTablesDB($dbname){
  global $modx;
  if ($modx->db->getRecordCount($modx->db->query("show tables from $dbname like '".$modx->db->config['table_prefix']."paykeeper_items'"))==0){
    $sql = array();
    $sql[] = "CREATE TABLE ".$modx->db->config['table_prefix']."paykeeper_items (`id` int(11) NOT NULL auto_increment, `payid` int(11) NOT NULL default '0', `description` char(255) NOT NULL default '',`content` varchar(255) default NULL,`price` float(9,2) NOT NULL default '0.00',`unit` varchar(255) default NULL,`state` enum('Y','N') default 'Y',`reserved` datetime default NULL,`paid` enum('Y','N') NOT NULL default 'N',PRIMARY KEY  (`id`))";
    $sql[] = "CREATE TABLE ".$modx->db->config['table_prefix']."paykeeper_payments (`id` int(11) NOT NULL auto_increment,`orderid` int(11) default '0',`state` enum('I','R','S','F') default 'I',`date` datetime default '0000-00-00 00:00:00',`userid` int(11) NOT NULL default '0',`email` varchar(255) default NULL,`signature` varchar(40) default NULL,`sys_trans_no` int(11) default NULL,`payer_purse` varchar(13) default NULL,`payer_id` varchar(12) default NULL,PRIMARY KEY  (`id`))";
    foreach ($sql as $line){
      $modx->db->query($line);
    }
  }
}

if(!function_exists('getNextAutoIncrement')){
function getNextAutoIncrement($table_name){
  global $modx;
  $next_increment = 0;
  $query = $modx->db->query("SHOW TABLE STATUS LIKE '$table_name'");
  while($row = $modx->db->getRow($query)){
    $next_increment = $row['Auto_increment'];
  }
  return $next_increment;
}
}

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


$currencyArray = array(
  "rub" => array('WMR','рубли','руб.','р.'),
  "usd" => array('WMZ','USD','usd','dollars'),
  "eur" => array('WME','EUR','eur','euro'),
  "hryv" => array('WMU','гривны','украинские гривны','hryv','hryvnia')
);


if(!get_magic_quotes_gpc() && isset($_POST)){
  function addslashes_to_array($value){
    return addslashes($value);
  };
  $_POST = array_map('addslashes_to_array', $_POST);
}



//Payment options

//payment for unregistered user
if(isset($_GET['pay'])){
  
  $pay_signature = $modx->db->escape($_GET['pay']);
  
  $pay_res = $modx->db->select(
    "pitem.id, pitem.price, pitem.unit, pitem.description, pitem.reserved, ppay.orderid, ppay.id AS payid, ppay.date, ppay.userid, ppay.email",
    "$mod_tab_items pitem, $mod_tab_payments ppay",
    "ppay.signature = '$pay_signature' AND pitem.payid = ppay.id AND pitem.paid='N'",// AND pitem.reserved + INTERVAL 2 DAY > NOW()"
    "","1"
  );
  if($modx->db->getRecordCount($pay_res) == 1){
    
    $pay_row = $modx->db->getRow($pay_res);
    
    //if((strtotime($pay_row['date'])+(3600*24*$reservingDays))>strtotime(date('Y-m-d H:i:s'))){
        
    $payment_no = $pay_row['payid'];
    $payment_orderid = $pay_row['orderid'];
    $payment_userid = $pay_row['userid'];
    $payment_useremail = $pay_row['email'];
    $payment_value = $pay_row['price'];
    $payment_currency = strlen($pay_row['unit'])==1 ? "WM".$pay_row['unit'] : $pay_row['unit'];
    $payment_method = $payment_currency=='RKR' ? 'robokassa' : 'webmoney';
    
    $modx->setPlaceholder('SHKorderid',$payment_orderid);
    
  }else{
    $error = $langTxt['error_1'];
  }
  
  unset($pay_res);


}else{

  $payment_method = isset($payment_method) ? $payment_method : (isset($_SESSION['shk_payment_method']) ? $_SESSION['shk_payment_method'] : ''); //`webmoney` || `robokassa`
  $payment_method = mb_detect_encoding($payment_method,"UTF-8",true)!==false ? mb_strtolower($payment_method,"UTF-8") : strtolower($payment_method);
  $payment_orderid = isset($payment_orderid) ? $payment_orderid : (isset($_SESSION['shk_order_id']) ? $_SESSION['shk_order_id'] : '');
  $payment_userid = isset($payment_userid) ? $payment_userid : (isset($_SESSION['shk_order_user_id']) ? $_SESSION['shk_order_user_id'] : '');
  $payment_useremail = isset($payment_useremail) ? $payment_useremail : (isset($_SESSION['shk_order_user_email']) ? $_SESSION['shk_order_user_email'] : '');
  $payment_value = isset($payment_value) ? $payment_value : (isset($_SESSION['shk_order_price']) ? $_SESSION['shk_order_price'] : '');
  $payment_currency = isset($payment_currency) ? $payment_currency : (isset($_SESSION['shk_currency']) ? $_SESSION['shk_currency'] : '');

}

if(empty($payment_method)) return;

$paymentDesc = str_replace('[+orderID+]',$payment_orderid,$paymentDesc);

$payment_value = isset($payment_value) ? number_format(floatval($payment_value),2,'.','') : '';
$pay_curr = recursive_array_search($payment_currency,$currencyArray);
$pay_purse_type = $payment_method=='robokassa' ? 'RKR' : substr($currencyArray[$pay_curr][0],-1);

if($userLogged){
  $user = $modx->userLoggedIn();
  $modx_webuser = $modx->getWebUserInfo($user['id']);
}

$userId = !empty($payment_userid) ? $payment_userid : (isset($user) ? $user['id'] : 0);


$output = '';

$default_action = $skipEmailStep && $payment_useremail ? 'payment' : 'pay_start';
$action = $payment_reserve=='true' ? 'reserve' : (isset($_POST['action']) ? $_POST['action'] : $default_action);

switch($action){

####################################################
# Reserve
####################################################
  case "reserve":
    
    $config_file = PAYKEEPER_PATH.$payment_method."/config.php";
    if(file_exists($config_file))
      require($config_file);
    else
      return;
    
    checkTablesDB($dbname);
    
    if($payment_method=='robokassa'){//robokassa
      $crc = getSignature($mrh_login,$mrh_pass1,$payment_value,$payment_orderid);
    }else{//webmoney
      $crc = getSignature($site_url,$wm_secret_key,$payment_value.":".$pay_purse_type,$payment_orderid);
    }
    
    if($debug) echo "$crc<br />";
    
    //if NOT registered user
    if($payment_userid==0){
      
      $query_select = $modx->db->select('pitem.id AS pitem_id,ppay.id ppay_id', "$mod_tab_items pitem, $mod_tab_payments ppay", "pitem.payid = ppay.id AND ppay.orderid='$payment_orderid' AND pitem.paid='N'","","1");
      if($modx->db->getRecordCount($query_select)==0){
        $query_result = $modx->db->query("INSERT INTO $mod_tab_payments SET orderid='$payment_orderid', state='I', date=NOW(), userid='$payment_userid', email='$payment_useremail', signature='$crc'");
        $payment_no = $modx->db->getInsertId();
        $query_result = $modx->db->query("INSERT INTO $mod_tab_items SET payid='$payment_no', description='$paymentDesc', content='$payment_orderid', price='$payment_value', unit='$pay_purse_type', state='Y', paid='N'");
      }
      
    }
    
    $output .= $langTxt['reserved'];
    
    //remove old unpaid orders
    $modx->db->query("DELETE $mod_tab_items pitem, $mod_tab_payments ppay FROM $mod_tab_items pitem, $mod_tab_payments ppay WHERE pitem.payid = ppay.id AND ppay.state='I' AND pitem.paid='N' AND ppay.date + INTERVAL 7 DAY < NOW()");
    
      
  break;
####################################################
# Start payment
####################################################
  case "pay_start":
      
      //check order phase (status)
      if(isset($_GET['pay']) && $modx->db->getValue($modx->db->select("status",$SHK_mod_table,"id='$payment_orderid'"))!=2){
        $error = $langTxt['error_3'];
      }
      
      if(isset($error)){
        $output = "<p>$error</p>";
        return;
      }
      
      $pay_email = !empty($payment_useremail) ? $payment_useremail : (isset($modx_webuser) ? $modx_webuser['email'] : '');
      $paySelect = '';
      $chunk = fetchTpl($template[$payment_method]['startpay']);
      
      $chunkArr = array(
        "pay_select" => $paySelect,
        "action" => $thisURL,
        "email" =>  $pay_email,
        "disabled" => empty($pay_email) ? ' disabled="disabled"' : '',
        "payment_desc" => $langTxt['payment_desc'],
        "payment_button" => $paymentButton
      );
      foreach ($chunkArr as $key => $value){
        $chunk = str_replace('[+'.$key.'+]', $value, $chunk);
      }
      unset($key,$value);
      
      $output .= $chunk;
    
    
  break;
####################################################
# Payment
####################################################
  case "payment":
    
    checkTablesDB($dbname);
    
    $config_file = PAYKEEPER_PATH.$payment_method."/config.php";
    if(file_exists($config_file))
      require($config_file);
    else
      return;
    
    $pay_to_purse = $purse[$pay_curr];
    
    $email = isset($_POST['email']) && !empty($_POST['email']) ? $modx->db->escape($_POST['email']) : $payment_useremail;
    $regexp = '/^[a-zA-Z][\w\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/';
    $email_string = preg_match($regexp, $email) ? 'E-mail: <b>'.$email.'</b>' : $langTxt['verify_email'].': <b>'.$email.'</b>';
    
    //get $payment_no
    if(!isset($payment_no)){
      $query_select = $modx->db->select('pitem.id AS pitem_id,ppay.id ppay_id', "$mod_tab_items pitem, $mod_tab_payments ppay", "pitem.payid = ppay.id AND ppay.orderid='$payment_orderid' AND pitem.paid='N'","","1");
      if($modx->db->getRecordCount($query_select)>0){
        $ppay = $modx->db->getRow($query_select);
        $payment_no = $ppay['ppay_id'];
      }else{
        
        $last_id = $modx->db->getValue($modx->db->select("MAX(id)", $mod_tab_payments));
        $payment_no = $last_id+1;

        $needInsert = 1;
        
        if($debug) echo "$payment_no - $needInsert<br />";
        
      }
      unset($ppay);
    }
    
    $chunkArr = array();
    $chunk = fetchTpl($template[$payment_method]['pay']);
    
    switch($payment_method){
      ##########################################
      case "webmoney":
        
        $browser_ie = strpos($_SERVER['HTTP_USER_AGENT'], "MSIE")===false ? false : true;
        $pay_action = $browser_ie && $charset=="UTF-8" ? $site_url."assets/snippets/paykeeper/webmoney/utf8_to_cp1251.php" : "https://merchant.webmoney.ru/lmi/payment.asp";
        $crc = getSignature($site_url,$wm_secret_key,$payment_value.":".$pay_purse_type,$payment_orderid,$payment_no);
        $h_inputs = '
          <input type="hidden" name="LMI_PAYMENT_AMOUNT" value="'.$payment_value.'" />
          <input type="hidden" name="LMI_PAYMENT_DESC" value="'.$paymentDesc.'" />
          <input type="hidden" name="LMI_PAYMENT_NO" value="'.$payment_no.'" />
          <input type="hidden" name="LMI_PAYEE_PURSE" value="'.$pay_to_purse.'" />
          <input type="hidden" name="SignatureValue" value="'.$crc.'" />
          <input type="hidden" name="OrderID" value="'.$payment_orderid.'" />
        ';
        if($resultURL){
          $h_inputs .= '
          <input type="hidden" name="LMI_RESULT_URL" value="'.$resultURL.'" />';
        }
        
      break;
      ##########################################
      case "robokassa":
        
        $pay_action = $payTest!='true' ? 'https://merchant.roboxchange.com/Index.aspx' : 'http://test.robokassa.ru/Index.aspx';
        $crc = getSignature($mrh_login,$mrh_pass1,$payment_value,$payment_orderid,$payment_no);
        $h_inputs = '
          <input type="hidden" name="MrchLogin" value="'.$mrh_login.'" />
          <input type="hidden" name="OutSum" value="'.$payment_value.'" />
          <input type="hidden" name="InvId" value="'.$payment_no.'" />
          <input type="hidden" name="Desc" value="'.$paymentDesc.'" />
          <input type="hidden" name="SignatureValue" value="'.$crc.'" />
          <input type="hidden" name="Shp_item" value="'.$payment_orderid.'" />
          <input type="hidden" name="Email" value="'.$email.'" />
          <input type="hidden" name="IncCurrLabel" value="'.$in_curr.'" />
          <input type="hidden" name="Culture" value="'.$culture.'" />
        ';
        
      break;
      default:
    	
    	break;
    }
    
    if($debug) echo "$payment_method - $crc<br />";
    
    //save to database
    if(isset($needInsert)){

      $query_result = $modx->db->query("INSERT INTO $mod_tab_payments SET id='$payment_no', orderid='$payment_orderid', state='I', date=NOW(), userid='$payment_userid', email='$email', signature='$crc'");
      $query_result = $modx->db->query("INSERT INTO $mod_tab_items SET payid='$payment_no', description='$paymentDesc', content='$payment_orderid', price='$payment_value', unit='$pay_purse_type', state='Y', paid='N'");
      
    }else{
      
      $modx->db->query("UPDATE $mod_tab_payments SET date=NOW() WHERE id='$payment_no'");
      
    }
    
    //parse chunk
    $chunkArr = array(
      "email_string" => $email_string,
      "pay_summ" => numberFormat($payment_value),
      "pay_currency" => $payment_currency,
      "pay_desc" => $paymentDesc,
      "action" => $pay_action,
      "pay_purse_type" => $pay_purse_type,
      "h_inputs" => $h_inputs
    );
    foreach ($chunkArr as $key => $value){
      $chunk = str_replace('[+'.$key.'+]', $value, $chunk);
    }
    unset($key,$value);
    
    $output = $chunk;
    
  break;
####################################################
# null
####################################################
  default:
    
  break;
}



?>