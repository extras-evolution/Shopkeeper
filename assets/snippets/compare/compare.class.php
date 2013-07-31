<?php

/**
 * prodCompare Class
 *
 * @package compare
 * @version 1.1
 * @author Andchir <andchir@gmail.com>
 */

class prodCompare {
    
    public $config = array();
    public $modx = null;
    public $phx = null;
    
    function __construct(&$modx, $config = array()){
        $this->modx = $modx;
        
        $this->config = array_merge(array(
            'tplPath' => '',
            'tbl_catalog' => 'modx_site_content',
            'tbl_catalog_tv' => 'modx_site_tmplvar_contentvalues',
            'renderTVDisplayFormat' => true,
            'limitProducts' => 0,
            'onlyThisParentId' => false,
            'toCompare_tpl' => '@CODE:',
            'product_tpl' => '@CODE:',
            'comparePageId' => '1',
            'filterTVID' => '',
            'removeLastTwo' => true
        ),$config);
        
        if (!class_exists("PHxParser")){
            require_once(dirname(__FILE__)."/phx.parser.class.inc.php");
        }
        $this->phx = new PHxParser(0,1000);
        
    }
    
    /**
     * Вытаскикает код чанков
     * 
     * @param string $tpl
     * @return string
     */
    function fetchTpl($tpl){
        $template = "";
        if(substr($tpl,-4)=='.tpl' && substr($tpl, 0, 6) == "@FILE:"){
            $tpl_file = MODX_BASE_PATH . $this->config['tplPath'] . substr($tpl, 6);
            if(file_exists($tpl_file)){
              $template = file_get_contents(trim($tpl_file));
            }
        }else if(substr($tpl, 0, 6) == "@CODE:"){
            $template = substr($tpl, 6);
        }else if($this->modx->getChunk($tpl) != ""){
            $template = $this->modx->getChunk(trim($tpl));
        }else{
            $template = $tpl;
        }
        return $template;
    }
    
    /**
     * Парсит шаблон, заменяя плейсхолдеры значениями
     * 
     * @param string $chunkname
     * @param array $chankArr
     * @return string
     */
    function parseTplChunk($chunkname,$chankArr=array()){
        $chunk = $this->fetchTpl($chunkname);
        foreach ($chankArr as $key => $value){
          $chunk = str_replace("[+".$key."+]", $value, $chunk);
        }
        return $chunk;
    }
    
    /**
     * Ставит плейсхолдеры для PHx
     * 
     * @param string $value
     * @param string $key
     * @param string $path
     */
    function setPlaceholders($value = '', $key = '', $path = ''){
        $keypath = !empty($path) ? $path . "." . $key : $key;
        $this->phx->curPass = 0;
        if(is_array($value)){
            foreach ($value as $subkey => $subval) {
                $this->setPlaceholders($subval, $subkey, $keypath);
            }
        }else{
            $this->phx->setPHxVariable($keypath, $value);
        }
    }
    
    /**
     * Чистит лишние (которые без значений) плейсхолдеры и TV-параметры
     *     
     * @param string $string
     * @return string
     */
    function cleanPHx($string){
        preg_match_all('~\[(\+|\*|\()([^:\+\[\]]+)([^\[\]]*?)(\1|\))\]~s', $string, $matches);
        if ($matches[0]) {
            $string = str_replace($matches[0], '', $string);
        }
        return $string;
    }
    
    /**
     * Вытаскивает товары из БД
     * 
     * @return array
     */
    function getProducts($prodIds=''){
        
        if(!$prodIds) return array();
        
        $prodIdsArr = explode(',',str_replace(' ','',$prodIds));
        
        $query = "
            SELECT id, pagetitle, alias, template FROM ".$this->config['tbl_catalog']."
            WHERE id IN ({$prodIds}) AND published = '1'
            ORDER BY FIND_IN_SET(id,'{$prodIds}')
        ";
        if($this->config['limitProducts'] && is_numeric($this->config['limitProducts'])) $query .= " LIMIT ".$this->config['limitProducts'];
        $result = $this->modx->db->query($query);
        
        $products = array();
        
        if($this->modx->db->getRecordCount($result)>0){
            if($this->modx->db->getRecordCount($result)){
              while($row = $this->modx->db->getRow($result)){
                $products[] = $row;
              }
            }
            
            $tvVars = $this->getTmplVars($prodIdsArr);
            $products = $this->mergeContentAndTV($products,$tvVars);
        }
        
        return $products;
        
    }
    
