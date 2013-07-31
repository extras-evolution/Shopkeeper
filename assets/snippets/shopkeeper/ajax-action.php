<?php

include_once(dirname(__FILE__)."/../../cache/siteManager.php");
require_once(dirname(__FILE__).'/../../../'.MGR_DIR.'/includes/protect.inc.php');

//if(!in_array(strpos($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST']),array(7,8))) exit;

if(empty($_SERVER['HTTP_REFERER'])) exit;

$request = $_POST;

function addslashes_array($input_arr){
  if(is_array($input_arr)){
    $tmp = array();
    foreach ($input_arr as $key1 => $val){
      $tmp[$key1] = addslashes_array($val);
    }
    return $tmp;
  }else{
    return addslashes($input_arr);
  }
}

if(!get_magic_quotes_gpc()){
  foreach ($request as $key => $value){
    $request[$key] = addslashes_array($value);
  }
  unset($key,$value);
}

define('MODX_MANAGER_PATH', "../../../".MGR_DIR."/");
require_once(MODX_MANAGER_PATH . 'includes/config.inc.php');
require_once(MODX_MANAGER_PATH . '/includes/protect.inc.php');
define('MODX_API_MODE', true);
require_once(MODX_MANAGER_PATH.'/includes/document.parser.class.inc.php');

session_name($site_sessionname);
session_id($_COOKIE[session_name()]);
session_start();

$modx = new DocumentParser;
$modx->db->connect();
$modx->getSettings();
$modx->config['site_url'] = isset($request['site_url']) ? $request['site_url'] : '';

$manager_language = $modx->config['manager_language'];
$charset = $modx->config['modx_charset'];
$dbname = $modx->db->config['dbase'];
$base_dir = $modx->config['rb_base_dir'];
$dbprefix = $modx->db->config['table_prefix'];
$mod_table = $dbprefix."manager_shopkeeper";
$mod_config_table = $dbprefix."manager_shopkeeper_config";

if($charset=="UTF-8"){
  header('Content-Type: text/html; charset=utf-8');
}elseif($charset=="windows-1251"){
  header('Content-Type: text/html; charset=windows-1251');
}

define('SHOPKEEPER_PATH', "snippets/shopkeeper/");
require_once "classes/class.shopkeeper.php";

function str2bool($str){
  return $str && $str!='false' ? true : false;
}

if(isset($request['action'])){
  
  //параметры по умолчанию
  $snippetProp = $modx->db->getValue($modx->db->select("properties",$modx->getFullTablename('site_snippets'),"name = 'Shopkeeper'"));
  $defaultProp = $modx->parseProperties($snippetProp);
  
  $shkconf = array();
  $shkconf['shkPath'] = realpath(dirname(__FILE__));
  $shkconf['lang'] = isset($request['lang']) ? $request['lang'] : $manager_language;
  $shkconf['cartType'] = isset($request['cart_type']) ? $request['cart_type'] : 'full';
  $shkconf['cartTpl'] = isset($request['cart_tpl']) ? $request['cart_tpl'] : "@FILE:chunk_shopCart.tpl";
  $shkconf['cartRowTpl'] = isset($request['cart_row_tpl']) ? $request['cart_row_tpl'] : "@FILE:chunk_shopCartRow.tpl";
  $shkconf['additDataTpl'] = isset($request['addit_data_tpl']) ? $request['addit_data_tpl'] : '';
  $shkconf['priceTV'] = isset($request['price_tv']) ? $request['price_tv'] : 'price';
  $shkconf['linkAllow'] = isset($request['link_allow']) ? str2bool($request['link_allow']) : $linkAllow = true;
  $shkconf['noCounter'] = isset($request['nocounter']) ? str2bool($request['nocounter']) : false;
  $shkconf['changePrice'] = isset($request['change_price']) ? str2bool($request['change_price']) : false;
  $shkconf['orderFormPage'] = isset($request['order_page']) ? $request['order_page'] : '';
  $shkconf['currency'] = isset($request['currency']) ? $request['currency'] : '';
  $shkconf['charset'] = $charset;
  if(empty($defaultProp['tplPath'])) $defaultProp['tplPath'] = "assets/snippets/shopkeeper/chunks/".substr($shkconf['lang'],0,2)."/";
  
  if(file_exists("lang/".$shkconf['lang']."-".str_replace('-','',$charset).".php")){
    $s_lang = $shkconf['lang']."-".str_replace('-','',$charset);
  }elseif(file_exists("lang/".$shkconf['lang'].".php")){
    $s_lang = $shkconf['lang'];
  }else{
    $s_lang = "russian";
  }
  
  $shkconf['lang'] == $s_lang;
  $thisPage = $_SERVER['HTTP_REFERER'];
  $action = $request['action'];
  
  $shopCart = new Shopkeeper($modx, array_merge($shkconf,$defaultProp));
  
  switch($action){
    case "fill_cart":
      $shopCart -> savePurchaseData($request);
      $output = $shopCart->getCartContent($shkconf['orderFormPage'],$thisPage);
    break;
    case "empty":
      $shopCart->emptySavedData();
      $output = $shopCart->getCartContent($shkconf['orderFormPage'],$thisPage);
    break;
    case "delete":
      $shopCart->delArrayItem($request['index']);
      $output = $shopCart->getCartContent($shkconf['orderFormPage'],$thisPage);
    break;
    case "recount":
      $shopCart->recountDataArray($request['index'],$request['count'],false);
      $output = $shopCart->getCartContent($shkconf['orderFormPage'],$thisPage);
    break;
    case "refresh_cart":
      $output = $shopCart->getCartContent($shkconf['orderFormPage'],$thisPage);
    break;
    default:
      $output = '';
    break;
  }
  //added by Bumkaka
  $modx->minParserPasses=2;
  $output = $modx->mergeSettingsContent($output);
  $output = $modx->mergeChunkContent($output);
  $output = $modx->evalSnippets($output);
  $output = $modx->rewriteUrls($output);
  //end added by Bumkaka
  
  echo $output;
  
}

?>