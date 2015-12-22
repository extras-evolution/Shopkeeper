<?php

/*

Плагин seOrderStat

Статистика заказов

Код плагина:
require MODX_BASE_PATH.'assets/plugins/shk_orderstat/se_order_stat_plugin.php';

События:
OnWebPagePrerender, OnSHKsaveOrder

$this->modx->invokeEvent('OnSHKsaveOrder', array('id' => $order_id,'purchases' => $curSavedP));

*/

$e = &$modx->Event;

$output = "";

switch($e->name) {
  case 'OnWebPagePrerender':
    
    $referer = isset($_SERVER['HTTP_REFERER']) ? urldecode($_SERVER['HTTP_REFERER']) : '';
    $referer = str_replace('&amp;','&',$referer);
    
    if(strpos($referer,'yandex')!==false || strpos($referer,'google')!==false){
      
      $_SESSION['stat_referer'] = $referer;
      
    }
    
    $e->output($output);
    
  break;
  
  case 'OnSHKsaveOrder':
    
    $referer = isset($_SESSION['stat_referer']) ? $_SESSION['stat_referer'] : '';
    
    if(strpos($referer,'yandex')!==false || strpos($referer,'google')!==false){
      
      $dbname = $modx->db->config['dbase'];
      $dbprefix = $modx->db->config['table_prefix'];
      
      $order_id = isset($id) ? $id : 0;
      $user_id = $modx->getLoginUserID();
      if(!$user_id) $user_id = 0;
      $se_words = '';
      $se_name = strpos($referer,'yandex')!==false ? 'yandex' : 'google';
      
      if($se_name=='yandex'){
          preg_match('@^(?:http://.+[\?&]text=)?([^$&]+)@i', $referer, $matches);
          $se_words = end($matches);
          $referer = 'http://yandex.ru/yandsearch?text='.$se_words;
      }else if($se_name=='google'){
          preg_match('@^(?:http://.+[\?&](as_q|q)=)?([^$&]+)@i', $referer, $matches);
          $se_words = end($matches);
          $referer = 'http://www.google.ru/search?q='.$se_words;
      }
      
      if ($modx->db->getRecordCount($modx->db->query("show tables from {$dbname} like '{$dbprefix}se_order_stat'"))==0){
        $modx->db->query("CREATE TABLE IF NOT EXISTS `{$dbprefix}se_order_stat` (`id` int(11) NOT NULL AUTO_INCREMENT, `link` varchar(255) NOT NULL, `word` varchar(255) NOT NULL, `date` date NOT NULL, `stat` int(11) NOT NULL, `orderid` int(11) NOT NULL, `userid` int(11) NOT NULL, PRIMARY KEY (`id`))");
      }
      
      $insertArr = array(
          'link' => $referer,
          'word' => $se_words,
          'date' => date("Y-m-d"),
          'stat' => '1',
          'orderid' => $order_id,
          'userid' => $user_id
      );
      $modx->db->insert($insertArr,$modx->getFullTableName('se_order_stat'));
      
      //$_SERVER['HTTP_REFERER'] = '';
      //unset($_SERVER['HTTP_REFERER']);
      
    }
    
    $e->output($output);
    
  break;
  
}



?>