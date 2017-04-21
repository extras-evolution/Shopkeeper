//<?php
/**
 * SHKprintOrder
 *
 * Print ordering information
 *
 * @category    plugin
 * @version     1.3.1
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      Andchir
 * @internal    @properties &print_text=Текст кнопки;string;Печать &tpl_path=Папка с шаблонами;string;assets/plugins/SHKprintOrder/chunks/ &print_tpl=Шаблон (чанк);string;@FILE:printOrder.tpl
 * @internal    @events OnSHKOrderDescRender
 * @internal    @modx_category Shop
 */

defined('IN_MANAGER_MODE') or die();

$print_icon = SHOPKEEPER_PATH.'/style/default/img/print.png'; //Иконка
if(!isset($print_text)) $print_text = 'Печать'; //Текст кнопки 
if(!isset($tpl_path)) $tpl_path = 'assets/plugins/SHKprintOrder/chunks/'; //Папка с шаблонами
if(!isset($print_tpl)) $print_tpl = '@FILE:printOrder.tpl'; //Шаблон (чанк)


//Функция для перевода суммы прописью 
require_once MODX_BASE_PATH . 'assets/plugins/SHKprintOrder/num2str.php';
require_once MODX_BASE_PATH . 'assets/snippets/shopkeeper/classes/class.shopkeeper.php';
require_once MODX_BASE_PATH . 'assets/snippets/shopkeeper/classes/class.shk_manager.php';

if(!isset($data)) $data = array();
if(!isset($purchases)) $purchases = array();
if(!isset($addit_params)) $addit_params = array();
if(!isset($p_allowed)) $p_allowed = array();  

$e = &$modx->Event;

$output = "";

if ($e->name == 'OnSHKOrderDescRender'){
  
  $shkm = new SHKmanager($modx);
  $shkm->mod_table = $modx->db->config['table_prefix']."manager_shopkeeper";
  $shkm->mod_config_table = $modx->db->config['table_prefix']."manager_shopkeeper_config";
  $shkm->config['tplPath'] = $tpl_path;
  
  //Кнопка печати
  $output .= '<li><a href="#print" onclick="window.location.href=window.location.pathname+\'?a=112&id='.$_GET['id'].'&action=show_descr&item_id='.$_GET['item_id'].'&print=1\';return false;"><img src="'.$print_icon.'" alt="">&nbsp; '.$print_text.'</a></li>';
  
  //Выводим страницу для печати в новом окне
  if(isset($_GET['item_id']) && isset($_GET['print']) && $_GET['print']==1){
  
  $p_tpl_chunk = preg_split('/(\[\+loop\+\]|\[\+end_loop\+\])/s', $shkm->fetchTpl($print_tpl));
  $total_count = 0;
  
  //Убираем товары, которые нельзя заказать
  foreach($purchases as $key => $value){
    if(is_array($p_allowed) && !in_array($key,$p_allowed))
      unset($purchases[$key]);
    else
      $total_count = $total_count + $value[1];
  }
  unset($key,$value);
  
  $shkm->config['currency'] = $data['currency'];
  $shkm->config['orderDataTpl'] = $print_tpl;
  //Получаем список товаров по шаблону
  $p_tpl_chunk[1] =  $shkm->getStuffList($purchases,$addit_params,'list');
  
  $print_tpl_out = implode('',$p_tpl_chunk);
  $print_tpl_out = addslashes($print_tpl_out);
  
  if($shkm->is_serialized($data["short_txt"])){
      $contact_fields = unserialize($data["short_txt"]);
      $contactsInfo = $shkm->renderContactInfo($contact_fields,$shkm->getConfig('conf_template')); 
  }else{
      $contactsInfo = $data["short_txt"];
  }
  
   //Парсим общий шаблон
  $chunkArr = array(
    'totalPrice' => $data['price'],
    'totalPrice_propis' => num2str($data['price']),
    'currency' => $data['currency'],
    'date' => $data['date'],
    'today' => date("d.m.Y"),
    'contact' => $contactsInfo,
    'site_name' => $modx->config['site_name'],
    'orderID' => $data['id'],
    'total_count' => $total_count,
    'note' => $data['note'],
    'email' => $data['email'],
    'phone' => $data['phone']
  );
  foreach($chunkArr as $key => $val){
    $print_tpl_out = str_replace("[+".$key."+]", $val, $print_tpl_out);
  }
  unset($key,$val);
  
  $print_tpl_out = preg_replace('/[\r\n]+/','',$print_tpl_out);
  
  //Выводим скрипт, который откроет окно с предпросмотром печати
  $output .= <<< OUT
      <script type="text/javascript">
        var newWindow = window.open('', '', 'width=800,height=600,menubar=yes,location=no,toolbar=no');
        if (!newWindow) {
          alert('Пожалуйста, разрешите всплывающие окна в браузере.');
        }
        newWindow.document.write('$print_tpl_out');
        newWindow.focus();
        newWindow.print();
      </script>
OUT;
    
  }
  
  $e->output($output);

}

