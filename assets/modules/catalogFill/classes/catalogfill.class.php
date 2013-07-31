<?php

/**
 *
 * @name catalogFill class
 * @version 1.3.8
 * @author Andchir <andchir@gmail.com>
 *
 */

class catalogFill {

    public $categories = array();
    public $prod_ids = array();

/**
 * class constructor
 *
 * @param array $config
 */
function __construct(&$modx, $config){

    $this->modx = $modx;
    
    $this->config = array_merge(array(
        "content_template" => 1,
        "imp_testmode" => false,
        "files_import_dir" => "",
        "files_export_dir" => "",
        "files_config_dir" => "",
        "content_table" => $this->modx->getFullTableName('catalog'),
        "tmplvar_content_table" => $this->modx->getFullTableName('catalog_tmplvar_contentvalues'),
        "exp_delete_file" => true,
        "delete_subcategories" => false,
        "imp_chk_field" => false,
        "imp_chk_tvid_val" => false,
        "imp_chk_delete" => 0,
        "imp_autoalias" => true,
        "imp_update" => true
    ),$config);

}

/**
 * Опредиляем кодировку (UTF-8 или нет)
 * 
 * @param string $str
 * @return boolean
 */
function isUTF8($str){
    //if($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"))
    if(mb_detect_encoding($str,"UTF-8",true)!==false)
        return true;
    else
        return false;
}


/**
 * Вытаскиваем данные TV параметров товаров
 * 
 * @param array $idsArr
 * @return array
 */
function getTmplVars($idsArr){
    $templateVars = array();
    if(count($idsArr)){
        $query = $this->modx->db->select(
            "tv.name, tvc.value, tvc.contentid, tv.id",
            $this->modx->getFullTableName('site_tmplvars')." tv, ".$this->config['tmplvar_content_table']." tvc",
            "tv.id = tvc.tmplvarid AND tvc.contentid IN (".implode(',',$idsArr).")"
        );
        while($row = $this->modx->db->getRow($query)){
            $templateVars[$row['contentid']][$row['name']] = $row['value'];
            $templateVars[$row['contentid']][$row['id']] = $row['value'];
        }
    }
    return $templateVars;
}


/**
 * Вытаскиваем данные товара
 * 
 * @param array $parent_id_arr
 * @return array
 */
function getProducts($parent_id_arr){
    if(!is_array($parent_id_arr) || count($parent_id_arr)==0) return array();
    $where_q = "parent IN (".implode(',',$parent_id_arr).")";
    if(isset($this->config['imp_content_default']['content']['isfolder'])) $where_q .= " AND isfolder = ".$this->config['imp_content_default']['content']['isfolder'];
    if(isset($this->config['imp_content_default']['content']['template'])) $where_q .= " AND template = ".$this->config['imp_content_default']['content']['template'];
    $data_query = $this->modx->db->select("*", $this->config["content_table"], $where_q, "parent ASC, id ASC");
    $products = array();
    $prodIds = array();
    while($row = $this->modx->db->getRow($data_query)){
        $products[] = $row;
        $prodIds[] = $row['id'];
    }
    return array($products,$prodIds);
}


/**
 * Импорт данных из CSV файла
 *
 * @param int $parent_id
 */
function csv_import($parent_id,$file_name=false){
    
    //очистка категории
    if($this->config['imp_update'] && $this->config['imp_chk_field']===false && $this->config['imp_chk_tvid_val']===false){
        $this->cleanParent($parent_id);
    }

    if($this->config['include_categories']){
      $this->categories = $this->getChildCategories($parent_id);
    }
    
    //если главная категория не является контейнером, делаем её таковой
    $isfolder = $this->modx->db->getValue($this->modx->db->select("isfolder",$this->modx->getFullTableName("site_content"),"id = '$parent_id'"));
    if($isfolder==0) $this->modx->db->update("isfolder = '1'", $this->modx->getFullTableName("site_content"), "id = '$parent_id'");
    
    if(!$file_name){
      //загрузка файла
      $csv_file = $this->config['files_import_dir']."imp_".date("dmy_His").".csv";
      if(copy($_FILES['file']['tmp_name'],$csv_file)){
        unlink($_FILES['file']['tmp_name']);
        $file_uploaded = true;
      }else{
        $file_uploaded = false;
      }
    //файл загружен
    }else{
      $csv_file = $this->config['files_import_dir'].$file_name;
      $file_uploaded = file_exists($csv_file) ? true : false;
    }

    if($file_uploaded){

        $count = 0;
        $fileHandler = fopen($csv_file, "r");
        $insertArr = array();
        while ($line = fgetcsv($fileHandler, filesize($csv_file), ";")){
            $tempArr = array();
            if($count==0 && $this->config['include_captions']){$count++; continue;}
            foreach($line as $key => $val){
                $c_type = $this->config['content_row'][$key][1];
                $c_value = $this->isUTF8($val) ? $val : iconv('cp1251','UTF-8',$val);
                if($c_type[1]!='category'){
                    $tempArr[$c_type[1]][$c_type[0]] = $this->modx->db->escape(trim($c_value));
                }else{
                    $tempArr['category'][] = array($c_type[0],$this->modx->db->escape(trim($c_value)));
                }
            }
            unset($key,$val);
            //parent
            if(isset($tempArr['category'])){
                $tempArr['content']['parent'] = $this->setParentCategory($tempArr['category'],$parent_id);
            }else{
                $tempArr['content']['parent'] = $parent_id;
            }
            //alias
            if($this->config['imp_autoalias']){
                $tempArr['content']['alias'] = $this->makeAlias($tempArr['content']['pagetitle']);
            }
            
            $insertArr[] = filter_import($tempArr);
            
            $count++;
            if($count%$this->config['insert_group']==0){
                $this->groupInsertToDB($insertArr);
                unset($insertArr);
                $insertArr = array();
            }
            
        }
        fclose($fileHandler);
        
        $this->groupInsertToDB($insertArr);
        unset($insertArr);

        $this->message = "Импортировано товаров: ".($this->config['include_captions'] ? $count-1 : $count);

    }else{

        $this->message = "Ошибка при копировании файла.";

    }

}



/**
 * Импорт данных из XLS файла
 * 
 * @param int $parent_id
 * @param string $file_name
 */
function xls_import($parent_id,$file_name,$xls_type='Excel5'){
    
    require_once realpath(dirname(__FILE__)).'/PHPExcel.php';

    $file_path = $this->config['files_import_dir'].$file_name;
    
    //очистка категории
    if($this->config['imp_update'] && $this->config['imp_chk_field']===false && $this->config['imp_chk_tvid_val']===false){
        $this->cleanParent($parent_id);
    }
    
    if($this->config['include_categories']){
        $this->categories = $this->getChildCategories($parent_id);
    }
    
    //если главная категория не является контейнером, делаем её таковой
    $isfolder = $this->modx->db->getValue($this->modx->db->select("isfolder",$this->modx->getFullTableName("site_content"),"id = '$parent_id'"));
    if($isfolder==0) $this->modx->db->update("isfolder = '1'", $this->modx->getFullTableName("site_content"), "id = '$parent_id'");
    
    if(file_exists($file_path)){

        if($xls_type=='Excel2007'){
            $objReader = new PHPExcel_Reader_Excel2007();
        }else{
            $objReader = new PHPExcel_Reader_Excel5();
        }
        $objPHPExcel = $objReader->load($file_path);
        $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);
        $objWorksheet = $objPHPExcel->getActiveSheet();
        
        $highestRow = $objWorksheet->getHighestRow();
        $highestColumn = $objWorksheet->getHighestColumn();
        //$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        
        $count = 0;
        $start = $this->config['include_captions'] ? 2 : 1;
        $insertArr = array();
        for ($row = $start; $row <= $highestRow; $row++) {
            $tempArr = array();
            foreach($this->config['content_row'] as $col => $v){
                $c_type = $this->config['content_row'][$col][1];
                $c_value = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue();
                $c_value = str_replace('\=','=',$c_value);
                if(!$this->isUTF8($c_value)) $c_value = iconv('cp1251','UTF-8',$c_value);
                if($c_type[1]!='category'){
                    $tempArr[$c_type[1]][$c_type[0]] = $this->modx->db->escape(trim($c_value));
                }else{
                    $tempArr['category'][] = array($c_type[0],$this->modx->db->escape(trim($c_value)));
                }
            }
            
            //parent
            if(isset($tempArr['category'])){
                $tempArr['content']['parent'] = $this->setParentCategory($tempArr['category'],$parent_id);
            }else{
                $tempArr['content']['parent'] = $parent_id;
            }
            //alias
            if(empty($tempArr['content']['alias']) && $this->config['imp_autoalias']){
                $tempArr['content']['alias'] = $this->makeAlias($tempArr['content']['pagetitle']);
            }
            
            $insertArr[] = filter_import($tempArr);
            
            $count++;
            
            if($count%$this->config['insert_group']==0){
                $this->groupInsertToDB($insertArr);
                unset($insertArr);
                $insertArr = array();
            }
            
        }
        
        $this->groupInsertToDB($insertArr);
        unset($insertArr);
        
        $this->removeMissing();//удаляем товары, которых нет в файле, если нужно
        
        $this->message = "Импортировано товаров: $count.";

    }else{

        $this->message = "Ошибка при копировании файла.";

    }
}



