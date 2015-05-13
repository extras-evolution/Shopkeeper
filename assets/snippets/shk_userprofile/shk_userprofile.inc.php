<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/***********************************
* 
* http://modx-shopkeeper.ru/
* SHKUserProfile 1.4.4
* snippet for MODx + Shopkeeper
* 
***********************************/

defined('IN_PARSER_MODE') or die();

$base_dir = $modx->config['rb_base_dir'];
$manager_language = $modx->config['manager_language'];
$charset = $modx->config['modx_charset'];
$rb_base_url = $modx->config['rb_base_url'];
$site_url = $modx->config['site_url'];
$dbname = $modx->db->config['dbase'];
$dbprefix = $modx->db->config['table_prefix'];
$userLogged =  isset($_SESSION['webValidated']) ? true : false;

$mod_table = $dbprefix."manager_shopkeeper";
$mod_config_table = $dbprefix."manager_shopkeeper_config";
$mod_user_table = $dbprefix."web_user_additdata";

if(!defined('SHK_UP_PATH')) define('SHK_UP_PATH', MODX_BASE_PATH."assets/snippets/shk_userprofile/");

$upconf = array();
$upconf['id'] = isset($id) ? $id : '';
$upconf['id_prefix'] = $upconf['id'] ? $upconf['id'].'_' : '';
$upconf['lang'] = isset($lang) ? $lang : $manager_language;
$upconf['showOrder'] = isset($showOrder) ? $showOrder : 'false';
$upconf['additFieldsHide'] = isset($additFieldsHide) ? $additFieldsHide : 'count_purchase';
$upconf['profileTpl'] = isset($profileTpl) ? $profileTpl : '@FILE:assets/snippets/shk_userprofile/tpl/profile.tpl';
$upconf['ordersListTpl'] = isset($ordersListTpl) ? $ordersListTpl : '@FILE:assets/snippets/shk_userprofile/tpl/ordersList.tpl';
$upconf['orderDescTpl'] = isset($orderDescTpl) ? $orderDescTpl : '@FILE:assets/snippets/shk_userprofile/tpl/orderDesc.tpl';
$upconf['purchasesListTpl'] = isset($purchasesListTpl) ? $purchasesListTpl : '@FILE:assets/snippets/shk_userprofile/tpl/purchasesList.tpl';
$upconf['additDataTpl'] = isset($additDataTpl) ? $additDataTpl : '@FILE:assets/snippets/shk_userprofile/tpl/additData.tpl';
$upconf['additFieldsTpl'] = isset($additFieldsTpl) ? $additFieldsTpl : '@FILE:assets/snippets/shk_userprofile/tpl/addit_fields.tpl';
$upconf['menuTpl'] = isset($menuTpl) ? $menuTpl : '@FILE:assets/snippets/shk_userprofile/tpl/menu.tpl';
$upconf['subMenuTpl'] = isset($subMenuTpl) ? $subMenuTpl : '@FILE:assets/snippets/shk_userprofile/tpl/subMenu.tpl';
$upconf['excepDigitGroup'] = isset($excepDigitGroup) ? $excepDigitGroup : true;
$upconf['oneLevelMenu'] = isset($oneLevelMenu) ? $oneLevelMenu : 'false'; 
$upconf['thisPage'] = $modx->makeUrl($modx->documentIdentifier, '', '', 'full');
$upconf['qs'] = strpos($upconf['thisPage'], "?")===false ? '?' : '&amp;';
$upconf['display'] = isset($display) && is_numeric($display) ? $display : 10;
$upconf['action'] = isset($action) ? $action : ''; //profile | curorders | archorders

$output = '';

//default Paykeeper params
$upconf['pay_params'] = array();
$upconf['pay_params']['payment_method'] = 'robokassa'; //default

//get Paykeeper params
foreach($params as $key => $value){
  if(strpos($key,'pay_')!==false){
    $key = str_replace('pay_','',$key);
    $upconf['pay_params'][$key] = $value;
  }
}
unset($key,$value);


