<?php

/**
 *  catalogView snippet for MODx Evo
 *
 * @author Andchir <andchir@gmail.com>
 * @package catalogView
 * @version 1.2.5
 */

defined('IN_PARSER_MODE') or die();

$base_dir = $modx->config['rb_base_dir'];
$manager_language = $modx->config['manager_language'];
$charset = $modx->config['modx_charset'];
$rb_base_url = $modx->config['rb_base_url'];
$site_url = $modx->config['site_url'];
$dbname = $modx->db->config['dbase'];
$dbprefix = $modx->db->config['table_prefix'];
$docId = $modx->documentObject['id'];

//define('CATALOG_VIEW_PATH', MODX_BASE_PATH."assets/snippets/catalogView/");

if(!isset($lang)) $lang = 'russian-UTF-8';
require MODX_BASE_PATH."assets/snippets/catalogView/lang/".$lang.".php";

//options
$cv_config = array();
$cv_config['lang'] = $lang;
$cv_config['dataType'] = isset($dataType) && $dataType=='documents' ? 'documents' : 'products'; //documents | products
$cv_config['tbl_catalog'] = $cv_config['dataType']=='products' ? $modx->getFullTableName('catalog') : $modx->getFullTableName('site_content');
$cv_config['tbl_catalog_tv'] = $cv_config['dataType']=='products' ? $modx->getFullTableName('catalog_tmplvar_contentvalues') : $modx->getFullTableName('site_tmplvar_contentvalues');
$cv_config['id'] = isset($id) ? $id : '';
$cv_config['id_prefix'] = $cv_config['id'] ? $cv_config['id'].'_' : '';
$cv_config['noResult'] = isset($noResult) ? $noResult : $langTxt['noResult'];
$cv_config['parents'] = isset($parents) ? $parents : ($modx->documentIdentifier ? $modx->documentIdentifier : 0);
$cv_config['documents'] = isset($documents) ? $documents : '';
$cv_config['products'] = isset($products) ? $products : ($cv_config['documents'] ? $cv_config['documents'] : '');
$cv_config['tpl'] = isset($tpl) ? $tpl : '@FILE:assets/snippets/catalogView/chunks/catalogRow_chunk.tpl';
$cv_config['descTpl'] = isset($descTpl) ? $descTpl : '@FILE:assets/snippets/catalogView/chunks/catalogDesc_chunk.tpl';
$cv_config['tplId'] = isset($tplId) ? $tplId : 1;
$cv_config['paginate'] = isset($paginate) ? $paginate : '0';
$cv_config['where'] = isset($where) ? $where : '';
$cv_config['filter'] = isset($filter) ? $filter : '';
$cv_config['sqlFilter'] = isset($sqlFilter) ? $sqlFilter : false;
$cv_config['globalFilterDelimiter'] = !empty($globalFilterDelimiter) ? $globalFilterDelimiter : '|';
$cv_config['localFilterDelimiter'] = !empty($localFilterDelimiter) ? $localFilterDelimiter : ',';
$cv_config['display'] = isset($display) && is_numeric($display) ? $display : 0;
$cv_config['randomize'] = isset($randomize) ? $randomize : '0';
$cv_config['sortBy'] = isset($sortBy) ? $sortBy : 'id';
$cv_config['sortBy_type'] = isset($sortBy_type) && $sortBy_type=='integer' ? 'integer' : 'string'; //string | integer
$cv_config['sortDir'] = isset($sortDir) && $sortDir=='desc' ? 'desc' : 'asc';
$cv_config['orderBy'] = isset($orderBy) ? $orderBy : '';
$cv_config['fetchContent'] = isset($fetchContent) ? $fetchContent : false;
$cv_config['pageParentClass'] = isset($pageParentClass) ? $pageParentClass : 'pages';
$cv_config['pageClass'] = isset($pageClass) ? $pageClass : 'page';
$cv_config['currentPageClass'] = isset($currentPageClass) ? $currentPageClass : 'current';
$cv_config['thisPageUrl'] = $modx->documentIdentifier ? $modx->makeUrl($modx->documentIdentifier, '', '', 'full') : $modx->makeUrl($cv_config['parents'], '', '', 'full');
$cv_config['excludeURL'] = isset($excludeURL) ? '|'.$excludeURL : '';
$cv_config['renderTVDisplayFormat'] = isset($renderTVDisplayFormat) ? $renderTVDisplayFormat : false;
$cv_config['toPlaceholder'] = !empty($toPlaceholder) ? $toPlaceholder : '';
$cv_config['skipDesc'] = !empty($skipDesc) ? $skipDesc : false;

require_once MODX_BASE_PATH."assets/snippets/catalogView/classes/catalogView.class.php";
$ctlView = new catalogView($modx,$cv_config);

//protection of SQL-injection
if(!get_magic_quotes_gpc() && isset($_GET)){
  foreach($_GET as $key => $value){
    if(!is_array($value)){
      $_GET[addslashes($key)] = addslashes($value);
    }else{
      foreach($value as $k => $v){
        $_GET[addslashes($key)][$k] = addslashes($v);
      }
    }
  }
  unset($key,$value,$k,$v);
}

$output = '';

