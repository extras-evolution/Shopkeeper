<?php

/**
* SHKmanager
*
* controller
*
* @author Andchir <andchir@gmail.com>
* @version 1.3.4
*/

defined('IN_MANAGER_MODE') or die();

date_default_timezone_set('Europe/Moscow');
setlocale (LC_ALL, 'ru_RU.UTF-8');

$dbname = $modx->db->config['dbase'];
$dbprefix = $modx->db->config['table_prefix'];
$theme = $modx->config['manager_theme'];
$charset = $modx->config['modx_charset'];
$site_name = $modx->config['site_name'];
$manager_language = $modx->config['manager_language'];
$rb_base_url = $modx->config['rb_base_url'];
$mod_page = "index.php?a=112&id=".$_GET['id'];
$cur_shk_version = '1.3.4';

define("SHOPKEEPER_PATH","../assets/snippets/shopkeeper/");

if(file_exists(SHOPKEEPER_PATH."module/lang/".$manager_language.".php")){
  $lang = $manager_language;
}elseif(file_exists(SHOPKEEPER_PATH."module/lang/russian".$charset.".php")){
  $lang = "russian".$charset;
}else{
  $lang = "russian";
}

require_once SHOPKEEPER_PATH."classes/pagination.class.php";
require_once SHOPKEEPER_PATH."module/lang/".$lang.".php";
require_once SHOPKEEPER_PATH."classes/class.shopkeeper.php";
require_once SHOPKEEPER_PATH."classes/class.shk_manager.php";

$shkm = new SHKmanager($modx);
$shkm->config['tplPath'] = 'assets/snippets/shopkeeper/module/templates/';
$shkm->cur_version = $cur_shk_version;
$shkm->langTxt = $langTxt;
$shkm->dbname = $dbname;
$shkm->mod_page = $mod_page;
$shkm->mod_table = $dbprefix."manager_shopkeeper";
$shkm->mod_config_table = $dbprefix."manager_shopkeeper_config";
$shkm->mod_user_table = $dbprefix."web_user_additdata";
$shkm->mod_catalog_table = $dbprefix."catalog";
$shkm->mod_catalog_tv_table = $dbprefix."catalog_tmplvar_contentvalues";
$shkm->tab_eventnames = $dbprefix."system_eventnames";
$shkm->excepDigitGroup = true;

//Настройки модуля
$tmp_config = $shkm->getModConfig();
extract($tmp_config);
$installed = isset($conf_shk_version) ? 1 : 0;
$notify = array();

//Верхние кнопки
$mod_links = array();
if(isset($conf_catalog) && $conf_catalog) $mod_links[] = array($langTxt['catalog_mod'], SHOPKEEPER_PATH.'style/default/img/catalog.png','catalog');
$mod_links[] = array($langTxt['configTitle'], SHOPKEEPER_PATH.'style/default/img/menu_settings.gif','config');

//Проверка версии и обновление
if((isset($conf_shk_version) && $conf_shk_version != $cur_shk_version) || !isset($conf_shk_version)){
  //update version
  if($installed){
    $shkm->modUpdate();
    $notify[] = $langTxt['notify_mod_updated'].$cur_shk_version;
  }
}



if(!isset($_SESSION['mod_loaded'])){
  //Проверка дат заказов и отмена просроченных
  if(!empty($conf_phase_days)){
    $ord_canceled = $shkm->chkOrdersPeriod($conf_phase_days);
    if($ord_canceled>0){
      $notify[] = $ord_canceled.$langTxt['notify_checked_orders_dates'];
    }
  }
}

$action = !empty($_GET['action']) ? $_GET['action'] : (!empty($_POST['action']) ? $_POST['action'] : '');