//get SHK phases colors
$upconf['phaseColor'] = array();
if (mysql_num_rows(mysql_query("show tables from $dbname like '$mod_config_table'"))>0){
  $conf_colors = $modx->db->getValue($modx->db->select("value", $mod_config_table, "setting = 'conf_colors'", "", ""));
  $upconf['phaseColor'] = explode("~", $conf_colors);
  $installed = 1;
}

if(file_exists(SHK_UP_PATH."lang/".$upconf['lang']."-".str_replace('-','',$charset).".php")){
  $s_lang = $upconf['lang']."-".str_replace('-','',$charset);
}elseif(file_exists(SHK_UP_PATH."lang/".$upconf['lang'].".php")){
  $s_lang = $upconf['lang'];
}else{
  $s_lang = "russian";
}

//include files
require SHK_UP_PATH."lang/".$s_lang.".php";
require_once MODX_BASE_PATH."assets/snippets/shopkeeper/classes/class.shopkeeper.php";
require_once SHK_UP_PATH."classes/pagination.class.php";
require_once SHK_UP_PATH."classes/class.shkuserprofile.php";
$shk_uprofile = new SHKuserprofile($modx,$upconf);
$shk_uprofile->langTxt = $langTxt;

////user not logged
if(!$userLogged || isset($_GET['pay'])){;
    if($upconf['showOrder']=='true'){
        $output .= $shk_uprofile->showOrderDesc(0,0);
    }
    return;
}

$user = $modx->userLoggedIn();
$userId = $user['id'];
$modx_webuser = $modx->getWebUserInfo($userId);
$email = $modx_webuser['email'];

///////////////////////////////////////////////////////////
//create menu
///////////////////////////////////////////////////////////
$subMenu = $shk_uprofile->createSubmenu($menuChunkArrSub);
$mainMenu = $shk_uprofile->createMenu($subMenu);
$modx->setPlaceholder(($upconf['id'] ? $upconf['id'].'.' : '').'up_menu',$mainMenu);
$modx->setPlaceholder(($upconf['id'] ? $upconf['id'].'.' : '').'submenu',$menu_active=='purchase' ? $subMenu : '');
///////////////////////////////////////////////////////////

if(isset($_GET[$upconf['id_prefix'].'act']) || isset($_GET[$upconf['id_prefix'].'sub'])){
    $upconf['action'] = isset($_GET[$upconf['id_prefix'].'sub']) ? $_GET[$upconf['id_prefix'].'sub'] : $_GET[$upconf['id_prefix'].'act'];
    $shk_uprofile->config['action'] = $upconf['action'];
}