/**
 * Записываем группу данных в таблицы БД
 * 
 * @param array $insertArr
 */
function groupInsertToDB($insertArr){
    
    if(count($insertArr)==0) return;
    
    if($this->config['imp_testmode']){
      header('Content-Type: text/html; charset=utf-8');
      echo "<pre>";
      print_r($insertArr);
      echo "</pre>";
      exit;
    }
    
    $content_names_arr = array_keys(array_merge($this->config['imp_content_default']['content'],$insertArr[0]['content']));
    $valueNamesContent = "(";
    if(!in_array('id',$content_names_arr)) $valueNamesContent .= "`id`,";
    $valueNamesContent .= "`".implode("`,`", $content_names_arr)."`)";
    
    $contentId = $this->modx->db->getValue($this->modx->db->select("MAX(id)",$this->config['content_table']));
    $insertContentStr = "";
    $insertTVStr = "";
    foreach($insertArr as $key => $val){
        
        $contentArr = array_merge($this->config['imp_content_default']['content'],$val['content']);
        $tvArr = $this->config['imp_content_default']['tv']+$val['tv'];
        
        if($this->config['imp_update'] && ($this->config['imp_chk_field']!==false || $this->config['imp_chk_tvid_val']!==false)){
            
            if($this->config['imp_chk_field']!==false){
                $upd_id = $this->modx->db->getValue($this->modx->db->select("id",$this->config['content_table'],"`".$this->config['imp_chk_field']."` = '".$val['content'][$this->config['imp_chk_field']]."' AND `parent` = '".$val['content']['parent']."'"));
            }else if(isset($tvArr[$this->config['imp_chk_tvid_val']])){
                $select_q = "
                    SELECT sc.id FROM ".$this->config['content_table']." sc
                    LEFT JOIN ".$this->config['tmplvar_content_table']." tvc ON sc.id = tvc.contentid
                    WHERE sc.parent = '".$val['content']['parent']."'
                    AND tvc.tmplvarid = '".$this->config['imp_chk_tvid_val']."'
                    AND tvc.value = '".$tvArr[$this->config['imp_chk_tvid_val']]."'
                ";
                $upd_id = $this->modx->db->getValue($this->modx->db->query($select_q));
            }
                
            if(!empty($upd_id)){
                
                if($this->config['imp_chk_delete']>0) $this->prod_ids[] = $upd_id;
                
                $updateContentStr = "UPDATE ".$this->config['content_table']." SET";
                if($this->config['imp_chk_field']=='id') unset($val['content']['id']);
                $contentArr = array_merge($this->config['imp_content_default']['content'],$val['content']);
                foreach($contentArr as $k => $v){
                    $updateContentStr .= " `$k` = '$v',";
                }
                unset($k,$v);
                
                //обновляем поля товара (content)
                $this->modx->db->query(substr($updateContentStr,0,-1)." WHERE id = '$upd_id';");
                
                //удаляем старые значения TV
                foreach ($tvArr as $k => $v){
                    $this->modx->db->query("DELETE FROM ".$this->config['tmplvar_content_table']." WHERE tmplvarid = '$k' AND contentid = '$upd_id'");
                    $insertTVStr .= "\n('$k', '$upd_id', '$v'),";
                }
                unset($k,$v);
                
                continue;
            }
        }
        
        $contentId++;
        
        if(!empty($contentArr['pagetitle'])){
            $insertContentStr .= "\n (";
            if(!in_array('id',$content_names_arr)) $insertContentStr .= "'$contentId',";
            $insertContentStr .= "'".implode("','",$contentArr)."'),";
            
            foreach ($tvArr as $k => $v){
                $insertTVStr .= "\n('$k', '".(isset($val['content']['id']) ? $val['content']['id'] : $contentId)."', '$v'),";
            }
        }
        
    }
    
    if($this->config['imp_chk_field']!==false){
        $this->clearAutoIncrement($this->config['content_table']);
        $this->clearAutoIncrement($this->config['tmplvar_content_table']);
    }
    
    //добавляем товары в БД (content)
    if($insertContentStr) $this->modx->db->query("INSERT INTO ".$this->config['content_table']." $valueNamesContent VALUES ".substr($insertContentStr,0,-1).";");
    
    //добавляем TV в БД
    if($insertTVStr) $this->modx->db->query("INSERT INTO ".$this->config['tmplvar_content_table']." (`tmplvarid`, `contentid`, `value`) VALUES ".substr($insertTVStr,0,-1).";");
    
}