//print current product content
if(!$cv_config['skipDesc'] && isset($_GET[$cv_config['id_prefix'].'p']) && is_numeric($_GET[$cv_config['id_prefix'].'p'])){
    
    $product_id = !is_array($_GET[$cv_config['id_prefix'].'p']) ? $modx->db->escape($_GET[$cv_config['id_prefix'].'p']) : '';
    $ctlView->config['products'] = $product_id;
    $ctlView->config['fetchContent'] = true;
    list($total,$prodIds,$products) = $ctlView->getProducts();

    if(isset($products[0])){
        $output = $ctlView->renderRows($products,$cv_config['descTpl']);
    }

//print products list
}else{

$qs_sortBy = !empty($_GET[$cv_config['id_prefix'].'sortby']) && !is_array($_GET[$cv_config['id_prefix'].'sortby']) ? $modx->db->escape($_GET[$cv_config['id_prefix'].'sortby']) : $cv_config['sortBy'];
$qs_sortDir = !empty($_GET[$cv_config['id_prefix'].'sortdir']) && in_array($_GET[$cv_config['id_prefix'].'sortdir'],array('asc','desc')) ? $modx->db->escape($_GET[$cv_config['id_prefix'].'sortdir']) : $cv_config['sortDir'];
$qs_sortType = !empty($_GET[$cv_config['id_prefix'].'sorttype']) && in_array($_GET[$cv_config['id_prefix'].'sorttype'],array('string','integer')) ? $modx->db->escape($_GET[$cv_config['id_prefix'].'sorttype']) : $cv_config['sortBy_type'];
$qs_page = !empty($_GET[$cv_config['id_prefix'].'page']) && is_numeric($_GET[$cv_config['id_prefix'].'page']) ? $modx->db->escape($_GET[$cv_config['id_prefix'].'page']) : 1;
$qs_display = !empty($_GET[$cv_config['id_prefix'].'display']) && is_numeric($_GET[$cv_config['id_prefix'].'display']) ? $modx->db->escape($_GET[$cv_config['id_prefix'].'display']) : $cv_config['display'];
$qs_start = !empty($_GET[$cv_config['id_prefix'].'start']) && is_numeric($_GET[$cv_config['id_prefix'].'start']) ? $_GET[$cv_config['id_prefix'].'start'] : ($qs_page*$qs_display)-$qs_display;
$sortDirOther = $qs_sortDir=='desc' ? 'asc' : 'desc';
$qs_filter = !empty($_GET[$cv_config['id_prefix'].'filter']) && !is_array($_GET[$cv_config['id_prefix'].'filter']) ? $modx->db->escape(urldecode($_GET[$cv_config['id_prefix'].'filter'])) : $cv_config['filter'];
if(!$ctlView->isUTF8($qs_filter)) $qs_filter = iconv("windows-1251","UTF-8",$qs_filter);

$ctlView->config['display'] = $qs_display;
$ctlView->config['start'] = $qs_start;
$ctlView->config['sortBy'] = $qs_sortBy;
$ctlView->config['sortDir'] = $qs_sortDir;
$ctlView->config['sortBy_type'] = $qs_sortType;
$ctlView->config['filter'] = $qs_filter;

$pagesQueryString = preg_replace('/((^|&amp;)(q|id|'.$cv_config['id_prefix'].'page'.$cv_config['excludeURL'].')=[^&]*)/','',$_SERVER['QUERY_STRING']);
if($pagesQueryString){
    if(strpos($cv_config['thisPageUrl'], '?')>-1){
        $pagesQueryString = strpos($pagesQueryString, '&amp;')>-1 ? $pagesQueryString : '&amp;'.$pagesQueryString;
    }else{
        $pagesQueryString = strpos($pagesQueryString, '&amp;')==0 ? '?'.substr($pagesQueryString, 5) : '?'.$pagesQueryString;
    }
}

list($total,$prodIds,$products) = $ctlView->getProducts();

if(count($products)==0){//if(!isset($prodIds) || count($prodIds)==0){
  $ctlView->setMODxPlaceholders(array("pages" =>'',"totalPages"=>0,"total"=>0));
  $output .= $cv_config['noResult'];
  if($cv_config['toPlaceholder']){
      $ctlView->setMODxPlaceholders(array($cv_config['toPlaceholder'] => $output));
      $output = '';
  }
  return;
}

$output = $ctlView->renderRows($products,$cv_config['tpl']);

$pagination = '';

if($cv_config['paginate'] && !$cv_config['randomize'] && $cv_config['display']>0){

    //pagination
    require_once MODX_BASE_PATH."assets/snippets/catalogView/classes/pagination.class.php";

    $p = new pagination;
    $p->nextT = $langTxt['next']; //' <a href="[+link+]">'.$langTxt['next'].'</a> ';
    $p->prevT = $langTxt['prev']; //' <a href="[+link+]">'.$langTxt['prev'].'</a> ';
    $p->numberT = ' <a href="[+link+]" class="'.$cv_config['pageClass'].'">[+num+]</a> ';
    $p->currentT = ' <b class="'.$cv_config['currentPageClass'].'">[+num+]</b> ';
    $p->prevI = '';
    $p->Items($total);
    $p->limit($ctlView->config['display']);
    $p->target($cv_config['thisPageUrl'].$pagesQueryString);
    $p->currentPage($qs_page);
    $p->parameterName($cv_config['id_prefix'].'page');
    $p->changeClass($cv_config['pageParentClass']);

    $pagination .= $p->getOutput();
    $totalPages = ceil($total/$qs_display);

    $ctlView->setMODxPlaceholders(array(
        "pages" => $pagination,
        "totalPages" => $totalPages
    ));

}

$ctlView->setMODxPlaceholders(array(
    "sortBy" => $qs_sortBy,
    "sortDir" => $qs_sortDir,
    "qs_start" => $qs_start,
    "total" => $total,
    "currentPage" => $qs_page,
    "display" => $qs_display,
    "sortDirOther" => $sortDirOther,
    "filter" => $qs_filter,
    "pagesQueryString" => $pagesQueryString,
    "thisPageUrl" => $cv_config['thisPageUrl']
));

if($cv_config['toPlaceholder']){
    $ctlView->setMODxPlaceholders(array($cv_config['toPlaceholder'] => $output));
    $output = '';
}

}

?>