switch($upconf['action']){
///////////////////////////////////////////////////////////
//page - profile
///////////////////////////////////////////////////////////
  case "profile":
    
    $error = 0;
    $ph['message'] = '';
    
    if(isset($_POST['email'])){
      
      if(isset($_POST['fullname'])) $fields['fullname'] = $modx->db->escape($_POST['fullname']);
      if(isset($_POST['email'])) $fields['email'] = $modx->db->escape($_POST['email']);
      if(isset($_POST['phone'])) $fields['phone'] = $modx->db->escape($_POST['phone']);
      if(isset($_POST['mobilephone'])) $fields['mobilephone'] = $modx->db->escape($_POST['mobilephone']);
      if(isset($_POST['fax'])) $fields['fax'] = $modx->db->escape($_POST['fax']);
      if(isset($_POST['state'])) $fields['state'] = $modx->db->escape($_POST['state']);
      if(isset($_POST['zip'])) $fields['zip'] = $modx->db->escape($_POST['zip']);
      if(isset($_POST['country'])) $fields['country'] = $modx->db->escape($_POST['country']);
      if(isset($_POST['comment'])) $fields['comment'] = $modx->db->escape($_POST['comment']);
      if(isset($_POST['oldpassword'])) $fields['oldpassword'] = $modx->db->escape($_POST['oldpassword']);
      if(isset($_POST['password'])) $fields['password'] = $modx->db->escape($_POST['password']);
      if(isset($_POST['repassword'])) $fields['repassword'] = $modx->db->escape($_POST['repassword']);
      
      //check for duplicate email address
      if($modx_webuser['email'] != $fields['email']){
        $sql = $modx->db->query("SELECT internalKey FROM ".$dbprefix."web_user_attributes WHERE email='".$fields['email']."'");
        if($modx->db->getRecordCount($sql)>0){
          $row = $modx->db->getRow($sql);
          if($row['internalKey'] != $userId){
            $ph['message'] .= "<span class=\"errors\">".str_replace('[+new_email+]',$fields['email'],$langTxt['error_email'])."</span><br />\n";
            $error++;
          }
        }
      }
      
      //change password
      if(isset($_POST['chpwd']) && !empty($fields['oldpassword']) && !empty($fields['password'])){
      
        if(strlen($fields['password'])<6){
          $ph['message'] .= "<span class=\"errors\">".$langTxt['error_pass_len']."</span><br />\n";
          $error++;
        }
        if($modx_webuser['password'] != md5($fields['oldpassword'])){
          $ph['message'] .= "<span class=\"errors\">".$langTxt['error_oldpass']."</span><br />\n";
          $error++;
        }
        if($fields['password'] != $fields['repassword']){
          $ph['message'] .= "<span class=\"errors\">".$langTxt['error_passerr']."</span><br />\n";
          $error++;
        }
        if($error==0){
          $new_password = md5($fields['password']);
          $modx->db->update(array('password'=>$new_password), $dbprefix."web_users", "id = '$userId'");
          $shk_uprofile->sendNewPassword($fields['email'],$modx_webuser['username'],$fields['password'],$fields['fullname']);
          $ph['success_mess'] .= "<span class=\"success\">".$langTxt['success_chpass']."</span><br />\n";
        }
        unset($fields['password']);
        unset($fields['oldpassword']);
        unset($fields['repassword']);
      
      }else{
        
        $ph['success_mess'] .= "<span class=\"success\">".$langTxt['success_chprofile']."</span><br />\n";
        
      }
      
      if(isset($fields['password'])) unset($fields['password']);
      if(isset($fields['oldpassword'])) unset($fields['oldpassword']);
      if(isset($fields['repassword'])) unset($fields['repassword']);
      if(isset($fields['id'])) unset($fields['id']);
      if(isset($fields['internalKey'])) unset($fields['internalKey']);
      
      if($error==0){
        $modx->db->update($fields, $dbprefix."web_user_attributes", "internalKey = '$userId'");
        $modx->invokeEvent("OnWebSaveUser",array("mode" => "upd", "userid" => $userId));
        if(isset($ph['success_mess'])){
            $modx_webuser = $modx->getWebUserInfo($userId);
        }else{
            $modx->sendRedirect($_SERVER['REQUEST_URI'],0,"REDIRECT_HEADER");
        }
      }
      
    }
    
    $chunk = $shk_uprofile->fetchTpl($upconf['profileTpl']);
    
    //run plugin
    $plugin = '';
    $evtOut = $modx->invokeEvent('OnWUsrFormRender',array('id'=>$userId,'tpl'=>$shk_uprofile->fetchTpl($upconf['additFieldsTpl']),'hide_fields'=>$upconf['additFieldsHide']));
    if (is_array($evtOut)) $plugin = implode('', $evtOut);
    unset($evtOut);

    //show form
    foreach (array_merge($ph,$modx_webuser,array('addit_fields'=>$plugin)) as $key => $value){
      $chunk = str_replace("[+".$key."+]", $value, $chunk);
    }
    unset($key,$value);
    
    $output .= $chunk;
    
  break;
///////////////////////////////////////////////////////////
//logout
///////////////////////////////////////////////////////////
  case "logout":
    
    if(isset($_SESSION['mgrValidated'])) {
      unset($_SESSION['webShortname']);
      unset($_SESSION['webFullname']);
      unset($_SESSION['webEmail']);
      unset($_SESSION['webValidated']);
      unset($_SESSION['webInternalKey']);
      unset($_SESSION['webValid']);
      unset($_SESSION['webUser']);
      unset($_SESSION['webFailedlogins']);
      unset($_SESSION['webLastlogin']);
      unset($_SESSION['webnrlogins']);
      unset($_SESSION['webUsrConfigSet']);
      unset($_SESSION['webUserGroupNames']);
      unset($_SESSION['webDocgroups']);
    }else {
      if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', 0, MODX_BASE_URL);
      }
      session_destroy();
    }
    $modx->sendRedirect($site_url,0,'REDIRECT_REFRESH');
    
  break;
