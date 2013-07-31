<?php

/**
 *
 * @name catalogFill module for MODx Evo
 * @version 1.3.8
 * @author Andchir <andchir@gmail.com>
 *
 */

defined('IN_MANAGER_MODE') or die();

//error_reporting(E_ALL | E_STRICT);
//ini_set('display_errors', 1);

ini_set("upload_max_filesize","15M");
ini_set("post_max_size","15M");
ini_set("max_execution_time","1200"); //20 min.
ini_set("max_input_time","1200"); //20 min.
ini_set('auto_detect_line_endings',1);
date_default_timezone_set('Europe/Moscow');
setlocale (LC_ALL, 'ru_RU.UTF-8');

$mod_name = 'Импорт/экспорт товаров';
$dbname = $modx->db->config['dbase'];
$dbprefix = $modx->db->config['table_prefix'];
$theme = $modx->config['manager_theme'];
$charset = $modx->config['modx_charset'];
$manager_language = $modx->config['manager_language'];
$mod_page = "index.php?a=112&id=".$_GET['id'];

define("MODULE_PATH","../assets/modules/catalogFill/");

$cf_config = array();

$config_file = isset($_POST['config']) && file_exists(MODULE_PATH."config/".$_POST['config'].'.php') ? MODULE_PATH."config/".$_POST['config'].'.php' : MODULE_PATH."config/".'default.php';
require $config_file;

//если в конфиге нет функций для фильтрации значений, создаем их
if(!function_exists('filter_import')){
    function filter_import($value_arr){
        return $value_arr;
    }
}
if(!function_exists('filter_export')){
    function filter_export($value_arr,$doc_id=0){
        return $value_arr;
    }
}

$cf_config['files_import_dir'] = MODULE_PATH."import/";
$cf_config['files_export_dir'] = MODULE_PATH."export/";
$cf_config['files_config_dir'] = MODULE_PATH."config/";
$cf_config['mod_page'] = "index.php?a=112&id=".$_GET['id'];
$cf_config['content_table'] = $cf_config['content_type']=='documents' ? $modx->getFullTableName('site_content') : $modx->getFullTableName('catalog');
$cf_config['tmplvar_content_table'] = $cf_config['content_type']=='documents' ? $modx->getFullTableName('site_tmplvar_contentvalues') : $modx->getFullTableName('catalog_tmplvar_contentvalues');
$cf_config['insert_group'] = 200;

require MODULE_PATH."classes/catalogfill.class.php";
$catalogFill = new catalogFill($modx,$cf_config);

$cf_action = isset($_POST['cf_action']) ? $_POST['cf_action'] : (isset($_GET['cf_action']) ? $_GET['cf_action'] : 'default');

switch ($cf_action){
    case "import":
        $parent_id = $_POST['parent'];
        $file_name = !empty($_POST['file_path']) ? $_POST['file_path'] : (!empty($_POST['file']) ? $_POST['file'] : false);
        $catalogFill->config['imp_update'] = isset($_POST['cleanimport']) && $_POST['cleanimport'] == "1" ? true : false;
        if(strtolower(substr($file_name,-3))=='csv'){
            $catalogFill->csv_import($parent_id,$file_name);
        }else if(strtolower(substr($file_name,-3))=='xls'){
            $catalogFill->xls_import($parent_id,$file_name);
        }else if(strtolower(substr($file_name,-4))=='xlsx'){
            $catalogFill->xls_import($parent_id,$file_name,'Excel2007');
        }
        
        //clear MODx cache
        if($cf_config['include_categories'] || $cf_config['content_type'] == 'documents'){
            $modx->sendRedirect('index.php?a=112&id='.$_GET['id'].'&cf_action=clear_cache', 0, 'REDIRECT_HEADER');
            exit();
        }
        
    break;
    case "export":
        $parent_id = $_POST['parent'];
        $exp_type = isset($_POST['exp_type']) ? $_POST['exp_type'] : 'csv';
        if($exp_type=='csv'){
            $catalogFill->csv_export($parent_id);
        }else if($exp_type=='xls'){
            $catalogFill->xls_export($parent_id);
        }else if($exp_type=='xlsx'){
            $catalogFill->xls_export($parent_id,'Excel2007');
        }
    break;
    case "clean_parent":
        $parent_id = $_POST['parent'];
        $catalogFill->cleanParent($parent_id);
        $catalogFill->cleanHermitTVvalues();
        $catalogFill->cleanHermitContent();
        if($cf_config['content_type']=='documents' || $cf_config['include_categories']){
            $modx->sendRedirect('index.php?a=112&id='.$_GET['id'].'&cf_action=clear_cache', 0, 'REDIRECT_HEADER');
            exit;
        }
    break;
    case "clean":
        $c_dir = isset($_POST['item_id']) && $_POST['item_id'] == "import" ? $cf_config['files_import_dir'] : $cf_config['files_export_dir'];
        $catalogFill->cleanDir($c_dir);
    break;
    case "clear_cache":
        include_once "./processors/cache_sync.class.processor.php";
        
        echo '
            <html>
              <head>
                <link rel="stylesheet" type="text/css" href="media/style/'.$theme.'/style.css" />
              </head>
              <body style="padding:20px;">
              <p><b>Очистка...</b></p>
        ';
        
        $sync = new synccache();
        $sync->setCachepath("../assets/cache/");
        $sync->setReport(true);
        $sync->emptyCache();
        
        echo '
              <script type="text/javascript">
                //parent.tree.location.reload();
                parent.tree.restoreTree();
                setTimeout(function(){
                    window.location.href="'.$mod_page.'";
                },2000);
              </script>
              </body>
            </html>
        ';
        exit;
    break;
}

$imp_count = $catalogFill->countFiles($cf_config['files_import_dir']);
$exp_count = $catalogFill->countFiles($cf_config['files_export_dir']);
$config_list = $catalogFill->configList();
$files_list = $catalogFill->filesList();

require MODULE_PATH."tpl/main.tpl.php";

?>
