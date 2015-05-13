<?php

/**
* SHKmanager
*
* Order management catalog controller
*
* @author Andchir <andchir@gmail.com>
* @version 1.0
*/

require_once MODX_MANAGER_PATH . "includes/controls/datagrid.class.php";

$page_num = 30;

$defaultCategory = isset($conf_catalog) ? $conf_catalog : 0;

$categoryId = isset($_GET['category']) ? $_GET['category'] : $defaultCategory;
$category = $categoryId ? $modx->getPageInfo($categoryId,1,'id,pagetitle') : $category = array('id'=>'','pagetitle'=>'');

$catalog_mod_page = $mod_page."&action=catalog&category=".$categoryId;

//actions
$catalog_action = isset($_POST['action']) ? $_POST['action'] : '';

switch ($catalog_action) {
    case 'delete_item':
        if(!$modx->hasPermission('edit_document')){
            global $e;
            $e->setError(3);
            $e->dumpError();
            exit;
      	}
        $catalog_item = isset($_POST['item_id']) ? $modx->db->escape($_POST['item_id']) : false;
        if($catalog_item!==false){
            $modx->db->delete($modx->getFullTableName('catalog'), "id = '$catalog_item'");
            $modx->db->delete($modx->getFullTableName('catalog_tmplvar_contentvalues'), "contentid = '$catalog_item'");
            $modx->sendRedirect($catalog_mod_page,0,"REDIRECT_HEADER");
        }
    break;
    case 'change_price':
        if(!$modx->hasPermission('edit_document')){
            global $e;
            $e->setError(3);
            $e->dumpError();
            exit;
      	}
        $shkm->changePrice($_POST['prod_id'],$_POST['price'],$conf_pricetv);
        $modx->sendRedirect($catalog_mod_page,0,"REDIRECT_HEADER");
    break;
    default:
    break;
}



$data_table = '';
$pagination = '';

if($categoryId){
    
    $curPage = isset($_GET['page']) ? $_GET['page'] : 1;
    $start = $curPage>1 ? ($curPage*$page_num)-$page_num : 0;
    $total = $modx->db->getValue($modx->db->select("COUNT(*)",$modx->getFullTableName('catalog'),"parent = '$categoryId'"));
    
    $items_query = "
        SELECT sc.id, sc.pagetitle, sc.parent, (SELECT tvc.value FROM ".$modx->getFullTableName('catalog_tmplvar_contentvalues')." tvc WHERE tvc.contentid = sc.id AND tvc.tmplvarid = '$conf_pricetv') AS price
        FROM ".$modx->getFullTableName('catalog')." sc
        WHERE sc.parent = '$categoryId'
        ORDER BY sc.id DESC
        LIMIT $start, $page_num
    ";
    $items_result = $modx->db->query($items_query);
    //$items_result = $modx->db->select("*",$modx->getFullTableName('catalog'),"parent = '$categoryId'","id DESC");
    $numRecords = $modx->db->getRecordCount($items_result);
    if($numRecords){
        
        $grd = new DataGrid('', $items_result);
        $grd->noRecordMsg = 'Нет заявок.';
        $grd->pageSize = $page_num;
        $grd->pagerLocation = 'bottom-left';
        $grd->cssClass = "grid";
        $grd->columnHeaderClass = "gridHeader";
        $grd->itemClass = "gridItem";
        $grd->altItemClass = "gridAltItem";
        $grd->columns = "ID,Наименование,Цена,Действия";
        $grd->colTypes = '
        ||template:<br /><a href="index.php?a=27&amp;update=[+id+]&amp;id=[+parent+]&amp;pid=[+parent+]&amp;to_shk=1" onclick="$.fn.colorbox.init();$.fn.colorbox($.extend(colorBoxOpt,{href:\'index.php?a=27&amp;update=[+id+]&amp;id=[+parent+]&amp;pid=[+parent+]&amp;to_shk=1\'})); return false;">[+pagetitle+]</a>
        ||template:<input type="hidden" name="prod_id[]" value="[+id+]" /> <input type="text" name="price[]" size="12" value="[+price+]" />
        ||template:<a href="#" title="'.$langTxt['delete'].'" onclick="if(confirm(\''.$langTxt['confirm'].'\')){postForm(\'delete_item\',\'[+id+]\',null);}return false;"><img src="'.SHOPKEEPER_PATH.'style/default/img/m_delete.gif" /></a>
        ';
        $grd->colWidths = "10%,50%,20%,20%";
        $grd->fields = "id,pagetitle,price";

        $data_table = $grd->render();
        
        //pagination
        $p = new pagination;
        $p->Items($total);
        $p->limit($page_num);
        $p->target($catalog_mod_page);
        $p->currentPage($curPage);
        $p->nextT = ' <a href="[+link+]">'.$langTxt['next'].'</a> ';
        $p->prevT = ' <a href="[+link+]">'.$langTxt['prev'].'</a> ';

        $pagination = $p->getOutput();
        
    }
}

include "templates/catalog.tpl.php";


?>