/**
 * Удаляет товары, которых нет в файле, если нужно
 */
function removeMissing(){
    
    
    
}


/**
 * Записываем данные в таблицы БД
 * 
 * @param array $insertArr
 */
function insertToDB($insertArr){

    if(!isset($insertArr['tv'])) $insertArr['tv'] = array();

    if($this->config['imp_testmode']){
      header('Content-Type: text/html; charset=utf-8');
      echo "<pre>";
      print_r(array_merge($this->config['imp_content_default']['content'],$insertArr['content']));
      print_r($this->config['imp_content_default']['tv']+$insertArr['tv']);
      echo "</pre>";
      exit;
    }

    if(isset($insertArr['content']['pagetitle'])){
      $contentArr = array_merge($this->config['imp_content_default']['content'],$insertArr['content']);
      $this->modx->db->insert($contentArr, $this->config['content_table']);
      $content_id = $this->modx->db->getInsertId();

      foreach($this->config['imp_content_default']['tv']+$insertArr['tv'] as $key => $val){
        if(is_numeric(trim($val))){
          $val = floor($val) == $val ? round($val) : round($val,2);
        }
        $tvArrInsert = array("tmplvarid"=>$key,"contentid"=>$content_id,"value"=>$val);
        $this->modx->db->insert($tvArrInsert, $this->config['tmplvar_content_table']);
      }
      
    }
}