    /**
     * Вытаскивает из БД TV параметры
     * 
     * @param array $contentIds
     * @return array
     */
    function getTmplVars($contentIds){
        $templateVars = array();
        if($this->config['renderTVDisplayFormat']){
            include_once MODX_MANAGER_PATH."includes/tmplvars.format.inc.php";
            include_once MODX_MANAGER_PATH."includes/tmplvars.commands.inc.php";
        }
        if(count($contentIds)>0){
          
          $query1 = "
            SELECT tv.name, tv.default_text FROM ".$this->modx->getFullTableName('site_tmplvars')." tv
            LEFT JOIN ".$this->modx->getFullTableName('site_tmplvar_templates')." stvt ON stvt.tmplvarid = tv.id
            WHERE stvt.templateid IN (SELECT DISTINCT template FROM ".$this->config['tbl_catalog']." WHERE id IN (".implode(',',$contentIds)."))
          ";
          $tv_names_res = $this->modx->db->query($query1);
          $tv_names = array();
          
          if($this->modx->db->getRecordCount($tv_names_res)>0){

            $query2 = "
              SELECT tv.name, tvc.value, tvc.contentid, tv.display, tv.display_params, tv.type
              FROM ".$this->modx->getFullTableName('site_tmplvars')." tv, ".$this->config['tbl_catalog_tv']." tvc
              WHERE tv.id = tvc.tmplvarid AND tvc.contentid IN (".implode(',',$contentIds).")
            ";
            $tv_values_res = $this->modx->db->query($query2);
            
            if($this->modx->db->getRecordCount($tv_values_res)===0) return $templateVars;
              
            //get tv names
            while($row = $this->modx->db->getRow($tv_names_res)){
                $tv_names[$row['name']] = $row['default_text'];
            }
            unset($row);
            
            //get tv values
            while($row = $this->modx->db->getRow($tv_values_res)){
                if($this->config['renderTVDisplayFormat']){
                      $tvout = getTVDisplayFormat($row['name'], $row['value'], $row['display'], $row['display_params'], $row['type'], $row['contentid']);
		      $templateVars[$row['contentid']] = !isset($templateVars[$row['contentid']]) ? array($row['name']=>$tvout) : array_merge($templateVars[$row['contentid']],array($row['name']=>$tvout));
                }else{
                      $templateVars[$row['contentid']] = !isset($templateVars[$row['contentid']]) ? array($row['name']=>$row['value']) : array_merge($templateVars[$row['contentid']],array($row['name']=>$row['value']));
                }
            }
            unset($row);
            
            //fill holes
            foreach($templateVars as $key => &$val){
                $hole = array_diff(array_keys($tv_names),array_keys($val));
                foreach($hole as $k => $v) $val[$v] = str_replace('||','',$tv_names[$v]);
            }
          
          }
            
        }
        return $templateVars;
    }
    
    /**
     * Соединяет массивы полей товара и его TV
     * 
     * @param array $cont_arr
     * @param array $tv_arr
     * @return array
     */
    function mergeContentAndTV($cont_arr,$tv_arr){
        if(!is_array($cont_arr)) $cont_arr = array();
        if(!is_array($tv_arr)) $tv_arr = array();
        if(count($cont_arr)==0 && count($tv_arr)==0) return array();
        foreach($cont_arr as $key => &$val){
            $tvs = isset($tv_arr[$val['id']]) ? $tv_arr[$val['id']] : array();
            $val = array_merge($val,$tvs);
        }
        return $cont_arr;
    }
    
    /**
     * Возвращает URL категорий товара
     * 
     * @param array $parents
     * @return array
     */
    function getParentsPaths($parents){
        if(!is_array($parents) && count($parents)>0) return array();
        $this->qsStart = $this->modx->config['friendly_urls'] ? '?' : '&amp;';
        $parentsPaths = array();
        foreach($parents as $key => $val){
            $prodId = (int)$val;
            $parentsPaths[$prodId] = $this->modx->makeUrl($prodId);
        }
        return $parentsPaths;
    }
    