switch($action) {

//Установка модуля
case 'install':
  $shkm->modInstall();
  $modx->sendRedirect($mod_page,0,"REDIRECT_HEADER");
break;

//Удаление модуля
case "uninstall":
  if(!$modx->hasPermission('save_document')){
      global $e;
      $e->setError(3);
      $e->dumpError();
      exit;
    }
  $shkm->modUninstall();
  $modx->sendRedirect($mod_page,0,"REDIRECT_HEADER");
break;

//Конфигурация
case "config":
  include "templates/header.tpl.php";
  include "templates/config.tpl.php";
break;

//Каталог
case "catalog":
    include "templates/header.tpl.php";
    include "catalog.inc.php";
break;

//Плагин
case "plugin":
    include "templates/header.tpl.php";
    $evtOut = $modx->invokeEvent('OnSHKmodPagePrint');
    if (is_array($evtOut)) echo implode('', $evtOut);
    unset($evtOut);
break;

//Сохранение конфигурации
case "save_config":
  if(!$modx->hasPermission('save_document')){
		global $e;
    $e->setError(3);
		$e->dumpError();
    exit;
	}
  $shkm->saveConfig($_POST);
  $modx->sendRedirect($mod_page,0,"REDIRECT_HEADER");
break;

//Удаление заказа из БД
case 'delete':
  if(!$modx->hasPermission('save_document')){
      global $e;
      $e->setError(3);
      $e->dumpError();
      exit;
    }
  $modx->db->delete($shkm->mod_table, "id = $_POST[item_id]");
  $modx->sendRedirect($mod_page,0,"REDIRECT_HEADER");
break;

//Удаление отмеченных заказов
case 'delgroup':
  if(!$modx->hasPermission('save_document')){
    global $e;
    $e->setError(3);
    $e->dumpError();
    exit;
  }
  $shkm->deleteGroup($_POST);
  $modx->sendRedirect($mod_page,0,"REDIRECT_HEADER");
break;

//Изменение статуса заказа
case 'status':
  if(!$modx->hasPermission('save_document')){
    global $e;
    $e->setError(3);
    $e->dumpError();
    exit;
  }
  $status = isset($_GET['item_val']) ? $_GET['item_val'] : $_POST['item_val'];
  $item_id = isset($_GET['item_id']) ? $_GET['item_id'] : $_POST['item_id'];
  $update_arr = array("status" => $status);
  $qs_notify = '';
  
  if(in_array($status,array(2,3,5))){
  
    $data = $modx->db->getRow($modx->db->select("id, short_txt, content, allowed, addit, price, currency, DATE_FORMAT(date,'%d.%m.%Y %k:%i') AS date, status, email, phone, payment, tracking_num,  userid", $shkm->mod_table, "id = $item_id", "", ""));
    $data['purchases'] = unserialize($data['content']);
    $data['addit'] = unserialize($data['addit']);
    $modx_webuser = $data['userid']!=0 ? $modx->getWebUserInfo($data['userid']) : false;
    $data['status'] = $status;
    
    //обновление даты
    if($status==2){
      $modx->db->update("date=NOW()",$shkm->mod_table,"id='$item_id'");
    }
    
    //сохранение/обновление статистики пользователя
    if($status==3){
      $user_purchase_query = $modx->db->select("setting_value", $shkm->mod_user_table, "webuser = ".$data['userid']." AND setting_name = 'count_purchase'", "", "");
      if($data['userid']){
        if($modx->db->getRecordCount($user_purchase_query)>0){
          $cur_p_stat = explode('/',$modx->db->getValue($user_purchase_query));
          $new_p_stat = count($cur_p_stat)>1 ? ($cur_p_stat[0]+1)."/".($cur_p_stat[1]+$data['price']) : ($cur_p_stat[0]+1)."/".$data['price'] ;          
          $p_result = $modx->db->update("setting_value = '$new_p_stat'", $shkm->mod_user_table, "webuser = ".$data['userid']." AND setting_name = 'count_purchase'");
        }else{
          $new_p_stat = "1/".$data['price'];
          $sql_p = "INSERT INTO `$shkm->mod_user_table` VALUES (NULL, '".$data['userid']."','count_purchase','$new_p_stat')";
          $modx->db->query($sql_p);
        }
      }
      //---
      $p_allowed = $shkm->allowedArray($data['allowed'],$data['purchases']);
      $shkm->updateInventory($data['purchases'],$p_allowed,$conf_inventory);
      $modx->clearCache();
      $update_arr = array_merge($update_arr,array("sentdate"=>date('Y-m-d H:i:s')));
    }
    
    if($conf_informing1){
      $email = !empty($data['email']) ? $data['email'] : ($modx_webuser!=false ? $modx_webuser['email'] : false);
      switch($status){
        case '2':
         $mail_template = $conf_tpl_mail_status;
        break;
        case '3':
         $mail_template = $conf_tpl_mail_shipped;
        break;
        case '5':
         $mail_template = $conf_tpl_mail_canceled;
        break;
      }
      if($email!==false){
        if($status==2){//run plugin
          $evtOut = $modx->invokeEvent('OnSHKMailApprovedForPayment',array('order_id'=>$item_id,'price'=>$data['price'],'currency'=>$data['currency'],'status'=>$status,'userid'=>$data['userid']));
          if (is_array($evtOut)) $data['plugin'] = implode('', $evtOut);
          unset($evtOut);
        }
        $shkm->status_sendMail($site_name.". ".$langTxt['mail_subject'],$email,$data,$mail_template);
        $qs_notify .= "&notify[]=notify_email_changestatus";
      }
    }
  }
  
  
  $change_status = $modx->db->update($update_arr, $shkm->mod_table, "id = $item_id");

  $evtOut = $modx->invokeEvent('OnSHKChangeStatus',array('order_id'=>$item_id,'status'=>$status));
  if (is_array($evtOut)) echo implode('', $evtOut);
  unset($evtOut);

  $modx->sendRedirect($mod_page.$qs_notify,0,"REDIRECT_HEADER");
break;

//Изменение статуса отмеченных заказов
case 'status_all':
  if(!$modx->hasPermission('save_document')){
    global $e;
    $e->setError(3);
    $e->dumpError();
    exit;
  }
  $shkm->changeStatusAll($_POST);
  $modx->sendRedirect($mod_page,0,"REDIRECT_HEADER");
break;

//Обновление страницы
case 'reload':
  $modx->sendRedirect($mod_page,0,"REDIRECT_HEADER");
break;

//Вывод подробностей заказа
case 'show_descr':
    include "templates/header.tpl.php";
    list($data,$orderDataList,$user_stat,$p_allowed,$plugin) = $shkm->showOrderData();
    if($shkm->is_serialized($data["short_txt"])){
        $contact_fields = unserialize($data["short_txt"]);
        $contactsInfo = $shkm->renderContactInfo($contact_fields,$conf_template,true); 
    }else{
        $contactsInfo = $data["short_txt"];
    }    
    include "templates/description.tpl.php";
break;


//Сохранение дфнных заказа
case 'save_purchases':
    if(!$modx->hasPermission('save_document')){
  		global $e;
      $e->setError(3);
  		$e->dumpError();
      exit;
  	}
    $shkm->saveOrderData($_POST);
    $redirect = !empty($_POST['item_val']) ? $mod_page."&action=status&item_val=2&item_id=".$_POST['item_id'] : $mod_page."&action=show_descr&item_id=".$_POST['item_id'];
    $modx->sendRedirect($redirect,0,"REDIRECT_HEADER");
break;



//Экспорт заказов в CSV
case 'csv_export':
    $shkm->csvExport($conf_perpage);
break;


//Страница модуля
default:
  
  include "templates/header.tpl.php";
  
  if ($installed == 0){
    
    echo '<br /><ul class="actionButtons"><li><a href="'.$mod_page.'&action=install">'.$langTxt['install'].'</a></li></ul>';

  }else{
    
    $search_orderid = isset($_GET['search_orderid']) ? $modx->db->escape(rawurldecode(trim($_GET['search_orderid']))) : '';
    $search_username = isset($_GET['search_username']) ? $modx->db->escape(rawurldecode(trim($_GET['search_username']))) : '';
    $search_userid = isset($_GET['search_userid']) ? $modx->db->escape(trim($_GET['search_userid'])) : '';
    $search_status = isset($_GET['search_status']) ? $modx->db->escape(rawurldecode(trim($_GET['search_status']))) : '';
    $search_date = isset($_GET['search_date']) ? $modx->db->escape(rawurldecode(trim($_GET['search_date']))) : '';
    
    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $pnum = $conf_perpage;
    $start = (($page-1)*$pnum+1)-1;
    
    $data_q = " FROM {$shkm->mod_table} shk ";
    if($search_username){
        $data_q .= " LEFT JOIN {$dbprefix}web_user_attributes wua ON wua.internalKey = shk.userid ";
    }
    
    if($search_orderid){
      
      $data_q .= " WHERE shk.id = '$search_orderid' ";
      
    }else{
      
      $data_q .= " WHERE shk.id <> 0 ";
      
      if($search_username){
          $data_q .= " AND (wua.state LIKE '%{$search_username}%' OR wua.fullname LIKE '%{$search_username}%' OR wua.email LIKE '%{$search_username}%')";
      }
      if($search_status){
          $data_q .= " AND shk.status = '{$search_status}' ";
      }
      if($search_date){
          $search_date_arr = explode('.',$search_date);
          $search_day = is_numeric($search_date_arr[0]) ? $search_date_arr[0] : date('d');
          $search_month = isset($search_date_arr[1]) && is_numeric($search_date_arr[1]) ? $search_date_arr[1] : date('m');
          $search_year = isset($search_date_arr[2]) && is_numeric($search_date_arr[2]) ? $search_date_arr[2] : date('Y');
          $data_q .= " AND DAY(shk.date) = '$search_day' AND MONTH(shk.date) = '$search_month' AND YEAR(shk.date) = '$search_year' ";
      }
    
    }
    
    $orderby_q = "
        ORDER BY shk.id DESC
        LIMIT $start, $pnum
    ";
    
    $total = $modx->db->getValue($modx->db->query("SELECT COUNT(DISTINCT shk.id) ".$data_q));
    
    if($total==$conf_perpage) $page = 1;
    
    //верхние кнопки
    echo '
      <div style="width:100%; height:30px;">
        <div style="width:200px;float:left;">
            <ul class="actionButtons">
                <li><a href="'.$mod_page.'"><img src="'.SHOPKEEPER_PATH.'style/default/img/refresh.png" alt="">&nbsp; '.$langTxt['refresh'].'</a></li>
            </ul>
        </div>
        <div align="right">
    ';

    $top_nav_plugin = '';
    $evtOut = $modx->invokeEvent('OnSHKmodRenderTopLinks');
    if (is_array($evtOut)) $top_nav_plugin = implode('', $evtOut);
    unset($evtOut);

    echo $shkm->renderButtons($mod_links,$top_nav_plugin);

    echo '
        </div>
      </div>
    ';

    if($total>0){

        //постраничная разбивка
        $page_href = $mod_page;
        if($search_username) $page_href .= "&amp;search_username=".$search_username;
        if($search_status) $page_href .= "&amp;search_status=".$search_status;
        if($search_date) $page_href .= "&amp;search_date=".$search_date;
        $pg = new pagination;
        $pg->Items($total);
        $pg->limit($pnum);
        $pg->target($page_href);
        $pg->currentPage($page);
        $pg->nextT = ' <a href="[+link+]">'.$langTxt['next'].'</a> ';
        $pg->prevT = ' <a href="[+link+]">'.$langTxt['prev'].'</a> ';
        $pager = $pg->getOutput();

        //Данные заказов
        $data_query = $modx->db->query("SELECT shk.id, shk.short_txt, shk.price, shk.currency, shk.note, shk.status, shk.userid, DATE_FORMAT(shk.date,'%d.%m.%Y %k:%i') AS date, DATE_FORMAT(shk.sentdate,'%d.%m.%Y %k:%i') AS sentdate ".$data_q.$orderby_q);
        //$data_query = $modx->db->select("id, short_txt, price, currency, note, status, userid, DATE_FORMAT(date,'%d.%m.%Y %k:%i') AS date, DATE_FORMAT(sentdate,'%d.%m.%Y %k:%i') AS sentdate", $shkm->mod_table, "", "id DESC", "$start,$pnum");

        //Данные пользователей
        $userData_query = $modx->db->select("DISTINCT wu.id, wu.username", $dbprefix."web_users wu, $shkm->mod_table shk", "wu.id = shk.userid", "", "");
        $users_id_list = "0";
        while ($userData = mysql_fetch_row($userData_query)){
          $userName[$userData[0]] = $userData[1];
          $users_id_list .= ",".$userData[0];
        }
        //Число заказов пользователя
        $users_all_purchase = array();
        $user_purchase_query = $modx->db->select("webuser, setting_value", $shkm->mod_user_table, "setting_name = 'count_purchase' AND webuser in($users_id_list)", "", "");
        while ($user_purchase = mysql_fetch_row($user_purchase_query)){
          $users_all_purchase[$user_purchase[0]] = strpos($user_purchase[1],'/')!== false ? explode('/',$user_purchase[1]) : array($user_purchase[1],0);
        }

    }
    
    include "templates/mainpage.tpl.php";

  }
break;
} 

//Показ информационных сообщений
unset($value);
if(count($notify)>0 || isset($_GET['notify'])){
  echo '<div id="notifyBlock">';
  echo '<h3>'.$langTxt['notify_title'].'</h3>';
  foreach($notify as $value){
    echo "<p>&bull; $value</p>";
  }
  unset($value);
  if(isset($_GET['notify']) && is_array($_GET['notify'])){
    foreach($_GET['notify'] as $value){
      if(isset($langTxt[$value]))
        echo "<p>&bull; ".$langTxt[$value]."</p>";
      else
        echo "<p>&bull; $value</p>";
    }
    unset($value);
  }
  echo '</div>';
}

echo "</div>\n</body>\n</html>";


if(!isset($_SESSION['mod_loaded']))
    $_SESSION['mod_loaded'] = strtotime("now");

if(strtotime("now")-$_SESSION['mod_loaded']>6*3600)
    unset($_SESSION['mod_loaded']);



?>