/**
 * Экспорт товаров в CSV файл
 * 
 * @param int $parent_id
 */
function csv_export($parent_id){
    
    if($this->config['include_categories']){
        $this->categories = $this->getChildCategories($parent_id);
        $parent_id_arr = $this->getChildCategoriesID($parent_id);
    }else{
        $this->categories = array();
        $parent_id_arr = array($parent_id);
    }
    
    list($products,$prodIds) = $this->getProducts($parent_id_arr);
    if(count($products)==0){
        $this->message = "В выбранной категории нет продуктов.";
        return;
    }
    $tmplVars = $this->getTmplVars($prodIds);

    $filename = "exp_".date("dmy_His").".csv";
    $filepath = $this->config['files_export_dir'].$filename;
    $fp = fopen($filepath, 'x+');

    //write header row
    if($this->config['include_captions']){
        $line = array();
        
        if(strtoupper($this->config['exp_csv_charset']) != 'UTF-8'){
            foreach($this->config['content_row'] as $val) $line[] = iconv('UTF-8',$this->config['exp_csv_charset'],$val[0]);
        }else{
            foreach($this->config['content_row'] as $val) $line[] = $val[0];
        }
        
        fputcsv($fp, $line, ';', '"');
    }
    unset($val,$line);

    foreach($products as $key => $val){
        $line = array();
        $categoryNames = $this->getCategoryNames($val['parent']);
        $val = filter_export($val);
        $tv_arr = isset($tmplVars[$val['id']]) ? filter_export($tmplVars[$val['id']],$val['id']) : array();
        foreach($this->config['content_row'] as $k => $v){
            if($v[1][1]=='content'){
                $c_value = isset($val[$v[1][0]]) ? trim($val[$v[1][0]]) : '';
            }else if($v[1][1]=='tv'){
                $c_value = isset($tv_arr[$v[1][0]]) ? trim($tv_arr[$v[1][0]]) : '';
            }else if($v[1][1]=='category'){
                $c_value = isset($categoryNames[$k]) ? $categoryNames[$k] : '';//isset($categories[$val['parent']]) ? $categories[$val['parent']] : '';
            }
            if(strtoupper($this->config['exp_csv_charset']) != 'UTF-8') $c_value = iconv('UTF-8',$this->config['exp_csv_charset'],$c_value);
            if($this->config['exp_strip_tags']) $c_value = strip_tags($c_value);
            
            $line[] = $c_value;
            
        }
        fputcsv($fp, $line, ';', '"');
    }
    fclose($fp);
    unset($fp);

    $this->downloadFile($filepath,$filename);
}




