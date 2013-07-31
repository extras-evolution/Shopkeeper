<?php

/**
 * @name catalogView
 * @author Andchir <andchir@gmail.com>
 * @version 1.2.5
 */

class catalogView {

    /**
     *
     * @param object $modx
     * @param array $config
     */
    function __construct($modx,$config=array()){

        if (!class_exists("PHxParser")) include_once(strtr(realpath(dirname(__FILE__))."/phx.parser.class.inc.php", '\\', '/'));

        $this->modx = $modx;
        $this->phx = new PHxParser(0,1000);
        $this->config = array_merge(array(
            'id' => '',
            'parents' => 0,
            'paginate' => 0,
            'content_fields' => array('id','pagetitle','alias','published','parent','isfolder','introtext','content','template','menuindex','createdon','hidemenu'),
            'filter_modes' => array('<>','=','<>','>=','<=','>','<','NOT LIKE','LIKE'),
            'sqlFilter' => true,
            'globalFilterDelimiter' => '|',
            'localFilterDelimiter' => ',',
            'randomize' => '0',
            'sortBy' => 'id',
            'sortDir' => 'asc',
            'start' => 0,
            'display' => 0,
            'fetchContent' => false,
            'renderTVDisplayFormat' => false
        ),$config);
        $prodIds = strlen($this->config['products'])>0 ? $this->config['products'] : $this->config['parents'];
        $this->parentsUrls = $this->getParentsPaths(explode(',',$prodIds));

    }

  /**
   *
   * @param string $tpl
   * @return boolean
   */
    function fetchTpl($tpl){
        $template = "";
        if(substr($tpl, 0, 6) == "@FILE:"){
          $tpl_file = MODX_BASE_PATH . substr($tpl, 6);
                $template = file_get_contents($tpl_file);
        }else if(substr($tpl, 0, 6) == "@CODE:"){
                $template = substr($tpl, 6);
        }else if($this->modx->getChunk($tpl) != ""){
                $template = $this->modx->getChunk($tpl);
        }else{
                $template = false;
        }
        return $template;
    }
    
  /**
   *
   * @param string $str
   * @return boolean
   */
    function isUTF8($str){
        if($str === mb_convert_encoding(mb_convert_encoding($str, "UTF-32", "UTF-8"), "UTF-8", "UTF-32"))
          return true;
        else
          return false;
    }
    
    /**
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
     *
     * @param array $placeholdersArr
     * @return null
     */
    function setMODxPlaceholders($placeholdersArr=array()){
        if(!is_array($placeholdersArr) || count($placeholdersArr)==0) return false;
        $prefix = $this->config['id'] ? $this->config['id'].'.' : '';
        foreach($placeholdersArr as $name => $value){
            $this->modx->setPlaceholder($prefix.$name, $value);
        }
    }

    /**
     *
     * @param string $chunkname
     * @param array $chankArr
     * @return string
     */
    function parseTplChunk($chunkname,$chankArr=array()){
        $chunk = fetchTpl($chunkname);
        foreach ($chankArr as $key => $value){
          $chunk = str_replace("[+".$key."+]", $value, $chunk);
        }
        return $chunk;
    }

    /**
     *
     * @param array $resource
     * @param array $orderBy
     * @param array $orderBy_default
     * @return array
     */
    function multiSort($array,$orderBy,$orderBy_default = array('pagetitle','ASC')) {
        if(count($array)==0) return $array;

        list($sort_by,$sort_dir) = $orderBy;
        list($def_sort_by,$def_sort_dir) = $orderBy_default;

        foreach ($array as $key => $row) {
            $sorting1[$key] = $row[$sort_by];
            $sorting2[$key] = $row[$def_sort_by];
        }

        if($orderBy_default[0] != $sort_by[0])
            array_multisort($sorting1, strtoupper($sort_dir)=='ASC' ? SORT_ASC : SORT_DESC, $sorting2, strtoupper($def_sort_dir)=='ASC' ? SORT_ASC : SORT_DESC, $array);
        else
            array_multisort($sorting1, strtoupper($sort_dir)=='ASC' ? SORT_ASC : SORT_DESC, $array);

        return $array;
    }

