<?php

/**
* SHKmanager
*
* Order management class
*
* @author Andchir <andchir@gmail.com>
* @version 1.3.4
*/

class SHKmanager extends Shopkeeper {

    public $lang = array();
    public $dbname = '';
    public $mod_table = '';
    public $mod_config_table = '';
    public $mod_user_table = '';
    public $mod_catalog_table = '';
    public $mod_catalog_tv_table = '';
    public $tab_eventnames = '';
    public $mod_page = '';

    /**
     *
     *
     */
    function __construct(&$modx){

        parent::__construct($modx);
        
    }

    /**
     * Устанавливает модуль (создает таблицы в БД)
     */
    function modInstall(){
        $sql = array();
        $sql[] = "CREATE TABLE IF NOT EXISTS `$this->mod_table` (`id` int(11) NOT NULL auto_increment,`short_txt` text NOT NULL,`content` text,`allowed` varchar(255) NOT NULL,`addit` text,`price` varchar(255) NOT NULL,`currency` varchar(255) NOT NULL,`date` datetime,`sentdate` datetime,`note` text NOT NULL,`email` varchar(255) NOT NULL,`phone` varchar(255) NOT NULL, `payment` VARCHAR(128) NOT NULL, `tracking_num` VARCHAR( 32 ), `status` int(11) NOT NULL,`userid` int(11) NOT NULL,PRIMARY KEY  (`id`));";
        $sql[] = "CREATE TABLE IF NOT EXISTS `$this->mod_config_table` (`id` INT(11) NOT NULL AUTO_INCREMENT, `setting` VARCHAR(255), `value` TEXT, PRIMARY KEY (`id`));";
        $sql[] = "CREATE TABLE IF NOT EXISTS `$this->mod_user_table` (`id` int(11) NOT NULL AUTO_INCREMENT, `webuser` INT(11) NOT NULL, `setting_name` VARCHAR(50) NOT NULL default '', `setting_value` TEXT, PRIMARY KEY (`id`));";
        $sql[] = "CREATE TABLE IF NOT EXISTS `$this->mod_catalog_table` (`id` int(10) NOT NULL AUTO_INCREMENT, `pagetitle` varchar(255) NOT NULL DEFAULT '', `alias` varchar(255) DEFAULT '', `published` int(1) NOT NULL DEFAULT '0', `parent` int(10) NOT NULL DEFAULT '0', `isfolder` int(1) NOT NULL DEFAULT '0', `introtext` text, `content` text, `template` int(10) NOT NULL DEFAULT '1', `menuindex` int(10) NOT NULL DEFAULT '0', `createdon` int(20) NOT NULL DEFAULT '0', `hidemenu` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `id` (`id`), KEY `parent` (`parent`));";
        $sql[] = "CREATE TABLE IF NOT EXISTS `$this->mod_catalog_tv_table` (`id` int(11) NOT NULL AUTO_INCREMENT, `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id', `contentid` int(10) NOT NULL DEFAULT '0' COMMENT 'Site Content Id', `value` text, PRIMARY KEY (`id`), KEY `idx_tmplvarid` (`tmplvarid`), KEY `idx_id` (`contentid`));";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_template', 'Ф.И.О.: [+name+]<br />\r\nадрес: [+address+]<br />\r\nадрес эл. почты: [+email+]<br />\r\nтелефон: [+phone+]<br />\r\nспособ доставки [+delivery+]<br />\r\nспособ оплаты: [+payment+]');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_small_template', '[+name+], [+address+], [+email+], [+phone+], [+delivery+], [+payment+]');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_currency', 'руб.');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_perpage', '20');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_colors', '#C5CAFE~#B1F2FC~#F3FDB0~#BEFAB4~#FFAEAE~#FFE1A4');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_informing1', '1');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_inventory', '');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_phase_days', 2);";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_pricetv', '');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_catalog', '');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_tpl_mail_status', '@FILE:mail_changeStatus.tpl');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_tpl_mail_shipped', '@FILE:mail_shipped.tpl');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_tpl_mail_canceled', '@FILE:mail_changeStatus.tpl');";
        $sql[] = "INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_shk_version', '$this->cur_version');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKChangeStatus', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKFrontendInit', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKOrderDescRender', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKMailApprovedForPayment', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKcartLoad', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKstatusSendMail', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKmodRenderTopLinks', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKmodPagePrint', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKbeforeSendOrder', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKsaveOrder', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKcalcTotalPrice', '6', 'Shopkeeper');";
        $sql[] = "INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKgetProductPrice', '6', 'Shopkeeper');";
        foreach ($sql as $line){
          $this->modx->db->query($line);
        }

    }

    /**
     * Удаляет все данные модуля из БД
     */
    function modUninstall(){
        $sql = array();
        $sql[] = "DROP TABLE IF EXISTS `$this->mod_table`";
        $sql[] = "DROP TABLE IF EXISTS `$this->mod_config_table`";
        $sql[] = "DROP TABLE IF EXISTS `$this->mod_user_table`";
        $sql[] = "DROP TABLE IF EXISTS `$this->mod_catalog_table`";
        $sql[] = "DROP TABLE IF EXISTS `$this->mod_catalog_tv_table`";
        foreach ($sql as $line){
          $this->modx->db->query($line);
        }
        $this->modx->db->delete($this->tab_eventnames, "groupname = 'Shopkeeper'");
    }

    /**
     * Обновляет модуль на новую версию
     */
    function modUpdate(){

        $this->modx->db->delete($this->mod_user_table, "webuser = '0'");

        if($this->cur_version=='0.9.6 beta1'){
          $this->modx->db->delete($this->tab_eventnames, "groupname = 'Shopkeeper'");
        }

        if(!isset($this->cur_version))
          $this->modx->db->query("INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_shk_version', '$this->cur_version');");
        else
          $this->modx->db->update("value = '$this->cur_version'", $this->mod_config_table, "setting = 'conf_shk_version'");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKChangeStatus'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKChangeStatus', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKFrontendInit'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKFrontendInit', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKOrderDescRender'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKOrderDescRender', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKMailApprovedForPayment'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKMailApprovedForPayment', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKcartLoad'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKcartLoad', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKstatusSendMail'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKstatusSendMail', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->mod_config_table, "setting = 'conf_phase_days'"))==0)
          $this->modx->db->query("INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_phase_days', '2');");

        $this->modx->db->query("ALTER TABLE `$this->mod_config_table` CHANGE `value` `value` TEXT NULL DEFAULT NULL");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->mod_config_table, "setting = 'conf_tpl_mail_canceled'"))==0)
          $this->modx->db->query("INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_tpl_mail_canceled', '@FILE:mail_changeStatus.tpl');");

        if(count($this->modx->db->getRow($this->modx->db->select("*",$this->mod_table,"","","1")))==14){
          $this->modx->db->query("ALTER TABLE `$this->mod_table` ADD `payment` VARCHAR(128) NOT NULL AFTER `phone`, ADD `tracking_num` VARCHAR(32) NOT NULL AFTER `payment`");
          $this->modx->db->query("ALTER TABLE `$this->mod_user_table` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`)");
        }

        //1.0
        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->mod_config_table, "setting = 'conf_catalog'"))==0)
          $this->modx->db->query("INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_catalog', '');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->mod_config_table, "setting = 'conf_pricetv'"))==0)
          $this->modx->db->query("INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_pricetv', '');");

        if ($this->modx->db->getRecordCount($this->modx->db->query("show tables from ".$this->dbname." like '".$this->mod_catalog_table."'"))==0){
          $this->modx->db->query("CREATE TABLE IF NOT EXISTS `$this->mod_catalog_table` (`id` int(10) NOT NULL AUTO_INCREMENT, `pagetitle` varchar(255) NOT NULL DEFAULT '', `alias` varchar(255) DEFAULT '', `published` int(1) NOT NULL DEFAULT '0', `parent` int(10) NOT NULL DEFAULT '0', `isfolder` int(1) NOT NULL DEFAULT '0', `introtext` text, `content` text, `template` int(10) NOT NULL DEFAULT '1', `menuindex` int(10) NOT NULL DEFAULT '0', `createdon` int(20) NOT NULL DEFAULT '0', `hidemenu` tinyint(1) NOT NULL DEFAULT '0', PRIMARY KEY (`id`), KEY `id` (`id`), KEY `parent` (`parent`));");
          $this->modx->db->query("CREATE TABLE IF NOT EXISTS `$this->mod_catalog_tv_table` (`id` int(11) NOT NULL AUTO_INCREMENT, `tmplvarid` int(10) NOT NULL DEFAULT '0' COMMENT 'Template Variable id', `contentid` int(10) NOT NULL DEFAULT '0' COMMENT 'Site Content Id', `value` text, PRIMARY KEY (`id`), KEY `idx_tmplvarid` (`tmplvarid`), KEY `idx_id` (`contentid`));");
        }

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKmodRenderTopLinks'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKmodRenderTopLinks', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKmodPagePrint'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKmodPagePrint', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKbeforeSendOrder'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKbeforeSendOrder', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKsaveOrder'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKsaveOrder', '6', 'Shopkeeper');");

        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKcalcTotalPrice'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKcalcTotalPrice', '6', 'Shopkeeper');");
        
        //1.0.1
        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->tab_eventnames, "name = 'OnSHKgetProductPrice'"))==0)
          $this->modx->db->query("INSERT INTO `$this->tab_eventnames` VALUES (NULL, 'OnSHKgetProductPrice', '6', 'Shopkeeper');");
        
        //1.3
        if($this->modx->db->getRecordCount($this->modx->db->select("id", $this->mod_config_table, "setting = 'conf_small_template'"))==0)
          $this->modx->db->query("INSERT INTO `$this->mod_config_table` VALUES (NULL, 'conf_small_template', '[+name+], [+address+], [+email+], [+phone+], [+delivery+], [+payment+]');");
        
        $conf_tpl_mail_status = $this->getConfig('conf_tpl_mail_status');
        if(substr($conf_tpl_mail_status,0,6)!='@FILE:')
            $this->modx->db->update("value = '@FILE:".basename($conf_tpl_mail_status)."'", $this->mod_config_table, "setting = 'conf_tpl_mail_status'");
        
        $conf_tpl_mail_shipped = $this->getConfig('conf_tpl_mail_shipped');
        if(substr($conf_tpl_mail_shipped,0,6)!='@FILE:')
            $this->modx->db->update("value = '@FILE:".basename($conf_tpl_mail_shipped)."'", $this->mod_config_table, "setting = 'conf_tpl_mail_shipped'");
        
        $conf_tpl_mail_canceled = $this->getConfig('conf_tpl_mail_canceled');
        if(substr($conf_tpl_mail_canceled,0,6)!='@FILE:')
            $this->modx->db->update("value = '@FILE:".basename($conf_tpl_mail_canceled)."'", $this->mod_config_table, "setting = 'conf_tpl_mail_canceled'");
        
    }

    /**
     * Вытаскивает полную конфигурацию модуля
     *     
     * @return array
     */
    function getModConfig(){
        $output = array();
        if($this->modx->db->getRecordCount($this->modx->db->query("SHOW TABLES FROM $this->dbname LIKE '$this->mod_table'"))>0){
            $config_query = $this->modx->db->select("*", $this->mod_config_table, "", "", "");
            while($config = $this->modx->db->getRow($config_query)){
              $output[$config['setting']] = $config['value'];
            }
            $output['phaseColor'] = explode("~", $output['conf_colors']);
        }
        return $output;
    }

    /**
     * Вытаскивает отдельный параметр из конфигурации
     *     
     * @param string $name
     * @return string
     */
    function getConfig($name){
        $output = $this->modx->db->getValue($this->modx->db->select("value", $this->mod_config_table, "setting = '{$name}'"));
        return $output;
    }

    /**
     * Сохраняет конфигурацию модуля
     *     
     * @param array $data
     */
    function saveConfig($data){
        $config = array(
          'conf_perpage' => array("value" => $data['perpage']),
          'conf_currency' => array("value" => $data['currency']),
          'conf_inventory' => array("value" => $data['inventory']),
          'conf_catalog' => array("value" => $data['catalog_id']),
          'conf_phase_days' => array("value" => $data['phase_days']),
          'conf_informing1' => isset($data['informing1']) ? array("value" => $data['informing1']) : array("value" => 0),
          'conf_pricetv' => array("value" => $data['pricetv']),
          'conf_colors' => !isset($data['colorDefault']) ? array("value" => implode("~", $data['color'])) : array("value" => "#C5CAFE~#B1F2FC~#F3FDB0~#BEFAB4~#FFAEAE~#FFE1A4"),
          'conf_template' => array("value" => $data['template']),
          'conf_small_template' => array("value" => $data['small_template']),
          'conf_tpl_mail_status' => array("value" => $data['tpl_mail_status']),
          'conf_tpl_mail_shipped' => array("value" => $data['tpl_mail_shipped']),
          'conf_tpl_mail_canceled' => array("value" => $data['tpl_mail_canceled'])
        );
        foreach($config as $key => $value){
          $query = $this->modx->db->update($value, $this->mod_config_table, "setting = '$key'");
        }
    }


    /**
     * Выводит данные заказа
     *      
     * @param array $post
     * @return array
     */
    function showOrderData(){
        $plugin = array();
        $item_id = isset($_POST['item_id']) ? $_POST['item_id'] : $_GET['item_id'];
        $data = $this->modx->db->getRow($this->modx->db->select("id, short_txt, content, allowed, addit, price, currency, DATE_FORMAT(date,'%d.%m.%Y %k:%i') AS date, DATE_FORMAT(sentdate,'%d.%m.%Y %k:%i') AS sentdate, note, status, email, phone, payment, tracking_num,  userid", $this->mod_table, "id = '$item_id'", "", ""));
        $data['purchases'] = unserialize($data['content']);
        $data['addit_params'] = unserialize($data['addit']);
        
        if (empty($data['purchases'][0][3])){
            $p_names = $this->getContentData($data['purchases']);
            foreach($data['purchases'] as $idx => $purchase){
                $isCatalog = (!empty($purchase['catalog']) && $purchase['catalog']) ? 1 : 0;
                $data['purchases'][$idx][3] = $p_names[$isCatalog][$purchase[0]];
            }
        }
        
        $modx_modx_webuser = $data['userid']!=0 ? $this->modx->getWebUserInfo($data['userid']) : false;
        $p_allowed = $this->allowedArray($data['allowed'],$data['purchases']);
        if($data['userid']){
            $user_purchase_query = $this->modx->db->select("setting_value", $this->mod_user_table, "webuser = ".$data['userid']." AND setting_name = 'count_purchase'", "", "");
            $user_stat = $this->modx->db->getRecordCount($user_purchase_query)>0 ? explode('/',$this->modx->db->getValue($user_purchase_query)) : false;
        }else{
            $user_stat = false;
        }
        $data['note'] = str_replace("<br />","\n",preg_replace("/[\r\n]+/",'',$data['note']));
        
        $this->config['orderDataTpl'] = $this->getConfig('conf_tpl_mail_status');
        $orderDataList = $this->getStuffList($data['purchases'],$data['addit_params'],'list',$p_allowed);
        
        $evtOut = $this->modx->invokeEvent('OnSHKOrderDescRender',array('data'=>$data,'purchases'=>$data['purchases'],'addit_params'=>$data['addit_params'],'p_allowed'=>$p_allowed));
        if (is_array($evtOut)) $plugin['OnSHKOrderDescRender'] = implode('', $evtOut);

        return array($data,$orderDataList,$user_stat,$p_allowed,$plugin);
    }

    /**
     * Сохраняет данные заказа
     *      
     * @param array $data
     */
    function saveOrderData($data){
        $purchases = array();
        $addit_params = array();
        $p_allowed = array();
        $c_data = $this->modx->db->getRow($this->modx->db->select("id, status, short_txt, content, addit, DATE_FORMAT(date,'%d.%m.%Y %k:%i') AS date, email, phone, currency, userid", $this->mod_table, "id = ".$data['item_id']."", "", ""));
        $modx_webuser = $c_data['userid']!=0 ? $this->modx->getWebUserInfo($c_data['userid']) : false;
        $c_data['content'] = $this->is_serialized($c_data['content']) ? unserialize($c_data['content']) : array();
        $c_data['addit'] = $this->is_serialized($c_data['addit']) ? unserialize($c_data['addit']) : array();
        $index = -1;
        for($i=0;$i<count($data['p_id']);$i++){
          if(isset($data['delete_'.$i])) continue;
          else $index++;
          
          list($p_id,$p_count,$p_price,$p_name) = array(0=>$data['p_id'][$i],1=>$data['p_count'][$i],2=>$data['p_price'][$i],3=>$data['p_name'][$i]);
          $purchases[$index] = array(
            0=>$p_id,
            1=>$p_count,
            2=>$p_price,
            3=>$p_name,
            'tv'=> isset($c_data['content'][$i]['tv']) ? $c_data['content'][$i]['tv'] : NULL,
            'tv_add'=> isset($c_data['content'][$i]['tv_add']) ? $c_data['content'][$i]['tv_add'] : NULL,
            'catalog'=> isset($c_data['content'][$i]['catalog']) ? $c_data['content'][$i]['catalog'] : NULL
          );
          if(!empty($data['a_name_'.$p_id.'_'.$i])){
            for($ii=0;$ii<count($data['a_name_'.$p_id.'_'.$i]);$ii++){
              if(!empty($data['a_name_'.$p_id.'_'.$i][$ii])){
                $addit_params[$index][$ii] = array($data['a_name_'.$p_id.'_'.$i][$ii],$data['a_price_'.$p_id.'_'.$i][$ii]);
                $a_fieldname = isset($c_data['addit'][$index][$ii][2]) ? $c_data['addit'][$index][$ii][2] : '';
                $addit_params[$index][$ii][2] = $a_fieldname;
                if($a_fieldname && isset($purchases[$index]['tv_add']['shk_'.$a_fieldname])) $purchases[$index]['tv_add']['shk_'.$a_fieldname] = $addit_params[$index][$ii][0];
              }
            }
          }else{
            $addit_params[$index] = array();
          }
          if(isset($data['allow_'.$i])){
            $p_allowed[] = $index;
          }
                                  
        }
        
        //добавить к заказу
        if(!empty($data['add_prod_id']) && !empty($data['add_prod_count'])){
            $add_prod = array();
            $add_prod_id = $this->modx->db->escape($data['add_prod_id']);
            $add_prod[0][0] = $add_prod_id;
            $add_prod[0][1] = $this->modx->db->escape($data['add_prod_count']);
            if(is_numeric($add_prod[0][0]) && is_numeric($add_prod[0][1])){
                $add_prod[0]['catalog'] = isset($purchases[0]['catalog']) ? $purchases[0]['catalog'] : 0;
                $add_prod_names = $this->getContentData($add_prod);
                $add_prod_tv = $this->getTmplVars($add_prod);
                $add_prod[0][2] = !empty($data['add_prod_price']) ? $this->modx->db->escape($data['add_prod_price']) : 0;
                $add_prod[0][3] = isset($add_prod_names[0][$add_prod_id]) ? $add_prod_names[0][$add_prod_id] : '';
                if($add_prod[0][3]){
                  $add_prod[0]['tv'] = isset($add_prod_tv[0][$add_prod_id]) ? $add_prod_tv[0][$add_prod_id] : array();
                  
                  $add_prod_params = array();
                  if(!empty($data['add_prod_params'])){
                      $add_prod_params_arr = explode('||',$this->modx->db->escape($data['add_prod_params']));
                      foreach($add_prod_params_arr as $key => $val){
                          $temp_arr = explode('==',$val);
                          $add_prod_params[$key][0] = $temp_arr[0];
                          $add_prod_params[$key][1] = isset($temp_arr[1]) && is_numeric($temp_arr[1]) ? $temp_arr[1] : 0;
                      }
                  }else{
                      $add_prod_params = array();
                  }
                  
                  array_push($p_allowed,count($purchases));
                  array_push($purchases,$add_prod[0]);
                  array_push($addit_params,$add_prod_params);
                }
            }
        }
        
        //Контактная информация
        $short_txt = '';
        $contact_fields = $this->is_serialized($c_data["short_txt"]) ? unserialize($c_data["short_txt"]) : array();
        $new_contact_fields = array();
        foreach($data as $key => $val){
            if(substr($key,0,6)=='shkct_'){
                $field_name = substr($key,6);
                $new_contact_fields[$field_name] = $val;
            }
        }
        $short_txt = count($new_contact_fields)>0 ? serialize(array_merge($contact_fields,$new_contact_fields)) : $c_data['short_txt'];
        
        list($totalItems,$totalPrice) = $this->getTotal($purchases,$addit_params,$p_allowed);
        
        $fields = array(
           "short_txt" => $this->modx->db->escape($short_txt),//$this->modx->db->escape(nl2br($data['short_txt'])),
           "content" => $this->modx->db->escape(serialize($purchases)),
           "addit" => $this->modx->db->escape(serialize($addit_params)),
           "allowed" => implode(',',$p_allowed),
           "price" => str_replace(',','.',$totalPrice),
           "status" => !empty($data['item_val']) && $data['item_val']==1 ? 2 : $c_data['status'],
           "note" => $this->modx->db->escape(nl2br($data['note'])),
           "email" => $this->modx->db->escape($data['email']),
           "phone" => $this->modx->db->escape($data['phone']),
           "payment" => $this->modx->db->escape($data['payment']),
           "tracking_num" => $this->modx->db->escape($data['tracking_num'])
        );
        $query = $this->modx->db->update($fields, $this->mod_table, "id = ".$data['item_id']);

        $this->modx->invokeEvent('OnSHKsaveOrder', array(
            'id' => $data['item_id'],
            'purchases' => $purchases
        ));

    }
    
    
    /**
    * Создает HTML контактной информации заказа по шаблону
    *     
    * @param array $fields
    * @param string $template   
    */
    function renderContactInfo($fields,$template,$with_fields=false){
        $output = '';
        if(!is_array($fields) || !$template) return $output;
        $output = $template;
        foreach ($fields as $key => $val){
          $ph_value = $with_fields ? '<input class="editable" type="text" name="shkct_'.$key.'" value="'.$val.'" size="35" />' : $val;
          $output = str_replace('[+'.$key.'+]',$ph_value,$output);
        }
        $output = preg_replace('/(\[\+(.*?)\+\]|\s,)/', "", $output);
        return $output;
    }
    
    
    /**
     * Меняет статус у выбранных заказов
     *     
     * @param array $array
     */
    function changeStatusAll($array){
        if(!is_array($array['check'])) return;
        if(isset($array['check'])){
            for($i=0;$i<count($array['check']);$i++){
                $id = $array['check'][$i];
                $fields = $array['item_val']!=2 ? "status = '".$array['item_val']."'" : "status = '".$array['item_val']."', date = NOW()";
                $this->modx->db->update($fields, $this->mod_table, "id = $id");
                
                $evtOut = $this->modx->invokeEvent('OnSHKChangeStatus',array('order_id'=>$id,'status'=>$array['item_val']));
                
            }
        }
    }

    /**
     * Экспорт заказов с CSV-файл
     *     
     * @param int $pnum
     */
    function csvExport($pnum){
        $this->config['orderDataTpl'] = $conf_tpl_mail_status;
        $tmpfname = tempnam(MODX_BASE_PATH."assets/export", "csv");
        
        $page = isset($_GET['dpgndsp1']) ? $_GET['dpgndsp1'] : 1;
        $start = (($page-1)*$pnum+1)-1;
        $data_query = $this->modx->db->select("id, content, addit, allowed, short_txt, price, currency, note, status, userid, DATE_FORMAT(date,'%d.%m.%Y %k:%i') AS date, DATE_FORMAT(sentdate,'%d.%m.%Y %k:%i') AS sentdate", $this->mod_table, "", "id DESC", "$start,$pnum");

        $fp = fopen($tmpfname, 'w');

        while ($row = $this->modx->db->getRow($data_query)){
            $purchases = unserialize($row['content']);
            $addit_params = unserialize($row['addit']);
            $p_names = $this->getContentData($purchases);
            $p_allowed = $this->allowedArray($row['allowed'],$purchases);
            $orderDataList = $this->getStuffList($purchases,$addit_params,'list',$p_allowed);
            $orderDataList = preg_replace('/<s>.+<\/s>/','',$orderDataList);
            $orderDataList = trim(strip_tags($orderDataList),"\t\n\r");
            
            if($this->is_serialized($row["short_txt"])){
                $contact_fields = unserialize($row["short_txt"]);
                $contactsInfo = $this->renderContactInfo($contact_fields,$this->getConfig('conf_template')); 
            }else{
                $contactsInfo = $row["short_txt"];
            }
            $contactsInfo = strip_tags($contactsInfo);
            
            $line = array($row['id'],$row['date'],$orderDataList,$contactsInfo,$row['price'],$row['currency'],$this->langTxt['phase'.$row['status']],$row['note']);
            fputcsv($fp, $line, ';', '"');
        }
        fclose($fp);
        unset($fp);
        header("Content-type: application/text; charset=windows-1251");
        header("Content-Disposition: attachment; filename=\"exp_".date("d.m.y_H.i").".csv\"");

        $fp = fopen($tmpfname, "r");
        $fcontent = fread($fp, filesize($tmpfname));
        fclose($fp);
        @unlink($tmpfname);

        if($this->modx->config['modx_charset'] == "UTF-8"){
            $fcontent = iconv('UTF-8','cp1251',$fcontent);
        }
        echo $fcontent;
        exit;
    }

    /**
     * Удаляет выбранные заказы
     *     
     * @param array $array
     */
    function deleteGroup($array){
        if(isset($array['check'])){
            for($i=0;$i<count($array['check']);$i++){
                $id = $array['check'][$i];
                $this->modx->db->delete($this->mod_table, "id = $id");
            }
        }
    }

    /**
     * Проверяет дату заказа и отменяет его, если заказ просрочен
     *      
     * @param int $conf_phase_days
     * @return int
     */
    function chkOrdersPeriod($conf_phase_days){
        $canceled = 0;
        $chk_select = $this->modx->db->select("id", $this->mod_table, "status = '2' AND date + INTERVAL $conf_phase_days DAY < NOW()");
        if($this->modx->db->getRecordCount($chk_select)>0){
          while($row = $this->modx->db->getRow($chk_select, 'num')){
            $this->modx->db->update("status = '5'",$this->mod_table,"id = '$row[0]'");
            $canceled++;
          }
        }
        return $canceled;
    }

    /**
     * Отправляет эл. письмо
     *     
     * @param string $subject
     * @param string $email
     * @param string $body
     */
    function sendMail($subject,$email,$body){
        $charset = $this->modx->config['modx_charset'];
        $site_name = $this->modx->config['site_name'];
        $adminEmail = $this->modx->config['emailsender'];
        require_once(MODX_MANAGER_PATH . "includes/controls/class.phpmailer.php");
        $mail = new PHPMailer();
        $mail->IsMail();
        $mail->IsHTML(true);
        $mail->CharSet = $charset;
        $mail->From	= $adminEmail;
        $mail->FromName	= $site_name;
        $mail->Subject	= $subject;
        $mail->Body	= $body;
        $mail->AddAddress($email);
        if(!$mail->send()){
          echo $mail->ErrorInfo;
          exit;
        }
    }

    /**
     * Отправляет письмо о смене статуса заказа
     *     
     * @param string $subject
     * @param string $email
     * @param array $data
     * @param string $template
     * @return string
     */
    function status_sendMail($subject,$email,$data,$template){
        if(!$template) return;
        $this->config['orderDataTpl'] = $template;
        $this->config['additDataTpl'] = '@FILE:additData.tpl';

        $p_allowed = $this->allowedArray($data['allowed'],$data['purchases']);
        $status = 'phase'.$data['status'];
        $statusName = $this->langTxt[$status];

        if(!isset($this->modx->placeholders))
          $this->modx->placeholders = array();

        //run plugin
        $evtOut = $this->modx->invokeEvent('OnSHKstatusSendMail',array('data'=>$data));
        
        if($this->is_serialized($data["short_txt"])){
            $contact_fields = unserialize($data["short_txt"]);
            $contactsInfo = $this->renderContactInfo($contact_fields,$this->getConfig('conf_template')); 
        }else{
            $contactsInfo = $data["short_txt"];
        }

        $chunkArr = array(
          'totalPrice' => $data['price'],
          'currency' => $data['currency'],
          'status' => $statusName,
          'date' => $data['date'],
          'contact' => $contactsInfo,//$data['short_txt'],
          'site_name' => $this->modx->config['site_name'],
          'orderID' => $data['id'],
          'tracking_num' => $data['tracking_num'],
          'order_changed_txt' => count($data['purchases']) > count($p_allowed) && count($p_allowed)>0 ? $this->langTxt['order_changed'] : '',
          'order_notpossible_txt' => count($p_allowed)==0 ? $this->langTxt['order_notpossible'] : '',
          'plugin' => isset($data['plugin']) ? $data['plugin'] : ''
        );
        $orderDataList = $this->getStuffList($data['purchases'],$data['addit'],'list',$p_allowed);
        $mainChunk = $this->fetchTpl($this->config['orderDataTpl']);
        $rowChunk = preg_split('/(\[\+loop\+\]|\[\+end_loop\+\])/s', $mainChunk);
        $chunk = $rowChunk[0].$orderDataList.$rowChunk[2];
        foreach (array_merge($chunkArr,$this->modx->placeholders) as $key => $value){
          $chunk = str_replace("[+".$key."+]", $value, $chunk);
        }
        /*
        if(count($data['purchases'])!=count($p_allowed)){
          $mail_body = preg_replace('/(\[\+if_changed\+\]|\[\+end_if\+\])/s','',$chunk);
        }else{
          $mail_body = preg_replace('/(\[\+if_changed\+\].*\[\+end_if\+\])/s','',$chunk);
        }
        */
        $mail_body = $chunk;
        $this->sendMail($subject,$email,$mail_body);
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
     * Меняет цену у товара в каталоге
     *     
     * @param array $prodIdArr
     * @param array $prodPriceArr
     * @param <int $tvId
     * @return null
     */
    function changePrice($prodIdArr,$prodPriceArr,$tvId){

        if(!is_array($prodIdArr) || !is_array($prodPriceArr) || !$tvId) return;

        foreach($prodIdArr as $key => $prod_id){
            $price = isset($prodPriceArr[$key]) ? $prodPriceArr[$key] : 0;
            if($this->modx->db->getValue($this->modx->db->select("COUNT(*)",$this->mod_catalog_tv_table,"contentid = '$prod_id' AND tmplvarid = '$tvId'"))){
                $this->modx->db->update(array("value"=>$price),$this->mod_catalog_tv_table,"contentid = '$prod_id' AND tmplvarid = '$tvId'");
            }else{
                $this->modx->db->insert(array("tmplvarid"=>$tvId,"contentid"=>$prod_id,"value"=>$price),$this->mod_catalog_tv_table);
            }
        }

    }

    
    /**
     * Генерирует кнопки модуля
     *     
     * @param array $array
     * @return string
     */
    function renderButtons($array,$plugin=''){
        $output = '<ul class="actionButtons">'."\n".$plugin;
        foreach($array as $val){
            $output .= '<li><a href="'.$this->mod_page.'&action='.$val[2].'">'.($val[1] ? '<img src="'.$val[1].'">&nbsp; ' : '').$val[0].'</a></li>';
        }
        return $output."\n</ul>";
    }
    

}


?>