/**
 * Экспорт товаров в XLS файл
 * 
 * @param int $parent_id
 */
function xls_export($parent_id, $xls_type='Excel5'){
    require_once realpath(dirname(__FILE__)).'/PHPExcel.php';

    if($this->config['include_categories']){
        $this->categories = $this->getChildCategories($parent_id);
        $parent_id_arr = $this->getChildCategoriesID($parent_id);
    }else{
        $this->categories = array();
        $parent_id_arr = array($parent_id);
    }
    
    list($products,$prodIds) = $this->getProducts($parent_id_arr);
    
    if(count($products)==0){
        $this->message = "В выбранной категории нет продуктов.";
        return;
    }
    $tmplVars = $this->getTmplVars($prodIds);
    
    $filename = "exp_".date("dmy_His").($xls_type == 'Excel2007' ? ".xlsx" : ".xls");
    $filepath = $this->config['files_export_dir'].$filename;
    
    $objPHPExcel = new PHPExcel();
    $objWorksheet = $objPHPExcel->setActiveSheetIndex(0);

    //write header row
    if($this->config['include_captions']){
        foreach($this->config['content_row'] as $key => $val){
            $objWorksheet->setCellValueByColumnAndRow($key,1,$val[0]);
            $objWorksheet->getStyleByColumnAndRow($key,1,$val[0])->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB('D3D3D3');
            //$objWorksheet->getColumnDimension($key)->setWidth(200);//setAutoSize(true);
        }
    }
    unset($key,$val);
    
    $start = $this->config['include_captions'] ? 2 : 1;
    foreach($products as $key => $val){
        
        $categoryNames = $this->getCategoryNames($val['parent']);
        
        $val = filter_export($val);
        $tv_arr = isset($tmplVars[$val['id']]) ? filter_export($tmplVars[$val['id']],$val['id']) : array();
        foreach($this->config['content_row'] as $k => $v){
            if($v[1][1]=='content'){
                $cell = isset($val[$v[1][0]]) ? trim($val[$v[1][0]]) : '';
            }else if($v[1][1]=='tv'){
                $cell = isset($tv_arr[$v[1][0]]) ? trim($tv_arr[$v[1][0]]) : '';
            }else if($v[1][1]=='category'){
                $cell = isset($categoryNames[$k]) ? $categoryNames[$k] : '';// $this->setParentCategoryExport($val['parent'],$categoryCount-$k-1);
            }
            //$cell = iconv('UTF-8','windows-1251',$cell);
            $cell = str_replace('=','\=',$cell);
            
            $objWorksheet->setCellValueByColumnAndRow($k,$key+$start,$cell);
            
            //$objWorksheet->getColumnDimension($k)->setAutoSize(true);
        }
    }
    
    $objPHPExcel->setActiveSheetIndex(0);

    if($xls_type=='Excel2007'){
        $objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
    }else{
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
    }
    
    $objWriter->save($filepath);
    $this->downloadFile($filepath,$filename);
    
}