    /**
     * Возвращает названия параметров, по которым будет происходить сравнение
     *
     */
    function getParameters($template=''){
        $out = array(array(),array());
        
        $compareIds = !empty($_COOKIE['shkCompareIds']) ? $_COOKIE['shkCompareIds'] : '';
        
        //определяем список TV ID, которые не нужно выводить для категории данной товаров
        $categoryId = isset($_COOKIE['shkCompareParent']) ? $_COOKIE['shkCompareParent'] : 0;
        $temp_ct_tvids = explode('||',str_replace(' ','',$this->config['filterTVID']));
        $filterTVID = array();
        if($categoryId && count($temp_ct_tvids)>0){
            foreach($temp_ct_tvids as $key => $val){
                $temp = explode('~',$val);
                if(count($temp_ct_tvids)>0 && $key==0) $filterTVID = isset($temp[1]) ? explode(',',$temp[1]) : explode(',',$temp[0]);
                if(isset($temp[1]) && $temp[0]==$categoryId){
                    $filterTVID = explode(',',$temp[1]);
                    break;
                }
            }
            unset($key,$val);
        }
        
        if($compareIds){
            
            $prodIdsArr = explode(',',str_replace(' ','',$compareIds));
            $query = "
                SELECT tv.id, tv.name, tv.caption FROM ".$this->modx->getFullTableName('site_tmplvars')." tv
                LEFT JOIN ".$this->modx->getFullTableName('site_tmplvar_templates')." tvt ON tv.id = tvt.tmplvarid
                WHERE tvt.templateid = '{$template}'
            ";
            if(count($filterTVID)>0 && !empty($filterTVID[0])) $query .= " AND tv.id NOT IN (".implode(',',$filterTVID).")";
            $query .= "
                ORDER BY tv.rank ASC
            ";
            $result = $this->modx->db->query($query);
            if($this->modx->db->getRecordCount($result)>0){
                while($row = $this->modx->db->getRow($result)){
                    $out[0][] = $row['name'];
                    $out[1][] = $row['caption'];
                }
            }
            
        }
        
        return $out;
    }
    
    /**
     * Создает URL для товара
     * 
     * @param int $parent
     * @param int $id
     * @return string
     */
    function makeURL($parent,$id,$alias=''){
        if(!isset($this->qsStart)) $this->qsStart = '?';
        if($this->config['dataType']=='products'){
            $parentUrl = isset($this->parentsUrls[$parent]) ? $this->parentsUrls[$parent] : $this->modx->makeUrl($parent);
            if($this->config['friendlyURL']){
                $output = $link = preg_replace('/\.(.*)$/','',$parentUrl).'/'.$this->config['id_prefix'].'p/'.$alias.'.html';
            }else{
                $output = $parentUrl.$this->qsStart.$this->config['id_prefix'].'p='.$id;
            }
        }else{
            $output = $this->modx->makeUrl($id);
        }
        return $output;
    }
    
    /**
     * Возвращает HTML-код вывода ссылки на страницу сравнения с числом выбранных товаров по шаблону
     * 
     * @return string
     */
    function toCompareContent(){
        
        $scriptCode = '
            <script type="text/javascript" src="'.MODX_BASE_URL.'assets/snippets/compare/js/compare.js"></script>
            <script type="text/javascript">
                var cmp_config = {"limitProducts":'.$this->config['limitProducts'].', "onlyThisParentId":'.(!empty($this->config['onlyThisParentId']) && is_numeric($this->config['onlyThisParentId']) ? $this->config['onlyThisParentId'] : 'false').'};
            </script>
        ';
        $this->modx->regClientStartupScript($scriptCode);
            
        $compareIds_arr = !empty($_COOKIE['shkCompareIds']) ? explode(',',$_COOKIE['shkCompareIds']) : array();
        
        $chunkArr = array(
            'count_current' => count($compareIds_arr),
            'count_max' => $this->config['limitProducts'],
            'href_compare' => $this->modx->makeUrl($this->config['comparePageId']),
            'href_cancel' => $this->modx->documentIdentifier ? $this->modx->makeUrl($this->modx->documentIdentifier,'','&cmp_action=empty') : '',
            'display_cancel' => count($compareIds_arr)==0 ? 'none' : 'inline'
        );
        
        return $this->parseTplChunk($this->config['toCompare_tpl'],$chunkArr);
    }
    
    /**
     * Выводит список ID товаров, выбранных для сравнения
     *
     */
    function printIDList(){
        $compareIds = !empty($_COOKIE['shkCompareIds']) ? $_COOKIE['shkCompareIds'] : '';
        return $compareIds;
    }
    