///////////////////////////////////////////////////////////
//discounts
///////////////////////////////////////////////////////////
  case "discounts":
     
     $output .= "[+up_menu+]";
     
  break;
///////////////////////////////////////////////////////////
//page - orders
///////////////////////////////////////////////////////////
  default:
    
    $title = empty($upconf['action']) || $upconf['action']=='curorders' ? $langTxt['curorders'] : $langTxt[$upconf['action']];
    
    //order description
    if(isset($_GET[$upconf['id_prefix'].'pid'])){
      
      //cancel order
      if(isset($_POST['shk_del'])){
        $modx->db->update(array('status'=>5), $mod_table, "id = ".$modx->db->escape($_POST['pid'])." AND userid = '$userId'");
      }
      
      //refresh order
      if(isset($_POST['shk_refresh'])){
          $orderId = $modx->db->escape($_POST['pid']);
          $new_orderid = $shk_uprofile->refreshOrder($orderId,$userId);
          if($new_orderid!==false) $modx->sendRedirect($upconf['thisPage'].$upconf['qs'].$upconf['id_prefix']."act=purchase&".$upconf['id_prefix']."pid=".$new_orderid,0,"REDIRECT_HEADER");
      }
      
      $output .= $shk_uprofile->showOrderDesc($_GET[$upconf['id_prefix'].'pid'],$userId);
    
    //order list
    }else{
      
      $chunk = preg_split('/(\[\+loop\+\]|\[\+end_loop\+\])/s',$shk_uprofile->fetchTpl($upconf['ordersListTpl']));
      $list_chunk = $chunk[1];
      $output_list = '';
      
      $status_list = $upconf['action']=='archorders' ? '4,5' : '1,2,3,6';
      $total = $modx->db->getValue($modx->db->select("COUNT(*)",$mod_table,"userid = '$userId' AND status in($status_list)"));

      //pagination
      $pages = '';
      $qs_page = !empty($_GET[$upconf['id_prefix'].'page']) && is_numeric($_GET[$upconf['id_prefix'].'page']) ? $modx->db->escape($_GET[$upconf['id_prefix'].'page']) : 1;
      $qs_start = !empty($_GET[$upconf['id_prefix'].'start']) && !is_array($_GET[$upconf['id_prefix'].'start']) ? $_GET[$upconf['id_prefix'].'start'] : ($qs_page*$upconf['display'])-$upconf['display'];
      $p = new pagination;
      $p->nextT = ' <a href="[+link+]">'.$langTxt['next'].'</a> ';
      $p->prevT = ' <a href="[+link+]">'.$langTxt['prev'].'</a> ';
      $p->numberT = ' <a href="[+link+]">[+num+]</a> ';
      $p->currentT = ' <b>[+num+]</b> ';//' <a class="current" href="[+link+]">[+num+]</a> ';
      $p->prevI = '';
      $p->Items($total);
      $p->limit($upconf['display']);
      $p->target($upconf['thisPage'].$upconf['qs'].$upconf['id_prefix']."act=purchase&amp;".$upconf['id_prefix']."sub=".$upconf['action']);
      $p->currentPage($qs_page);
      $p->parameterName($upconf['id_prefix'].'page');
      $pages .= $p->getOutput();
      $totalPages = ceil($orders_total_count/$upconf['display']);

      //orders
      $output_list = $shk_uprofile->getOrderList($userId,$list_chunk,$qs_start,$status_list);

      $shk_uprofile->phx->placeholders = array();
      $shk_uprofile->setPlaceholders(array(
        'notempty' => $total > 0 ? 'true' : '',
        'title' => $title,
        'list' => $output_list,
        'pages' => $pages,
        'totalPages' => $totalPages
      ));
      $output .= $shk_uprofile->phx->Parse($chunk[0].'[+list+]'.$chunk[2]);
      $output = $shk_uprofile->cleanPHx($output);
      
    }
    
  break;
}

return $output;

?>
