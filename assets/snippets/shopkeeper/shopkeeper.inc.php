<?php

/***********************************
* 
* http://modx-shopkeeper.ru/
* Shopkeeper 1.3.6RC
* Shopping cart for MODx Evolution
* 
***********************************/

defined('IN_PARSER_MODE') or die();

if(function_exists('populateOrderData')) return;
if(isset($hideOn) && preg_match('/(^|\s|,)'.$modx->documentIdentifier.'(,|$)/',$hideOn)) return;

$base_dir = $modx->config['rb_base_dir'];
$manager_language = $modx->config['manager_language'];
$charset = $modx->config['modx_charset'];
$rb_base_url = $modx->config['rb_base_url'];
$site_url = $modx->config['site_url'];
$isfolder = $modx->documentObject['isfolder'] ? true : false;

define('SHOPKEEPER_PATH', MODX_BASE_PATH."assets/snippets/shopkeeper/");
define('SHOPKEEPER_URL', MODX_SITE_URL."assets/snippets/shopkeeper/");

$shkconf = array();

$shkconf['shkPath'] = realpath(dirname(__FILE__));
$shkconf['lang'] = isset($lang) ? $lang : $manager_language;  //"russian" || "english" || "german" || "francais"
$shkconf['cartType'] = isset($cartType) ? $cartType : "full"; //"full" || "small" || "empty"
$shkconf['tplPath'] = isset($tplPath) ? $tplPath : "assets/snippets/shopkeeper/chunks/".substr($shkconf['lang'],0,2)."/";
$shkconf['cartTpl'] = isset($cartTpl) ? $cartTpl : "@FILE:chunk_shopCart.tpl";
$shkconf['cartRowTpl'] = isset($cartRowTpl) ? $cartRowTpl : "@FILE:chunk_shopCartRow.tpl";
$shkconf['additDataTpl'] = isset($additDataTpl) ? $additDataTpl : false;
$shkconf['orderDataTpl'] = isset($orderDataTpl) ? $orderDataTpl : false;
$shkconf['cartHelperTpl'] = isset($cartHelperTpl) ? $cartHelperTpl : false;
$shkconf['flyToCart'] = isset($flyToCart) ? $flyToCart : "helper"; //"helper" || "image" || "nofly"
$shkconf['priceTV'] = isset($priceTV) ? $priceTV : "price";
$shkconf['style'] = isset($style) ? $style : "default";
$shkconf['currency'] = isset($currency) ? $currency : "руб.";
$shkconf['inventory'] = isset($inventory) ? $inventory : false;
$shkconf['linkAllow'] = isset($linkAllow) ? $linkAllow : true;
$shkconf['noCounter'] = isset($noCounter) ? $noCounter : false;
$shkconf['noLoader'] = isset($noLoader) ? $noLoader : false;
$shkconf['orderFormPage'] = isset($orderFormPage) ? $orderFormPage : 1;
$shkconf['noJQuery'] = isset($noJQuery) ? $noJQuery : false;
$shkconf['noConflict'] = isset($noConflict) ? $noConflict : false;
$shkconf['debug'] = isset($debug) ? $debug : false;
$shkconf['counterField'] = isset($counterField) ? $counterField : false;
$shkconf['changePrice'] = isset($changePrice) ? $changePrice : false;
$shkconf['stuffCont'] = isset($stuffCont) && preg_match('/\./',$stuffCont)==true ? $stuffCont : (isset($stuffCont) && preg_match('/\./',$stuffCont)==false ? "div.$stuffCont" : "div.shk-item");
$shkconf['noJavaScript'] = isset($noJavaScript) ? $noJavaScript : false;
$shkconf['excepDigitGroup'] = isset($excepDigitGroup) ? $excepDigitGroup : true;
$shkconf['docid'] = $modx->documentIdentifier ? $modx->documentIdentifier : 0;

require_once MODX_BASE_PATH."assets/snippets/shopkeeper/classes/class.shopkeeper.php";
$shopCart = new Shopkeeper($modx, $shkconf);

$GLOBALS['shkconf'] = $shkconf;

if(!function_exists('populateOrderData')){
function populateOrderData( &$fields ){
  global $modx, $shkconf;
  
  if(!class_exists('Shopkeeper')) require_once MODX_BASE_PATH."assets/snippets/shopkeeper/class.shopkeeper.php";
  $shopCart = new Shopkeeper($modx, $shkconf);
  
  $shopCart->populateOrderData($fields);
  
  return true;
}
}

if(!function_exists('sendOrderToManager')){
function sendOrderToManager(&$fields){
  global $modx, $shkconf;
  
  if(!class_exists('Shopkeeper')) require_once MODX_BASE_PATH."assets/snippets/shopkeeper/class.shopkeeper.php";
  $shopCart = new Shopkeeper($modx, $shkconf);
  
  $shopCart->sendOrderToManager($fields);
  
  return true;
}
}


//////////////////////////////////////////////////
//sql injection protection
if(!get_magic_quotes_gpc() && isset($_POST)){
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
  foreach ($_POST as $key => $value){
    if(is_array($value)){
      $_POST[$key] = addslashes_array($value);
    }else{
      $_POST[$key] = addslashes($value);
    }
  }
  unset($key,$value);
}
//////////////////////////////////////////////////

$output = '';