    /**
     * Выводит таблицу с параметрами товаров, выбранных для сравнения
     *
     */
    function printCompareProducts(){
        $out = '';
        $tpl = $this->fetchTpl($this->config['product_tpl']);
        $tpl_arr = explode('<!--tpl_separator-->',$tpl);
        if(count($tpl_arr)<7) return '[Ошибка] Шаблон не соответствует правилам.';
        
        $compareIds = !empty($_COOKIE['shkCompareIds']) ? $_COOKIE['shkCompareIds'] : '';
        if($compareIds){
            $products = $this->getProducts($compareIds);
            
            if(count($products)>0){
                $template_id = $products[0]['template'];
                $parameters = $this->getParameters($template_id);
                
                $out .= $tpl_arr[0];//верхняя часть таблицы
                
                //верхняя строка таблицы
                if(preg_match('/[\w]/',$tpl_arr[2])){
                    $this->phx->placeholders = array();
                    $out .= $this->phx->Parse($tpl_arr[2]);
                    foreach($products as $key => $prod){
                        $c_classes = $key%2==0 ? 'even' : 'odd';
                        if($key+1==count($products)) $c_classes .= ' last';
                        $this->phx->placeholders = array();
                        $this->setPlaceholders(array_merge($prod,array('iteration'=>$key+1,'classes'=>$c_classes)));
                        $out .= $this->phx->Parse($tpl_arr[3]);
                    }
                    unset($key,$prod);
                }
                
                //строки с параметрами товаров
                foreach($parameters[0] as $p_key => $p_name){
                    $row_str = '';
                    
                    $iteration = $p_key+1;
                    $param_name = $parameters[1][$p_key];
                    $r_classes = $iteration%2==0 ? 'even' : 'odd';
                    if($iteration==count($parameters[0])) $r_classes .= ' last';
                    
                    //Строка с наименованием параметра
                    $this->phx->placeholders = array();
                    $this->setPlaceholders(array('param_name'=>$param_name,'row_number'=>$iteration));
                    $row_str .= $this->phx->Parse($tpl_arr[4]);
                    
                    //строка с параметрами
                    foreach($products as $key => $prod){
                        $c_classes = $key%2==0 ? 'even' : 'odd';
                        if($key+1==count($products)) $c_classes .= ' last';
                        $chunkArr = array(
                            'param_name'=>$param_name,
                            'tv_name'=>$p_name,
                            'param_value'=>(isset($prod[$p_name]) ? $prod[$p_name] : ''),
                            'iteration'=>$key+1,
                            'row_number'=>$iteration,
                            'classes'=>$c_classes
                        );
                        $this->phx->placeholders = array();
                        $this->setPlaceholders($chunkArr);
                        $row_str .= $this->phx->Parse($tpl_arr[5]);
                    }
                    $this->phx->placeholders = array();
                    $this->setPlaceholders(array('inner'=>$row_str,'classes'=>$r_classes));
                    $out .= $this->phx->Parse($tpl_arr[1]);
                }
                
                $out .= $tpl_arr[6];//нижняя часть таблицы
                
            }
            
        }
        return $out;
    }
    
    /**
     * Удаление товара из списка для сравнения
     *
     */
    function deleteCompareProduct(){
        $prod_id = isset($_GET['pid']) && is_numeric($_GET['pid']) ? $_GET['pid'] : 0;
        $compareIds = !empty($_COOKIE['shkCompareIds']) ? $_COOKIE['shkCompareIds'] : '';
        if($prod_id && $compareIds){
            $prodIdsArr = explode(',',str_replace(' ','',$compareIds));
            $out_arr = array();
            foreach($prodIdsArr as $key => $id){
                if($id!=$prod_id) array_push($out_arr,$id);
            }
            if(($this->config['removeLastTwo'] && count($out_arr)==1) || count($out_arr)==0){
                setcookie('shkCompareParent', '', 0, '/');
                setcookie('shkCompareIds', '', 0, '/');
            }else{
                setcookie('shkCompareIds', implode(',',$out_arr), time()+3600*24*365, '/');
            }
            
        }
        $this->modx->sendRedirect($this->modx->makeUrl($this->modx->documentIdentifier),0,'REDIRECT_HEADER');
        exit;
    }
    
    /**
     * Очищает список ID товаров, выбранных для сравнения
     *
     */
    function emptyCompare(){
        setcookie('shkCompareParent', '', 0, '/');
        setcookie('shkCompareIds', '', 0, '/');
        $this->modx->sendRedirect($this->modx->makeUrl($this->modx->documentIdentifier),0,'REDIRECT_HEADER');
        exit;
    }

}

?>