    /**
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
     *
     * @return array
     */
    function getProducts($prodIds=''){

        $is_parents = $prodIds || strlen($this->config['products'])>0 ? false : true;
        $tv_sorting = !in_array($this->config['sortBy'],$this->config['content_fields']) && strlen($this->config['orderBy'])==0 ? true : false;
        
        if(strlen($this->config['filter'])>0)
            $filter = $this->parseFilters($this->config['filter'],$this->config['globalFilterDelimiter'],$this->config['localFilterDelimiter']);
        else
            $filter = array("basic"=>array(),"custom"=>array());
        
        if(!$prodIds){
            $prodIds = $is_parents ? $this->config['parents'] : $this->config['products'];
        }

        if(strlen($prodIds)==0) return array(0,array(),array());

        if(strlen($this->config['orderBy'])==0){
          $this->config['orderBy'] = count($filter['basic'])==0 ? $this->config['sortBy']." ".$this->config['sortDir'] : 'sc.id desc';
        }

        //create query
        $query = '';
        $q_sort = '';
        if($tv_sorting && count($filter['basic'])==0){
            $tv_id = $this->modx->db->getValue($this->modx->db->select("id",$this->modx->getFullTableName("site_tmplvars"),"name = '".$this->config['sortBy']."'"));
            if(!$tv_id) $tv_id = 0;
            $q_sort = ", (SELECT value FROM ".$this->config['tbl_catalog_tv']." WHERE tmplvarid = '$tv_id' AND contentid = sc.id LIMIT 1) AS ".str_replace('=','',$this->config['sortBy']);
        }
        $query .= "
          FROM ".$this->config['tbl_catalog']." sc
        ";
        if($this->config['where'] || count($filter['sql'])>0){
          $query .= "
            LEFT JOIN ".$this->config['tbl_catalog_tv']." tvc ON sc.id = tvc.contentid
          ";
        }
        $query .= "
            WHERE sc.".($is_parents ? 'parent' : 'id')." IN(".$prodIds.")
            AND sc.published = '1'
        ";
        if($this->config['dataType']=='documents') $query .= " AND sc.deleted = 0 ";
        
        if($this->config['where']){
            $query .= " AND ".str_replace('@eq','=',$this->config['where']);
        //sql filter
        }else if(isset($filter['sql'][0]) && count($filter['sql'][0])==3){
            
            list($f_name,$f_val,$f_mode) = $filter['sql'][0];
            $f_action = isset($this->config['filter_modes'][$f_mode]) ? $this->config['filter_modes'][$f_mode] : '=';
            if($f_mode == 7 || $f_mode == 8) $f_val = "'%$f_val%'";
            else if(!is_numeric($f_val)) $f_val = "'".$f_val."'";
            //if is content field
            if(in_array($f_name,$this->config['content_fields'])){
                
                $query .= " AND sc.{$f_name} {$f_action} $f_val";
                
            //is TV   
            }else{
                if(!is_numeric($f_name)){
                    $tv_id = $this->modx->db->getValue($this->modx->db->select('id',$this->modx->getFullTableName('site_tmplvars'),"name = '$f_name'"));
                }else{
                    $tv_id = $f_name;
                }
                $q_value = 'tvc.value';//is_numeric($f_val) ? 'CAST(tvc.value AS SIGNED)' : 'tvc.value';
                if($tv_id) $query .= " AND (tvc.tmplvarid = '{$tv_id}' AND {$q_value} {$f_action} {$f_val})";
            }
            
        }
        
        $q_orderby = '';
        if(!$this->config['randomize'] && count($filter['basic'])==0){
            if($this->config['sortBy_type']=='integer'){
                $sortArr = explode(' ',trim($this->config['orderBy']));
                if(!isset($sortArr[1])) $sortArr[1] = 'DESC';
                $q_orderby.= "
                    GROUP BY sc.id
                    ORDER BY CAST(".$sortArr[0]." AS SIGNED) ".$sortArr[1]."
                ";
            }else{
                $q_orderby.= "
                    GROUP BY sc.id
                    ORDER BY ".$this->orderByCheck($this->config['orderBy'])."
                ";
            }
        }
        $q_limit = '';
        if(count($filter['basic'])==0){
            //random start
            if($this->config['randomize']){
                $total = $this->getSQLTotal($query);
                $start = rand(0, ($total > $this->config['display'] ? $total-$this->config['display'] : 0));
                if($this->config['display']){
                  $q_limit .= "
                      LIMIT ".$start.", ".$this->config['display']."
                  ";
                }
            }else{
                if($this->config['display']){
                  $q_limit .= "
                      LIMIT ".$this->config['start'].", ".$this->config['display']."
                  ";
                }
            }

        }
        
        if($this->config['dataType']=='documents' && !$this->config['fetchContent']){
        		$select_fields =  'sc.id, sc.pagetitle, sc.longtitle, sc.menutitle, sc.alias, sc.published, sc.parent, sc.isfolder, sc.introtext, sc.template, sc.menuindex, sc.pub_date, sc.createdon, sc.menuindex';
        }else if($this->config['dataType']=='products' && !$this->config['fetchContent']){
        		$select_fields = 'sc.id, sc.pagetitle, sc.alias, sc.published, sc.parent, sc.isfolder, sc.introtext, sc.template, sc.menuindex, sc.createdon, sc.menuindex';
        }else{
        		$select_fields = 'sc.*';
        }
        //var_dump("SELECT $select_fields $q_sort ".$query.$q_orderby.$q_limit);
        $result = $this->modx->db->query("SELECT $select_fields $q_sort ".$query.$q_orderby.$q_limit);

        $prodIdsArr = array();
        $products = array();
        if($this->modx->db->getRecordCount($result)){
          while($row = $this->modx->db->getRow($result)){
            $products[] = $row;
            $prodIdsArr[] = $row['id'];
          }
        }

        $tvVars = $this->getTmplVars($prodIdsArr);
        $products = $this->mergeContentAndTV($products,$tvVars);

        if(count($filter['basic'])>0){

            if (!class_exists("filter")) include_once(strtr(realpath(dirname(__FILE__))."/filter.class.inc.php", '\\', '/'));
            $filterObj = new filter();
            $products = $filterObj->execute($products, $filter);

            $total = count($products);
            //if($tv_sorting){
            if($total>0){
                if(!$this->config['randomize']){
                    $products = $this->multiSort($products, array($this->config['sortBy'],$this->config['sortDir']));
                }else{
                    shuffle($products);
                }
                $products = $this->limitForPage($products);
            }
        }else{
            //random array
            if($this->config['randomize'] && count($products)>1) shuffle($products);
            if(!isset($total)) $total = $this->getSQLTotal($query);
        }

        return array($total,$prodIdsArr,$products);
    }