if($cartType=="empty"){
  
  $shopCart->emptySavedData();
  return;

}elseif(isset($_POST['shk-id'])){
  
  $thisPage = $modx->documentIdentifier;
  $orderFormPageUrl = $modx->makeUrl($shkconf['orderFormPage'], '', '', 'full');
  $purchaseArray = $_POST;
  $shopCart -> savePurchaseData($purchaseArray);
  $modx->sendRedirect($_SERVER['HTTP_REFERER'],0,'REDIRECT_HEADER');

}elseif(isset($_GET['shk_action'])){
  
  $action = addslashes($_GET['shk_action']);
  switch($action){
    case "empty":
      $shopCart->emptySavedData();
    break;
    case "del":
      $item_index = isset($_GET['n']) && is_numeric($_GET['n']) ? $_GET['n'] : "";
      $shopCart->delArrayItem($item_index);
    break;
  }
  $modx->sendRedirect($_SERVER['HTTP_REFERER'],0,'REDIRECT_HEADER');

}elseif(isset($_POST['shk_recount'])){
  
  if(!empty($_POST['count'])){
    $shopCart->recountAll($_POST['count']);
    $modx->sendRedirect($_SERVER['HTTP_REFERER'],0,'REDIRECT_HEADER');
  }
  
}

$headHtml = "";

if($shkconf['style']){$headHtml .= "
    <link type=\"text/css\" rel=\"stylesheet\" href=\"".SHOPKEEPER_URL."style/".$shkconf['style']."/style.css\" />";
  $modx->regClientStartupHTMLBlock($headHtml);
}

if($shkconf['noJavaScript']==false){
  
  if(!$shkconf['noJQuery']){
    $modx->regClientStartupScript(SHOPKEEPER_URL."js/jquery-1.6.3.min.js",array('name'=>'jquery','version'=>'1.6.3','plaintext'=>false));
  }
  
  $jsSrc ="\n\t<script type=\"text/javascript\">\n\t<!--";
    
    if($shkconf['noConflict']){$jsSrc .="
      jQuery.noConflict();";
    }
    
  $jsSrc .="
      var site_url = '".$site_url."';
      var shkOptions = {
         stuffCont: '".$shkconf['stuffCont']."',
         lang: '".$shkconf['lang']."',
         currency: '".$shkconf['currency']."',
         orderFormPage: '[~".$shkconf['orderFormPage']."~]',
         cartTpl: ['".$shkconf['cartTpl']."','".$shkconf['cartRowTpl']."','".$shkconf['additDataTpl']."'],
         priceTV: '".$shkconf['priceTV']."'";
      if($shkconf['cartType']!='full'){$jsSrc .=",\n\t cartType: '".$shkconf['cartType']."'";}
      if($shkconf['counterField']){$jsSrc .=",\n\t counterField: true";}
      if($shkconf['changePrice']){$jsSrc .=",\n\t changePrice: true";}
      if($shkconf['noCounter']){$jsSrc .=",\n\t noCounter: true";}
      if($shkconf['flyToCart']!='helper'){$jsSrc .=",\n\t flyToCart: '".$shkconf['flyToCart']."'";}
      if(!$shkconf['linkAllow']){$jsSrc .=",\n\t linkAllow: false";}
      if($shkconf['style']){$jsSrc .=",\n\t style:'".$shkconf['style']."'";}
      if($shkconf['noLoader']){$jsSrc .=",\n\t noLoader: true";}
      if($shkconf['debug']){$jsSrc .=",\n\t debug: true";}
      
      if($shkconf['cartHelperTpl']){
        $helperChunk = preg_split("/[\r\n]+/", trim($shopCart->fetchTpl($shkconf['cartHelperTpl'])));
        $helperStr = '';
        for($i=0;$i<count($helperChunk);$i++){
          $plus = $i==0 ? '' : '+';
          $helperStr .= "$plus'".trim($helperChunk[$i])."'\n";
        }
        $jsSrc .=",\n\t shkHelper: $helperStr";
      }
      
      $jsSrc .="\n\t};
      jQuery(document).ready(function(){
        jQuery(shkOptions.stuffCont).shopkeeper();
      });";
  
  $jsSrc .="\n\t//-->\n\t</script>";
  
  $jsSrc .="
    <script src=\"".SHOPKEEPER_URL."lang/".$shkconf['lang'].".js\" type=\"text/javascript\"></script>
    <script src=\"".SHOPKEEPER_URL."js/jquery.livequery.js\" type=\"text/javascript\"></script>
    <script src=\"".SHOPKEEPER_URL."js/shopkeeper.js\" type=\"text/javascript\"></script>
  ";
  
  if($shkconf['debug']){
    $jsSrc .= "
    <script src=\"".SHOPKEEPER_URL."js/log4javascript.js\" type=\"text/javascript\"></script>
    <script src=\"".SHOPKEEPER_URL."js/shkdebug.js\" type=\"text/javascript\"></script>";
  }
    
  $modx->regClientStartupScript($jsSrc);

}

$thisPage = $modx->documentIdentifier;
$orderFormPageUrl = $modx->makeUrl($shkconf['orderFormPage'], '', '', 'full');

$evtOut = $modx->invokeEvent('OnSHKFrontendInit');
if (is_array($evtOut)) $output .= implode('', $evtOut);
unset($evtOut);

//
$purchases = !empty($_SESSION['purchases']) ? unserialize($_SESSION['purchases']) : array();
$addit_params = !empty($_SESSION['addit_params']) ? unserialize($_SESSION['addit_params']) : array();
list($totalItems,$totalPrice) = $shopCart->getTotal($purchases,$addit_params);

$modx->setPlaceholder('totalItems',$totalItems);
$modx->setPlaceholder('totalPrice',$totalPrice);

$output .= $shopCart->getCartContent($orderFormPageUrl,$thisPage,$langTxt);

?>