/**
 * Выставляем нужные заголовки для скачивания файла
 * 
 * @param string $filepath
 * @param string $filename
 */
function downloadFile($filepath,$filename){
    //header("Content-type: text/plain; charset=utf-8");
    header("Content-type: application/text; charset=windows-1251");
    header("Content-Disposition: attachment; filename=\"{$filename}\"");

    $fp = fopen($filepath, "r");
    $fcontent = fread($fp, filesize($filepath));
    fclose($fp);

    if($this->config['exp_delete_file']) unlink($filepath);

    echo $fcontent;
    exit;
}


/**
 * Собираем массив дочерних категорий (ID + названия)
 * 
 * @param int $parent_id
 * @return array
 */
function getChildCategories($parent_id){
    $categories = array();
    
    $childrens = $this->modx->getChildIds($parent_id);
    $childrens = is_array($childrens) ? array_values($childrens) : array();
    
    if(count($childrens)>0){
        $sql = "SELECT id, pagetitle, parent FROM ".$this->modx->getFullTableName('site_content')." WHERE id IN (".implode(',',$childrens).")";
        $sql .= $this->config['content_type']=='documents' ? " AND isfolder = 1;" : ';';
        $rs = $this->modx->db->query($sql);
        if($this->modx->db->getRecordCount($rs)>0){
            while($row = $this->modx->db->getRow($rs)){
                $categories[$row['parent']][$row['id']] = trim($row['pagetitle']);
            }
        }
    }
    return $categories;
}


/**
 * Собираем массив ID дочерних категорий
 * 
 * @param int $parent_id
 * @return array
 */
function getChildCategoriesID($parent_id){
    $categories = array();

    $childrens = $this->modx->getChildIds($parent_id);
    $childrens = is_array($childrens) ? array_values($childrens) : array();
    
    if(count($childrens)>0){
        $sql = "SELECT id, pagetitle FROM ".$this->modx->getFullTableName('site_content')." WHERE id IN (".implode(',',$childrens).")";
        $sql .= $this->config['content_type']=='documents' ? " AND isfolder = 1;" : ';';
        $rs = $this->modx->db->query($sql);
        if($this->modx->db->getRecordCount($rs)>0){
            while($row = $this->modx->db->getRow($rs)){
                $categories[] = $row['id'];
            }
        }
    }
    return $categories;
}


/**
 * Устанавливаем ID категории для товара и создаем категории, если нужно
 * 
 * @param int $parent_id
 * @return int
 */
function setParentCategory($category_name,$parent_id){
    
    $categoryId = $parent_id;
    
    if(!$this->config['imp_testmode']){
        foreach($category_name as $key => $val){
            
            if(empty($val[1])) continue;
            if(!isset($this->categories[$parent_id])) $this->categories[$parent_id] = array();
            
            if(!in_array($val[1],$this->categories[$parent_id])){
                $alias = $this->config['imp_autoalias'] ? $this->makeAlias($val[1]) : '';
                $categoryId = $this->modx->db->insert(array('pagetitle'=>$val[1],'alias'=>$alias,'template'=>$val[0],'parent'=>$parent_id,'isfolder'=>1,'published'=>1),$this->modx->getFullTableName("site_content"));
                $this->categories[$parent_id][$categoryId] = trim($val[1]);
                $parent_id = $categoryId;
            }else{
                $parent_id = array_search($val[1], $this->categories[$parent_id]);
            }
            
        }
    }
    
    return $parent_id;
}


