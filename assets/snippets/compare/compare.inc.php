<?php
if(!defined('MODX_BASE_PATH')){die('What are you doing? Get out of here!');}
/*

compare 1.2
Сравнение товаров

Andchir - http://modx-shopkeeper.ru/

*/

defined('IN_PARSER_MODE') or die();

$default_toCompareTpl = '
    <p>
        Выбрано <span id="skolko_vibrano">[+count_current+]</span> из [+count_max+]
        / <a href="[+href_compare+]" onclick="return shkCompare.toCompareLink();">сравнить</a>
        <span id="sravnenie_otmena" style="display:[+display_cancel+];"> / <a href="[+href_cancel+]">отменить</a></span>
    </p>
    <br clear="all" />
';

$cmp_config = array();
$cmp_config['dataType'] = isset($dataType) ? $dataType : 'documents'; //documents | products
$cmp_config['tbl_catalog'] = $cmp_config['dataType']=='products' ? $modx->getFullTableName('catalog') : $modx->getFullTableName('site_content');
$cmp_config['tbl_catalog_tv'] = $cmp_config['dataType']=='products' ? $modx->getFullTableName('catalog_tmplvar_contentvalues') : $modx->getFullTableName('site_tmplvar_contentvalues');
$cmp_config['toCompare_tpl'] = !empty($toCompare_tpl) ? $toCompare_tpl : '@CODE: '.$default_toCompareTpl;
$cmp_config['product_tpl'] = !empty($product_tpl) ? $product_tpl : '@CODE: не указан чанк &product_tpl';
$cmp_config['limitProducts'] = !empty($limitProducts) ? $limitProducts : 0;
$cmp_config['comparePageId'] = !empty($comparePageId) ? $comparePageId : 1;
$cmp_config['onlyThisParentId'] = !empty($onlyThisParentId) ? $onlyThisParentId : false;
$cmp_config['filterTVID'] = !empty($filterTVID) ? $filterTVID : '';
$cmp_config['removeLastTwo'] = isset($removeLastTwo) ? $removeLastTwo : true;
$action = isset($action) ? $action : 'to_compare';

require_once MODX_BASE_PATH.'assets/snippets/compare/compare.class.php';
$compare = new prodCompare($modx,$cmp_config);

//действия, переданные по $_GET
$cmp_action = isset($_GET['cmp_action']) && !is_array($_GET['cmp_action']) ? $_GET['cmp_action'] : '';
if($cmp_action=='del_product' && !in_array($action,array('print_products','print_id_list'))) return;
switch($cmp_action){
    //удаление одного товара из списка для сравнения
    case 'del_product':
        if(!empty($_GET['pid'])) $compare->deleteCompareProduct();
    break;
    //очистка списка товаров, выбранных для сравнения
    case 'empty':
        $compare->emptyCompare();
    break;
}

//действия для вывода в месте вызова сниппета
switch($action){
    //вывод строки со ссылками на страницу сравнения
    case 'to_compare':
        $output = $compare->toCompareContent();
    break;
    //вывод списка ID товаров, выбранных для сравнения
    case 'print_id_list':
        $output = $compare->printIDList();
    break;
    //вывод списка товаров, выбранных для сравнения
    case 'print_products':
        $output = $compare->printCompareProducts();
    break;
    //вывод ID категории товаров, выбранных для стравнения
    case 'print_parent_id':
        $output = isset($_COOKIE['shkCompareParent']) ? $_COOKIE['shkCompareParent'] : '';
    break;
    default:
        $output = '';
    break;
}

return $output;

?>