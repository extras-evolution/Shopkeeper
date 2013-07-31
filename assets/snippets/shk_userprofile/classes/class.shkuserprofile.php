<?php

/**
 * SHKuserprofile class
 *
 * @name SHKuserprofile
 * @version 1.4.4
 * @author Andchir <andchir@gmail.com>
 */

class SHKuserprofile extends Shopkeeper {

    public $langTxt = array();
    public $modx = null;

/**
 *
 *
 */
function __construct(&$modx, $config){

    parent::__construct($modx);

    $this->config = array_merge(array(
        "lang" => "russian-UTF-8",
        "oneLevelMenu" => false,
        "id_prefix" => ""
    ),$config);

}

/**
 * Возвращает HTML-код чанка
 * 
 * @param string $tpl
 * @return string
 */
function fetchTpl($tpl){
    $template = "";
    if(substr($tpl, 0, 6) == "@FILE:"){
      $tpl_file = MODX_BASE_PATH . trim(substr($tpl, 6));
            $template = file_get_contents($tpl_file);
    }else if(substr($tpl, 0, 6) == "@CODE:"){
            $template = trim(substr($tpl, 6));
    }else if($this->modx->getChunk($tpl) != ""){
            $template = $this->modx->getChunk(trim($tpl));
    }else{
            $template = false;
    }
    return $template;
}

/**
 * Подготовливает плейсхолдеры для второго уровня меню и создает его HTML-код
 * 
 * @param array $menuChunkArr
 * @return string|array
 */
function createSubmenu($menuChunkArr){
    if($this->config['oneLevelMenu'] != 'true'){
        $menu_active = isset($_GET[$this->config['id_prefix'].'sub']) ? $_GET[$this->config['id_prefix'].'sub'] : 'curorders';
    }else{
        $menu_active = isset($_GET[$this->config['id_prefix'].'sub']) ? $_GET[$this->config['id_prefix'].'sub'] : (isset($_GET[$this->config['id_prefix'].'act']) && $_GET[$this->config['id_prefix'].'act']=='purchase' ? 'curorders' : '');
    }
    $menuChunkArr = array(
        array(
            'name' => $this->langTxt['current_orders'],
            'link' => $this->config['thisPage'].$this->config['qs'].$this->config['id_prefix']."act=purchase&amp;".$this->config['id_prefix']."sub=curorders",
            'active' => $menu_active=='curorders' || (!isset($_GET['sub']) && !isset($_GET['act'])) ? 'true' : ''
        ),
        array(
            'name' => $this->langTxt['archive_orders'],
            'link' =>  $this->config['thisPage'].$this->config['qs'].$this->config['id_prefix']."act=purchase&amp;".$this->config['id_prefix']."sub=archorders",
            'active' => $menu_active=='archorders' ? 'true' : ''
        )
    );
    if($this->config['oneLevelMenu'] != 'true'){
        $menuChunk = $this->fetchTpl($this->config['subMenuTpl']);
        $output = $this->renderMenu($menuChunkArr,$menuChunk);
    }else{
        $output = $menuChunkArr;
    }
    return $output;
}

/**
 * Подготовливает плейсхолдеры для первого уровня меню и создает его HTML-код
 * 
 * @param string|array $subMenu
 * @return string
 */
function createMenu($subMenu){
    $menu_active = isset($_GET[$this->config['id_prefix'].'act']) ? $_GET[$this->config['id_prefix'].'act'] : 'purchase';
    $menuChunkArr = array(
        array(
            'name' => $this->langTxt['purchase'],
            'link' => $this->config['thisPage'],
            'active' => $menu_active=='purchase' ? 'true' : '',
            'submenu' => $menu_active=='purchase' ? $subMenu : ''
        ),
        array(
            'name' => $this->langTxt['profile'],
            'link' => $this->config['thisPage'].$this->config['qs'].$this->config['id_prefix']."act=profile",
            'active' => $menu_active=='profile' ? 'true' : '',
            'submenu' => ''
        )/*,
        array(
            'name' => $this->langTxt['logout'],
            'link' => $this->config['thisPage'].$this->config['qs']."act=logout",
            'active' => '',
            'submenu' => ''
        )*/
    );
    $menuChunk = $this->fetchTpl($this->config['menuTpl']);
    $output = $this->config['oneLevelMenu'] != 'true' ? $this->renderMenu($menuChunkArr,$menuChunk) : $this->renderMenu(array_merge(is_array($subMenu) ? $subMenu : array(),$menuChunkArr),$menuChunk,2);
    return $output;
}


/**
 * Создает HTML-код меню по шаблону
 *
 * @param array $menuChunkArr
 * @param string $tpl
 * @param boolean $unset
 * @return string
 */
function renderMenu($menuChunkArr,$tpl,$unset=false){
  $menuRow = '';
  $menuRowChunk = preg_split('/(\[\+loop\+\]|\[\+end_loop\+\])/s', $tpl);
  if($unset!==false) unset($menuChunkArr[$unset]);
  foreach ($menuChunkArr as $key => $value){
    $rowChunk = $menuRowChunk[1];
    $this->phx->placeholders = array();
    $this->setPlaceholders($value);
    $menuRow .= $this->phx->Parse($rowChunk);
  }
  return $menuRowChunk[0].$menuRow.$menuRowChunk[2];
}


/**
 * Возвращает массив разрешенных к заказу товаров
 *
 * @param string $allowed
 * @param array $purchases
 * @return array
 */
function allowedArray($allowed,$purchases){
  if(empty($allowed) && $allowed!='0'){
    $o_allowed = array();
  }elseif($allowed=='all'){
    $o_allowed = array();
    foreach ($purchases as $i => $arr) {
      $o_allowed = array_merge($o_allowed,array($i));
    }
    unset($arr);
  }else{
    $o_allowed = explode(',',$allowed);
  }
  return $o_allowed;
}


/**
 * Отправка нового пароля пользователю на эл. почту
 *
 * @param string $email
 * @param string $uid
 * @param string $pwd
 * @param string $ufn
 * @return boolean
 */
function sendNewPassword($email,$uid,$pwd,$ufn){
    $mailto = $this->modx->config['mailto'];
    $websignupemail_message = $this->modx->config['websignupemail_message'];
    $emailsubject = $this->modx->config['emailsubject'];
    $emailsender = $this->modx->config['emailsender'];
    $site_url = $this->modx->config['site_url'];
    $site_name = $this->modx->config['site_name'];
    $site_start = $this->modx->config['site_start'];
    $charset = $this->modx->config['modx_charset'];
    $message = sprintf($websignupemail_message, $uid, $pwd);
    // replace placeholders
    $message = str_replace("[+uid+]",$uid,$message);
    $message = str_replace("[+pwd+]",$pwd,$message);
    $message = str_replace("[+ufn+]",$ufn,$message);
    $message = str_replace("[+sname+]",$site_name,$message);
    $message = str_replace("[+semail+]",$emailsender,$message);
    $message = str_replace("[+surl+]",$site_url,$message);
    require_once(MODX_MANAGER_PATH . "includes/controls/class.phpmailer.php");
    $mail = new PHPMailer();
    $mail->IsMail();
    $mail->IsHTML(false);
    $mail->CharSet = $charset;
    $mail->From	= $emailsender;
    $mail->FromName = $site_name;
    $mail->Subject = $emailsubject;
    $mail->Body	= $message;
    $mail->AddAddress($email);
    if(!$mail->send()){
      echo $mail->ErrorInfo;
      exit;
    }
    return true;
}



/**
 * Возвращает HTML-код подробностей заказа
 *
 * @param int $orderId
 * @param int $userId
 * @return string
 */
function showOrderDesc($orderId, $userId=0){
  $output = '';

  $chunk = preg_split('/(\[\+loop\+\]|\[\+end_loop\+\])/s',$this->fetchTpl($this->config['orderDescTpl']));
  $list_chunk = $chunk[1];
  $output_list = '';
  $payment_snippet = '';

  //если незарегистрированный пользователь
  if(isset($_GET['pay'])){
    $snippet_q = $this->modx->db->select('id',$modx->getFullTableName("site_snippets"),"name='Paykeeper'");
    $payment_snippet = $this->modx->db->getRecordCount($snippet_q)>0 ? $this->modx->runSnippet(
      'Paykeeper',
      array_merge($this->config['pay_params'])
    )
    : '';
    $orderId = isset($this->modx->placeholders['SHKorderid']) ? $this->modx->placeholders['SHKorderid'] : 0;
  }

  $query = $this->modx->db->select("id, short_txt, content, allowed, addit, price, currency, DATE_FORMAT(date,'%d.%m.%Y %k:%i') AS date, note, status, email, phone, payment, tracking_num,  userid", $this->modx->getFullTableName('manager_shopkeeper'), "id = '$orderId' AND userid = '$userId'", "", "");

  if($this->modx->db->getRecordCount($query)){

    $data = $this->modx->db->getRow($query);

    $purchases = unserialize($data['content']);
    $addit_params = unserialize($data['addit']);
    $p_allowed = $this->allowedArray($data['allowed'],$purchases);
    $this->config['orderDataTpl'] = $this->config['orderDescTpl'];
    $this->config['additDataTpl'] = $this->config['additDataTpl'];
    $orderData = $this->getStuffList($purchases,$addit_params,'list',$p_allowed);

    if(empty($payment_snippet)){
      if($data['status']!=2){
        $payment_snippet = '';
      }else{
        $snippet_q = $this->modx->db->select('id',$this->modx->getFullTableName("site_snippets"),"name='Paykeeper'");
        $p_params = array(
          'payment_value' => $data['price'],
          'payment_orderid' => $data['id'],
          'payment_currency' => $data['currency'],
          'payment_userid' => $userId,
          'payment_useremail' => $this->modx_webuser['email']
        );
        $payment_snippet = $this->modx->db->getRecordCount($snippet_q)>0 ? $this->modx->runSnippet(
          'Paykeeper',
          array_merge($p_params,$this->config['pay_params'])
        )
        : '';
      }
    }
    
    //контактная информация
    if($this->is_serialized($data["short_txt"])){
        $contact_fields = unserialize($data["short_txt"]);
        $conf_template = $this->modx->db->getValue($this->modx->db->select("value", $this->modx->getFullTableName('manager_shopkeeper_config'), "setting = 'conf_template'"));
        $contactsInfo = $conf_template;
        foreach ($contact_fields as $key => $val){
          $contactsInfo = str_replace('[+'.$key.'+]',$val,$contactsInfo);
        }
        unset($key,$val,$contact_fields);
        $contactsInfo = preg_replace('/(\[\+(.*?)\+\]|\s,)/', "", $contactsInfo);
    }else{
        $contactsInfo = $data["short_txt"];
    }
    
    $this->phx->placeholders = array();
    unset($this->modx->placeholders['totalPrice'],$this->modx->placeholders['totalItems']);
    
    $this->setPlaceholders(array(
      'title' => $title,
      //'orderData' => $orderData,
      'id' => $data['id'],
      'date' => $data['date'],
      'totalPrice' => $this->numberFormat($data['price']),
      'currency' => $data['currency'],
      'payment' => $data['payment'],
      'tracking_num' => $data['tracking_num'],
      'status' => $this->langTxt['phase'.$data['status']],
      'phaseColor' => $this->config['phaseColor'][$data['status']-1],
      'contact' => $contactsInfo,
      'backLink' => $this->config['action']=='archorders' ? $this->config['thisPage'].$this->config['qs'].$this->config['id_prefix']."act=purchase&amp;".$this->config['id_prefix']."sub=archorders" : $this->config['thisPage'].$this->config['qs'].$this->config['id_prefix']."act=purchase",
      'payment_snippet' => $payment_snippet,
      'cancel_allowed' => in_array($data['status'],array(1,2)) ? 'true' : '',
      'refresh_allowed' => in_array($data['status'],array(4,5)) ? 'true' : '',
      'list' => $orderData
    ));
    $this->setPlaceholders($this->modx->placeholders);
    $output .= $this->phx->Parse($chunk[0].'[+list+]'.$chunk[2]);
    $output = $this->cleanPHx($output);
  }

  return $output;

}

/**
 * Дублирует заказ и назначает статус "Новый"
 *
 * @param int $orderId
 * @param int $userId
 * @return boolean|int
 */
function refreshOrder($orderId,$userId){
    if(!$orderId || !is_numeric($userId)) return false;
    $update_where = "id = '$orderId' AND userid = '$userId'";
    $update_purchases_result = $this->modx->db->select("*", $this->modx->getFullTableName('manager_shopkeeper'), $update_where);
    $update_purchases = $this->modx->db->getRow($update_purchases_result);
    $purchases = unserialize($update_purchases['content']);
    $addit_params = unserialize($update_purchases['addit']);
    list($totalItems,$totalPrice) = $this->getTotal($purchases,$addit_params,false);

    $keys = array_keys($update_purchases);
    $insert_query = "INSERT INTO ".$this->modx->getFullTableName('manager_shopkeeper')." (".implode(",", $keys).") VALUES(NULL";
    unset($update_purchases['id']);
    $update_purchases['status'] = '1';
    $update_purchases['allowed'] = 'all';
    $update_purchases['price'] = $totalPrice;
    $update_purchases['date'] = date('Y-m-d H:i:s');
    foreach($update_purchases as $key => $value){
        $insert_query .= ",'".$this->modx->db->escape($value)."'";
    }
    unset($keys,$key,$value);
    $insert_query .= ")";

    if(!$this->modx->db->query($insert_query)){
        echo $this->modx->db->getLastError();
        exit;
    }
    return $this->modx->db->getInsertId();
}

/**
 * Возвращает HTML-код списка товаров в заказе
 *
 * @param int $userId
 * @param string $status_list
 * @return string
 */
function getOrderList($userId,$tpl,$start,$status_list='1,2,3,4,5,6'){
    $output = '';
    $thisUrl = $this->config['action']=='archorders' ? $this->config['thisPage'].$this->config['qs'].$this->config['id_prefix']."act=purchase&amp;".$this->config['id_prefix']."sub=archorders" : $this->config['thisPage'].$this->config['qs'].$this->config['id_prefix']."act=purchase&amp;".$this->config['id_prefix']."sub=curorders";
    $data_query = $this->modx->db->select("id, short_txt, content, allowed, addit, price, currency, note, status, DATE_FORMAT(date,'%d.%m.%Y %k:%i') AS date, payment, tracking_num", $this->modx->getFullTableName('manager_shopkeeper'), "userid = '$userId' AND status in($status_list)", "id DESC", "$start, ".$this->config['display']);
    if($this->modx->db->getRecordCount($data_query) > 0){
        while($row = $this->modx->db->getRow($data_query)){
            if($this->config['purchasesListTpl']!='false'){
                $purchases = unserialize($row['content']);
                $addit_params = unserialize($row['addit']);
                $p_allowed = $this->allowedArray($row['allowed'],$purchases);
                $this->config['orderDataTpl'] = $this->config['purchasesListTpl'];
                $this->config['additDataTpl'] = $this->config['additDataTpl'];
                $orderData = $this->getStuffList($purchases,$addit_params,'list',$p_allowed);
            }else{
                $orderData = $this->langTxt['debug_empty_purchasesListTpl'];
            }
            $this->phx->placeholders = array();
            $this->setPlaceholders(array(
                'id' => $row['id'],
                'date' => $row['date'],
                'purchases_list' => $orderData,
                'price' => $row['price'],
                'currency' => $row['currency'],
                'phaseColor' => $this->config['phaseColor'][$row['status']-1],
                'payment' => $row['payment'],
                'tracking_num' => $row['tracking_num'],
                'phaseName' => $this->langTxt['phase'.$row['status']],
                'link' => $thisUrl."&amp;".$this->config['id_prefix']."pid=".$row['id']
            ));
            $output .= $this->phx->Parse($tpl);
            $output = $this->cleanPHx($output);
        }
    }
    return $output;
}



}

?>