/**
 * Массив названий категорий товара
 * 
 * @param integer $parent_id
 * @return array 
 */
function getCategoryNames($parent_id){
    $names = array();
    $count = 0;
    foreach($this->config['content_row'] as $key => $val){
        if($val[1][1] == 'category') $count++;
    }
    
    for($i=1;$i<=$count;$i++){
        $tempName = $this->setParentCategoryExport($parent_id,$count-$i);
        if($tempName) $names[] = $tempName;
    }
    
    return $names;
}


/**
 * Функция возвращает имя категории. Используется при экспорте товаров.
 * 
 * @param integer $parent_id
 * @param integer $index
 * @return string
 */
function setParentCategoryExport($parent_id,$index){
    $name = '';
    $count = 0;
    foreach($this->categories as $key => $val){
        if(in_array($parent_id, array_keys($val))){
            $name = trim($val[$parent_id]);
            //$parent_id = $key;
            if($index > $count){
                $name = $this->setParentCategoryExport($key,$index-1);//$this->setParentCategoryExport($parent_id,$index-1);
            }
            $count++;
        }
    }
    
    return $name;
}



/**
 * Очищаем категорию
 * 
 * @param int $parent_id
 */
function cleanParent($parent_id){

    //delete categories
    if($this->config['content_type']=='documents' || $this->config['include_categories']){
        $parentIds = $this->getChildCategoriesID($parent_id);
        
        $parentIds_str = implode(',',$parentIds);
        if(count($parentIds)>0 && $this->config['delete_subcategories']){
            $this->modx->db->delete($this->modx->getFullTableName('site_content'), "id IN ($parentIds_str)");
            $this->modx->db->delete($this->modx->getFullTableName('site_tmplvar_contentvalues'), "contentid IN ($parentIds_str)");
        }
        $parentIds_str .= strlen($parentIds_str)>0 ? ','.$parent_id : $parent_id;
    }else{
        $parentIds_str = $parent_id;
    }
    
    //get products IDs
    if(isset($this->config['imp_content_default']['content']['template'])){
        $where_q = " AND template = '".$this->config['imp_content_default']['content']['template']."' AND isfolder = 0";
    }else{
        if(!$this->config['delete_subcategories']) $where_q = " AND isfolder = 0";
    }
    $ids_res = $this->modx->db->select("id",$this->config['content_table'],"parent IN ($parentIds_str)".$where_q);
    $ids_arr = array();
    if($ids_res){
        while ($row = $this->modx->db->getRow($ids_res)){
            $ids_arr[] = $row['id'];
        }
        unset($row);
    }
    
    if(count($ids_arr)>0){
      //delete products
      $this->modx->db->delete($this->config['content_table'],"id IN (".implode(',',$ids_arr).")");
      //delete TVs
      $this->modx->db->delete($this->config['tmplvar_content_table'], "contentid IN (".implode(',',$ids_arr).")");
    }

    //clear auto_increment
    $this->clearAutoIncrement($this->config['content_table']);
    $this->clearAutoIncrement($this->config['tmplvar_content_table']);
    $this->message = "Категория очищена.";

}

/**
 * Удаляет значения TV-параметров, которые не привязаны ни к одному документы
 * 
 */
function cleanHermitTVvalues(){
    $query = '
        SELECT tvc.id FROM '.$this->config['tmplvar_content_table'].' tvc
        WHERE tvc.contentid NOT IN (SELECT sc.id FROM '.$this->config['content_table'].' sc)
    ';
    $result = $this->modx->db->query($query);
    $ids_arr = array();
    if($this->modx->db->getRecordCount($result)>0){
        while($row = $this->modx->db->getRow($result)){
            array_push($ids_arr,$row['id']);
        }
        $this->modx->db->delete($this->config['tmplvar_content_table'],"id IN (".implode(',',$ids_arr).")");
    }
}

