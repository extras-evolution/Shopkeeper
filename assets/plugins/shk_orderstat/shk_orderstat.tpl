//<?php
/**
 * SHKorderStat
 *
 * Statistics orders
 *
 * @category    plugin
 * @version     1.0
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @package     modx
 * @author      Andchir
 * @internal    @properties
 * @internal    @events OnSHKmodRenderTopLinks,OnSHKmodPagePrint
 * @internal    @modx_category Shop
 */

defined('IN_MANAGER_MODE') or die();

$mod_name = 'Статистика заказов';
$mod_page = "index.php?a=112&id=".$_GET['id'];

$e = &$modx->Event;
$output = "";

if ($e->name == 'OnSHKmodRenderTopLinks') {
  
  $output .= '<li><a href="'.$mod_page.'&amp;action=plugin&amp;pname=orderstat"><img src="../assets/plugins/shk_orderstat/img/chart-pie-separate.png"> '.$mod_name.'</a></li>';

}

if ($e->name == 'OnSHKmodPagePrint') {
  
  if(isset($_GET['pname']) && $_GET['pname']=='orderstat'){
    
    ob_start();
    
    require MODX_BASE_PATH.'assets/plugins/shk_orderstat/shk_orderstat.inc.php';
    
    $output = ob_get_contents();
    
    ob_end_clean();
  
  }

}

$e->output($output);

//?>