    //taken from a snippet Ditto
    /**
     *
     * @param array $filter
     * @param string $globalDelimiter
     * @param string $localDelimiter
     * @return array
     */
    function parseFilters($filter=false,$globalDelimiter='|',$localDelimiter=',') {
        $parsedFilters = array("basic"=>array(),"custom"=>array(),"sql"=>array());
        $filters = explode($globalDelimiter, $filter);
        $f_count = 0;
        if ($filter && count($filters) > 0) {
            foreach ($filters AS $filter) {
                if (!empty($filter)) {
                    $filterArray = explode($localDelimiter, $filter);
                    $source = $filterArray[0];
                    $value = isset ($filterArray[2]) ? $filterArray[1] : 0;
                    $mode = isset ($filterArray[2]) ? $filterArray[2] : 1;
                    if($f_count==0 && $this->config['sqlFilter']){
                        $parsedFilters["sql"][] = array($this->modx->db->escape($source),$this->modx->db->escape($value),$this->modx->db->escape($mode));
                        $f_count++;
                    }else{
                        $parsedFilters["basic"][] = array("source"=>$source,"value"=>$value,"mode"=>$mode);
                    }
                }
            }
        }
        return $parsedFilters;
    }


    /**
     *
     * @return int
     */
    function getSQLTotal($query){
        $output = 0;
        $output = $this->modx->db->getValue($this->modx->db->query("SELECT COUNT(DISTINCT sc.id) ".$query));
        return $output;
    }

    /**
     *
     * @param array $products
     * @return array
     */
    function limitForPage($products){
        return array_slice($products,$this->config['start'],$this->config['display']);
    }

    /**
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
            SELECT tv.name FROM ".$this->modx->getFullTableName('site_tmplvars')." tv
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
                $tv_names[] = $row['name'];
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
                $hole = array_diff($tv_names,array_keys($val));
                foreach($hole as $k => $v) $val[$v] = '';
            }
          
          }
            
        }
        return $templateVars;
    }

    /**
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
     *
     * @param int $parent
     * @param int $id
     * @return string
     */
    function makeURL($parent,$id){
        if(!isset($this->qsStart)) $this->qsStart = '?';
        if($this->config['dataType']=='products'){
            $output = isset($this->parentsUrls[$parent]) ? $this->parentsUrls[$parent].$this->qsStart.$this->config['id_prefix'].'p='.$id : $this->modx->makeUrl($parent,'','&'.$this->config['id_prefix'].'p='.$id);
        }else{
            $output = $this->modx->makeUrl($id);
        }
        return $output;
    }

    /**
     *
     * @param array $productsList
     * @return string
     */
    function renderRows($productsList,$tpl){
        $output = '';
        $row_chunk = $this->fetchTpl($tpl);
        $currentDocId = $this->modx->documentIdentifier ? $this->modx->documentIdentifier : 0;
        foreach($productsList as $key => $val){
          $this->phx->placeholders = array();
          $val['url'] = $this->makeURL($val['parent'],$val['id']);
          $val['thisPageUrl'] = isset($this->config['thisPageUrl']) ? $this->config['thisPageUrl'] : '';
          $val['cv_iteration'] = $key;
          $val['cv_item_num'] = $key+1;
          $val['active'] = $currentDocId==$val['id'] ? 'active' : '';
          $this->setPlaceholders($val);
          $chunk = str_replace(array('[!','!]'),array('[[',']]'),$row_chunk);
          $output .= $this->phx->Parse($chunk);
        }
        $output = $this->cleanPHx($output);
        return $output;
    }

   /**
    *
    * @param string $string
    * @return string
    */
    function orderByCheck($string){
        if(preg_match("/\w+\(.+\)/",$string)) return $string;
        $array = explode(' ',$string);
        if(count($array)<2) return 'sc.id DESC';
        if(in_array($array[0],$this->config['content_fields'])){
            $fieldName = 'sc.'.$array[0];
        }else{
            $fieldName = $array[0];
        }
        $sort_dir = strtoupper($array[1])=='ASC' ? 'ASC' : 'DESC';
        return $fieldName.' '.$sort_dir;
    }


    /**
     *
     * @param string $string
     * @return string
     */
    function cleanPHx($string){
        preg_match_all('~\[(\+|\*|\()([^:\+\[\]]+)([^\[\]]*?)(\1|\))\]~s', $string, $matches);
        if ($matches[0]){
            $string = str_replace($matches[0], '', $string);
        }
        return $string;
    }

}


?>