/**
 * Удаляет товары (документы), у которых нет родителя
 * 
 */
function cleanHermitContent(){
    $query = '
        SELECT sc.id FROM '.$this->config['content_table'].' sc
        WHERE sc.parent <> 0 AND sc.parent NOT IN (SELECT scp.id FROM '.$this->config['content_table'].' scp)
    ';
    $result = $this->modx->db->query($query);
    $ids_arr = array();
    if($this->modx->db->getRecordCount($result)>0){
        while($row = $this->modx->db->getRow($result)){
            array_push($ids_arr,$row['id']);
        }
        $this->modx->db->delete($this->config['content_table'],"id IN (".implode(',',$ids_arr).")");
    }
}


/**
 * Сбрасываем auto_increment ID
 * 
 * @param string $tablename
 */
function clearAutoIncrement($tablename){
    $id_content_max = $this->modx->db->getValue($this->modx->db->select("MAX(id)",$tablename));
    if(!$id_content_max) $id_content_max = 0;
    $this->modx->db->query("ALTER TABLE {$tablename} AUTO_INCREMENT = ".($id_content_max+1));
}


/**
 * Очищаем директорию от файлов
 * 
 * @param string $dir
 */
function cleanDir($dir_path){
    $dir = opendir(realpath($dir_path));
    while($f = readdir($dir)){
        if(is_file($dir_path.$f))
            unlink($dir_path.$f);
    }
    closedir($dir);
    $this->message = "Все файлы удалены из папки $dir_path.";
}


/**
 * Считаем файлы в директории
 * 
 * @param string $dir_path
 * @return int
 */
function countFiles($dir_path){
    $f_count = 0;
    $dir = opendir(realpath($dir_path));
    while($f = readdir($dir)){
        if(is_file($dir_path.$f)) $f_count++;
    }
    closedir($dir);
    return $f_count;
}


/**
 * Составляем список конфигурационных файлов
 * 
 * @return string
 */
function configList(){
    $output = '';
    $dir = opendir(realpath($this->config['files_config_dir']));
    while($f = readdir($dir)){
        if(is_file($this->config['files_config_dir'].$f)) $output .= '<option value="'.substr($f,0,-4).'">'.substr($f,0,-4).'</option>'."\n";
    }
    closedir($dir);
    return $output;
}


/**
 * Составляем список файлов для импорта
 * 
 * @return string
 */
function filesList(){
    $output = '';
    $dir = opendir(realpath($this->config['files_import_dir']));
    while($f = readdir($dir)){
        if(is_file($this->config['files_import_dir'].$f)) $output .= '<option value="'.$f.'""> '.$f.'</option>'."\n";
    }
    closedir($dir);
    return $output;
}

/**
 * Создание псевдонима в транслите
 * 
 * @param string $str
 * @return string
 */
function makeAlias($str){
    $str = mb_strtolower($str, mb_detect_encoding($str));
    $str = strtr($str, 
        array(
            " "=>"-", "."=>"", ","=>"", "$"=>"", "?"=>"", "!"=>"", "\""=>"", "'"=>"", "/"=>"",
            "\\"=>"", "("=>"", ")"=>"", "{"=>"", "}"=>"", "["=>"", "]"=>"", "+"=>"-", "&"=>"",
            "?"=>"", "!"=>"", "«"=>"", "»"=>""
        )
    );
    if($this->config['imp_autoalias'] !== 'notranslit'){
      $str = strtr($str, 
          array(
              "а"=>"a","б"=>"b","в"=>"v","г"=>"g","д"=>"d","е"=>"e","ё"=>"yo","ж"=>"zh","з"=>"z",
              "и"=>"i","й"=>"y","к"=>"k","л"=>"l","м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
              "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h","ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch",
              "ь"=>"","ъ"=>"","ы"=>"y","э"=>"e","ю"=>"yu","я"=>"ya"
          )
      );
    }
    return $str;
}


}


?>