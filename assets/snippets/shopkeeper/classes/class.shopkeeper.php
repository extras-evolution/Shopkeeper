<?php

/**
 * Shopkeeper
 *
 * Shopping cart class
 *
 * @author Andchir <andchir@gmail.com>
 * @package Shopkeeper
 * @version 1.3.4
 */

class Shopkeeper {

    /**
     *
     * @param object $modx
     * @param array $config
     */
    function __construct(&$modx, $config = array()){

        $this->modx = $modx;

        $this->config = array_merge(array(
            "shkPath" => MODX_BASE_PATH."assets/snippets/shopkeeper",
            "tplPath" => "assets/snippets/shopkeeper/chunks/ru/",
            "linkAllow" => true,
            "currency" => "",
            "noCounter" => false,
            "changePrice" => false,
            "priceTV" => "price",
            "charset" => "",
            "lang" => "russian-UTF-8",
            "excepDigitGroup" => true,
            "allowFloatCount" => false
        ),$config);

        if (!class_exists("PHxParser")){
            require_once(dirname(__FILE__)."/phx.parser.class.inc.php");
        }
        $this->phx = new PHxParser(0,1000);

        if($this->config['charset']=='windows-1251' && $this->isUTF8($this->config['currency'])){
            $this->config['currency'] = iconv("UTF-8", "windows-1251", $this->config['currency']);
        }

        if(file_exists($this->config['shkPath']."/lang/".$this->config['lang'].".php")){
            require_once($this->config['shkPath']."/lang/".$this->config['lang'].".php");
        };

        $this->langTxt = isset($langTxt) ? $langTxt : array();

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
            $template = false;
        }
        return $template;
    }

  /**
   * Проверяет кодировку
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
   * Формат числа
   *   
   * @param float $number
   * @return float
   */
    function numberFormat($number){
        $output = $number;
        if($this->config['excepDigitGroup']==true){
          $output = number_format($number,(floor($number) == $number ? 0 : 2),'.',' ');
        }
        return $output;
    }


  /**
   * Ставит плейсхолдеры в чанки
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
    * Возвращает последний ID таблицы по auto increment
    *    
    * @param string $table_name
    * @return string
    */
    function getNextAutoIncrement($table_name){
        $next_increment = 0;
        $query = $this->modx->db->query("SHOW TABLE STATUS LIKE '$table_name'");
        while($row = mysql_fetch_assoc($query)){
          $next_increment = $row['Auto_increment'];
        }
        return $next_increment;
    }

   /**
    * Сохраняет товары в сессию
    *    
    * @param array $purchaseArray
    * @return null
    */
    function savePurchaseData($purchaseArray){

        if(!isset($purchaseArray['shk-id'])) return;

        $curSavedP = !empty($_SESSION['purchases']) ? unserialize($_SESSION['purchases']) : array();
        $curSavedA = !empty($_SESSION['addit_params']) ? unserialize($_SESSION['addit_params']) : array();
        $purchases = array();
        $addit_params = array();

        $multiplication = false;
        $p_id = 0;

        $is_catalog = isset($purchaseArray['shk-catalog']) && $purchaseArray['shk-catalog']==1 ? true : false;

        $prodIdArr = explode('__',$purchaseArray['shk-id']);
        $p_id = is_numeric($prodIdArr[0]) ? $prodIdArr[0] : 0;
        $price_tv = isset($prodIdArr[1]) ? $prodIdArr[1] : $this->config['priceTV'];

        if(!$is_catalog){

          if(count($this->modx->getDocuments(array($p_id)))==0) return;

          $p_price = $this->modx->getTemplateVarOutput(array($price_tv),$p_id);
          $prod_price = !preg_match("/\D/", $p_price[$price_tv]) ? $p_price[$price_tv] : (preg_match("/[,.]/", $p_price[$price_tv]) ? str_replace(",", ".", $p_price[$price_tv]) : preg_replace("/\D/", "", $p_price[$price_tv]));
          if(!$prod_price) $prod_price = 0;
          $prod_parent = 0;

        }else{

          if($this->modx->db->getValue($this->modx->db->select("count(*)",$this->modx->getFullTableName('catalog'),"id = '$p_id'"))==0) return;
          $prod_parent = $this->modx->db->getValue($this->modx->db->select("parent",$this->modx->getFullTableName('catalog'),"id = '$p_id'"));
          list($price_tv_id,$price_tv_default) = $this->modx->db->getRow($this->modx->db->select("id,default_text",$this->modx->getFullTableName('site_tmplvars'),"name = '$price_tv'"),'num');
          $prod_price = $this->modx->db->getValue($this->modx->db->select("value",$this->modx->getFullTableName('catalog_tmplvar_contentvalues'),"tmplvarid = '$price_tv_id' AND contentid = '$p_id'"));
          if($prod_price===false && !empty($price_tv_default)){
            $prod_price = $price_tv_default;
          }

        }

        //product id
        $purchases[0][0] = $p_id;
        //product count
        $purchases[0][1] = !empty($purchaseArray['count']) && is_numeric($purchaseArray['count']) ? str_replace(',','.',$purchaseArray['count']) : (!empty($purchaseArray['shk-count']) && is_numeric($purchaseArray['shk-count']) ? str_replace(',','.',$purchaseArray['shk-count']) : 1);
        $purchases[0][1] = $this->config['allowFloatCount'] ? floatval($purchases[0][1]) : ceil(floatval($purchases[0][1]));
        //product price
        $purchases[0][2] = $prod_price;
        //product in shk catalog?
        $purchases[0]['catalog'] = $is_catalog ? $prod_parent : 0;
        
        //plugin
        $evtOut = $this->modx->invokeEvent('OnSHKgetProductPrice',array('price' => $purchases[0][2],'purchaseArray' => $purchaseArray));
        if(is_array($evtOut)) $purchases[0][2] = $evtOut[0];

        unset($purchaseArray['shk-id'],$purchaseArray['count'],$purchaseArray['shk-count']);

        foreach($purchaseArray as $key => $value){
          if(preg_match("/".$p_id."/",$key) && strlen($value)>0){
            
            list($a_fieldname,$a_id,$a_string) = explode('__',$key);
            if($a_id != $p_id) continue;
            
            if(isset($a_string) && $a_string=='add'){
                
                list($a_name,$a_price) = array($value,0);
                $purchases[0]['tv_add'][$a_fieldname] = $value;

            }else{
                if(!$is_catalog){
                    $a_val_res = $this->modx->getTemplateVarOutput(array($a_fieldname),$p_id);
                    $a_val_arr = explode('||',$a_val_res[$a_fieldname]);
                }else{
                    $tv_val_result = $this->modx->db->select(
                        "tvc.value",
                        $this->modx->getFullTableName('site_tmplvars')." tv, ".$this->modx->getFullTableName('catalog_tmplvar_contentvalues')." tvc",
                        "tv.id = tvc.tmplvarid AND tv.name = '$a_fieldname' AND tvc.contentid = '$p_id'"
                    );
                    $a_val_res = $this->modx->db->getValue($tv_val_result);
                    $a_val_arr = $a_val_res ? explode('||',$a_val_res) : array();
                }
                list($afi,$afp,$afn) = explode('__',$value);
                list($a_name,$a_price) = !isset($afn) ? explode('==',$a_val_arr[$afi]) : array($afn,0);
            }

            if(strlen($a_price)>0){

              if(substr($a_price,0,1) == '*'){
                $multiplication = true;
                $a_price = (float) substr($a_price,1);
              }else{
                $multiplication = false;
              }

              if($this->config['changePrice'] || $multiplication){
                if(isset($purchases[0][2])){
                  $purchases[0][2] = !$multiplication ? $purchases[0][2]+$a_price : $purchases[0][2]*$a_price;
                }else{
                  $purchases[0][2] = $a_price;
                }
                $a_price = 0;
              }

              $addit_params[0][] = array($this->modx->db->escape($a_name),$this->modx->db->escape($a_price),$this->modx->db->escape($a_fieldname));
              $purchases[0]['tv_add']['shk_'.$a_fieldname] = $this->modx->db->escape($a_name);
              unset($a_name,$a_price);

            }
          }
        }

        ksort($purchases[0]);       
        if(!isset($addit_params[0])) $addit_params[0] = array();
        $intersect = $this->checkIntersect($purchases,$addit_params);
        if($intersect===false){
          $purchasesStr = serialize(array_merge($purchases,$curSavedP));
          $_SESSION['purchases'] = $purchasesStr;
          $addit_paramsStr = serialize(array_merge($addit_params,$curSavedA));
          $_SESSION['addit_params'] = $addit_paramsStr;
        }else{
          $this->recountDataArray($intersect,$purchases[0][1]);
        }

    }

   /**
    * Проверяет существует ли уже добавляемый товар в корзине
    *    
    * @param array $newArrayP
    * @param array $newArrayA
    * @return int
    */
    function checkIntersect($newArrayP,$newArrayA){
        $curSavedP = unserialize($_SESSION['purchases']);
        $curSavedA = !empty($_SESSION['addit_params']) ? unserialize($_SESSION['addit_params']) : false;
        $output = false;
        for($i=0;$i<count($curSavedP);$i++){
          if($curSavedP[$i][0]==$newArrayP[0][0] && $curSavedP[$i][2]==$newArrayP[0][2]){
            if($curSavedA!==false){
              if(serialize($newArrayA[0])==serialize($curSavedA[$i])){
                $output = $i;
                break;
              }
            }else{
              $output = $i;
              break;
            }
          }
        }
        return $output;
    }


   /**
    * Изменяет количество отдельного товара в корзине
    *    
    * @param int $index
    * @param int $count
    * @param boolean $plus
    */
    function recountDataArray($index,$count,$plus=true){
        if($count<=0 || !is_numeric($count)) return;
        else $count = floatval($count);
        if(!$this->config['allowFloatCount']) $count = ceil($count);
        $curSavedP = unserialize($_SESSION['purchases']);
        $outputArray = array();
        for($i=0;$i<count($curSavedP);$i++){
            $outputArray[$i] = $curSavedP[$i];
            if($i==$index){
                $outputArray[$i][1] = $plus==true ? $curSavedP[$i][1]+$count : $count;
            }
        }
        $_SESSION['purchases'] = serialize($outputArray);
    }



   /**
    * Пересчитывает товар с новыми количествами
    *    
    * @param array $countArr
    */
    function recountAll($countArr){
        $curSavedP = unserialize($_SESSION['purchases']);
        $outputArray = $curSavedP;
        for($i=0;$i<count($curSavedP);$i++){
            $newcount = !$this->config['allowFloatCount'] ? ceil(floatval($countArr[$i])) : floatval($countArr[$i]);
            if(!$newcount) $newcount = 1;
            if(!empty($countArr[$i])) $outputArray[$i][1] = abs($newcount);
        }
        $_SESSION['purchases'] = serialize($outputArray);
    }

  /**
   * Удаляет отдельный товар из корзины
   *   
   * @param int $index
   * @return null
   */
  function delArrayItem($index){
    if($index==""){
      return;
    }
    $curSavedP = unserialize($_SESSION['purchases']);
    $curSavedA = !empty($_SESSION['addit_params']) ? unserialize($_SESSION['addit_params']) : false;
    $outputP = array();
    $outputA = array();
    if(count($curSavedP)>1){
      unset($curSavedP[$index]);
      if($curSavedA!==false){
        unset($curSavedA[$index]);
      }
      $c = 0;
      foreach ($curSavedP as $i => $value){
        $outputP[$c] = $value;
        if($curSavedA!==false){
          $outputA[$c] = $curSavedA[$i];
        }
        $c++;
      }
      $purchasesStr = serialize($outputP);
      $_SESSION['purchases'] = $purchasesStr;
      if($curSavedA!==false){
        $addit_paramsStr = serialize($outputA);
        $_SESSION['addit_params'] = $addit_paramsStr;
      }
    }else{
      $this->emptySavedData();
    }
  }

   /**
    * Очищает корзину
    */
    function emptySavedData(){
        $_SESSION['purchases'] = '';
        $_SESSION['addit_params'] = '';
        unset($_SESSION['purchases'],$_SESSION['addit_params']);
        echo '<script type="text/javascript">if(typeof(jQuery)!=\'undefined\' && typeof(jQuery.refreshCart)!=\'undefined\')$(document).bind(\'ready\',jQuery.refreshCart);</script>';
    }


  /**
   * Вытаскивает данные из таблицы контента (пока только pagetitle) и формирует массив
   *   
   * @param array $array
   * @param boolean $withids
   * @return array
   */
  function getContentData($array, $withids=true){
    $pNames = array();

    //if data stored in array
    if(isset($array[0][3])){

      foreach($array as $value){
        $arrIndex = isset($value['catalog']) && $value['catalog']!=0 ? 1 : 0;
        if($withids)
          $pNames[$arrIndex][$value[0]] = $value[3];
        else
          $pNames[$arrIndex][] = $value[3];
      }

    //if data stored in DB
    }else{
      $idsStr = '';
      $idsStr_c = '';
      foreach($array as $val){
        if(isset($val['catalog']) && $val['catalog']!=0)
          $idsStr_c .= ",".$val[0];
        else
          $idsStr .= ",".$val[0];
      }
      $idsStr = $this->modx->db->escape(trim($idsStr,','));
      $idsStr_c = $this->modx->db->escape(trim($idsStr_c,','));

      //data in MODx content
      if($idsStr){
        $res1 = $this->modx->db->select("id,pagetitle",$this->modx->getFullTableName('site_content'),"id IN ($idsStr)");
        while($row = $this->modx->db->getRow($res1)){
          if($withids)
            $pNames[0][$row['id']] = $row['pagetitle'];
          else
            $pNames[0][] = $row['pagetitle'];
        }
      }
      //data in SHK catalog
      if($idsStr_c){
        $res2 = $this->modx->db->select("id,pagetitle",$this->modx->getFullTableName('catalog'),"id IN ($idsStr_c)");
        while($row = $this->modx->db->getRow($res2)){
          if($withids)
            $pNames[1][$row['id']] = $row['pagetitle'];
          else
            $pNames[1][] = $row['pagetitle'];
        }
      }

    }

    return $pNames;
  }

   /**
    * Формирует массив с TV-параметрами
    *    
    * @param array $array
    * @return array
    */
    function getTmplVars($array){
        $templateVars = array(array(),array());
        $tv_names = array();
        
        //if data stored in array
        if(isset($array[0]['tv'])){
          foreach($array as $val){
            $arrIndex = isset($val['catalog']) && $val['catalog']!=0 ? 1 : 0;
            $templateVars[$arrIndex][$val[0]] = $val['tv'];
          }
        //if data stored in DB
        }else{
          $idsStr = '';
          $idsStr_c = '';
          foreach($array as $val){
            if(isset($val['catalog']) && $val['catalog']!=0)
              $idsStr_c .= ",".$val[0];
            else
              $idsStr .= ",".$val[0];
          }
          $idsStr = $this->modx->db->escape(trim($idsStr,','));
          $idsStr_c = $this->modx->db->escape(trim($idsStr_c,','));
          
          //data in MODx content
          if($idsStr){
            $res1 = $this->modx->db->select(
              "tv.name, tvc.value, tvc.contentid",
              $this->modx->getFullTableName('site_tmplvars')." tv, ".$this->modx->getFullTableName('site_tmplvar_contentvalues')." tvc",
              "tv.id = tvc.tmplvarid AND tvc.contentid IN ($idsStr)"
            );
            //$templateVars[0] = $this->getRowArray($res1);
            while($row = $this->modx->db->getRow($res1)){
              $templateVars[0][$row['contentid']][$row['name']] = $row['value'];
              if(!in_array($row['name'],$tv_names)) $tv_names[] = $row['name'];
            }
            unset($row);
          }
          //data in SHK catalog
          if($idsStr_c){
            $res2 = $this->modx->db->select(
              "tv.name, tvc.value, tvc.contentid",
              $this->modx->getFullTableName('site_tmplvars')." tv, ".$this->modx->getFullTableName('catalog_tmplvar_contentvalues')." tvc",
              "tv.id = tvc.tmplvarid AND tvc.contentid IN ($idsStr_c)"
            );
            //$templateVars[1] = $this->getRowArray($res2);
            while($row = $this->modx->db->getRow($res2)){
              $templateVars[1][$row['contentid']][$row['name']] = $row['value'];
              if(!in_array($row['name'],$tv_names)) $tv_names[] = $row['name'];
            }
            unset($row);
          }
          
          $templateVars[0] = $this->arrayFillHoles($templateVars[0],$tv_names);
          $templateVars[1] = $this->arrayFillHoles($templateVars[1],$tv_names);
          
        }
        
        return $templateVars;
    }
    
   /**
    * Возвращает массив значений из MySQL result
    *    
    * @param resource $q_result
    * @return array
    */
    function getRowArray($q_result){
        $templateVars = array();
        while($row = $this->modx->db->getRow($q_result)){
          if(!isset($templateVars[$row['contentid']]))
            $templateVars[$row['contentid']] = array($row['name']=>$row['value']);
          else
            $templateVars[$row['contentid']] = array_merge($templateVars[$row['contentid']],array($row['name']=>$row['value']));
        }
        return $templateVars;
    }
    
    /**
    * Убирает дыры в массиве
    *    
    * @param array $array
    * @param array $keys  
    * @return array
    */
    function arrayFillHoles($array,$keys){
        foreach($array as $key => &$val){
            $hole = array_diff($keys,array_keys($val));
            foreach($hole as $k => $v) $val[$v] = '';
        }
        return $array;
    }
    
   /**
    * Формирует HTML-код отдельного товара в корзине
    * 
    * @param array $purchases
    * @param array $addit_params
    * @param string $outType
    * @param array $allowed
    * @param string $thisPage
    * @param boolean $del_notavailable
    * @return string
    */
    function getStuffList($purchases,$addit_params,$outType,$allowed=false,$thisPage='',$del_notavailable=false){
        $output = '';
        $url_qs = strpos($thisPage, "?")!==false ? "&amp;" : "?";

        if(count($purchases)>0){

          $pNames = $this->getContentData($purchases);
          $templateVars = $this->getTmplVars($purchases);
          $num = 0;

          if($del_notavailable){
            //Remove products, which can not be ordered
            foreach($purchases as $key => $value){
              if(is_array($allowed) && !in_array($key,$allowed))
                unset($purchases[$key]);
            }
            unset($key,$value);
          }
          
          //get chunk
          $rowChunk = '';
          if($outType=="table"){
              if($this->config['cartRowTpl']){
                $rowChunk = $this->fetchTpl($this->config['cartRowTpl']);
              }
          }else if($outType=="list"){
              if($this->config['orderDataTpl']){
                  $mainChunk = $this->fetchTpl($this->config['orderDataTpl']);
                  $tempChunk = preg_split('/(\[\+loop\+\]|\[\+end_loop\+\])/s', $mainChunk);
                  $rowChunk = $tempChunk[1];
              }
          }
                    
          foreach($purchases as $i => $dataArray){
            list($id, $count, $price) = $dataArray;
            if(!$id){
                $this->emptySavedData();
                return $output;
            }
            $is_catalog = isset($dataArray['catalog']) && $dataArray['catalog']!=0 ? true : false;
            $cIndex = $is_catalog ? 1 : 0;

            $name = isset($pNames[$cIndex][$id]) ? $pNames[$cIndex][$id] : '';
            $tvArr = isset($templateVars[$cIndex][$id]) ? (array) $templateVars[$cIndex][$id] : array();
            if(isset($purchases[$i]['tv_add'])) $tvArr = array_merge($tvArr,$purchases[$i]['tv_add']);

            if($is_catalog){
              $catalogId = isset($dataArray['catalog']) ? $dataArray['catalog'] : 0;
              $link = $this->modx->makeUrl($catalogId, '', '', 'full'); //$this->modx->makeUrl($catalogId);
              $link = strpos($link,'?')===false ? $link.'?p='.$id : $link.'&amp;p='.$id;
            }else{
              $link = $this->modx->makeUrl($id, '', '', 'full');//$this->modx->makeUrl($id);
            }
            $nameStr = $this->config['linkAllow'] ? "<a href=\"$link\">".$name."</a>" : $name;

            list($additStr, $additPrice) = $this->getAddit($addit_params[$i],$i);
            $url_del_item = $thisPage.$url_qs."shk_action=del&amp;n=".$i;

            $available = $allowed===false || (is_array($allowed) && in_array($i,$allowed)) ? true : false;

            if($available) $num++;

            $chunkArr = array(
              'name' => $name,
              'id' => $id,
              'link' => $link,
              'addit_data' => $additStr,
              'price' => $this->numberFormat($price),
              'price_total' => $this->numberFormat($price+$additPrice),
              'price_count' => $price*$count,
              'currency' => $this->config['currency'],
              'count' => $count,
              'this_page_url' => $thisPage,
              'index' => $i,
              'num' => $num,
              'even' => $num%2==0 ? 1 : 0,
              'comma' => (($del_notavailable && $num!=count($purchases)) || (!$del_notavailable && $i!=count($purchases)-1)) && count($purchases)>1 ? ',' : '',
              'url_del_item' => $url_del_item,
              'available' => $available ? 'available' : 'notavailable',
              '<s>' => $available ? '' : '<s>',
              '</s>' => $available ? '' : '</s>'
            );
            
            $chunk = $rowChunk;
            
            if($chunk){
              $this->phx->placeholders = array();
              $this->setPlaceholders(array_merge($tvArr,$chunkArr));
              $chunk = $this->phx->Parse($chunk);
              $output .= $chunk;
              unset($chunk);
            }else{
              $additStr = !empty($additStr) ? ", ".$additStr : '';
              $output .= "<li><b>$nameStr</b>$additStr <b>x $count</b></li>";
            }
            
          }//end foreach
        }
        return $output;
    }


   /**
    * Формирует данные дополнительных параметров товара в корзине
    *    
    * @param array $array
    * @param int $index
    * @return array
    */
    function getAddit($array,$index){
        $additParam = $array;
        $outputArray = array();
        $outputStr = '';
        $outputPrice = 0;
        $template = $this->config['additDataTpl'] ? $this->fetchTpl($this->config['additDataTpl']) : false;
        if(!empty($additParam[0][0])){
          for($i=0;$i<count($additParam);$i++){
            list($name,$price) = $additParam[$i];
            if($this->config['charset']=='windows-1251' && $this->isUTF8($name)){
              $name = iconv("UTF-8", "windows-1251", $name);
            }
            $param = $price!=0 ? "$name ($price)" : $name;
            $outputPrice += $price;
            if($template!==false){
              $chunk = $template;
              $chunkArr = array(
                'param' => $param,
                'name' => $name,
                'price' => $this->numberFormat($price)
              );
              foreach ($chunkArr as $key => $value){
                $chunk = str_replace("[+".$key."+]", $value, $chunk);
              }
              $outputStr .= $chunk;
              unset($chunk);
            }else{
              $outputStr .= "$param, ";
            }
          }
        }
        return !$this->config['additDataTpl'] ? array(trim($outputStr,", "), $outputPrice) : array($outputStr, $outputPrice);
    }

   /**
    * Возвращает число товаров в корзине и общую стоимость
    *    
    * @param array $purchases
    * @param array $addit_params
    * @param array $allowed
    * @return array
    */
    function getTotal($purchases,$addit_params=array(),$allowed=false){
        $totalPrice = 0;
        $totalItems = 0;
        if(!empty($purchases)){
          for($i=0;$i<count($purchases);$i++){
            list($id, $count, $price) = $purchases[$i];
            $count = str_replace(',','.',$count);
            $price = floatval(str_replace(',','.',$price));
            if($allowed===false || (is_array($allowed) && in_array($i,$allowed))){
              if(!empty($addit_params[$i])){
                $totalPrice += $price*$count;
                for($ii=0;$ii<count($addit_params[$i]);$ii++){
                  $totalPrice += floatval(str_replace(',','.',$addit_params[$i][$ii][1]))*$count;
                }
              }else{
                $totalPrice += $price*$count;
              }
              $totalItems += $count;
            }
          }
        }
        
        $evtOut = $this->modx->invokeEvent('OnSHKcalcTotalPrice',array(
            'totalPrice' => $totalPrice,
            'purchases' => $purchases
        ));
        if(!empty($evtOut[0])) $totalPrice = $evtOut[0];

        $output = array($totalItems,$totalPrice);
        return $output;
    }

   /**
    * Обновляет кол-во товара на складе
    *    
    * @param array $purchases
    * @param array $allowed
    * @param string $shkTvInventory
    */
    function updateInventory($purchases,$allowed,$shkTvInventory){
        if(count($purchases)==0 || empty($shkTvInventory)) return;
        //for($i=0;$i<count($purchases);$i++){
        foreach($purchases as $i => $val){
            if(!in_array($i,$allowed)) continue;
            $TVcontentid = $val[0];
            $TVplusValue = $val[1];
            if(!isset($TVinventoryId)){
              $TVinventoryId = $this->modx->db->getValue($this->modx->db->select("id",$this->modx->getFullTableName("site_tmplvars"), "name = '$shkTvInventory'"));
            }
            $tbl_tv_values = !$val['catalog'] ? $this->modx->getFullTableName("site_tmplvar_contentvalues") : $this->modx->getFullTableName("catalog_tmplvar_contentvalues");                        
            $TVcurInventory = $this->modx->db->getValue($this->modx->db->select("value",$tbl_tv_values,"contentid = '$TVcontentid' AND tmplvarid = '$TVinventoryId'"));
            if($TVcurInventory){
              $TVplusValue = $TVplusValue<$TVcurInventory ? $TVplusValue : $TVcurInventory;
              $this->modx->db->update("value = value-$TVplusValue", $tbl_tv_values, "contentid = '$TVcontentid' AND tmplvarid = '$TVinventoryId'");
            }            
        }
    }


   /**
    * Создает окончание для слова "товар" по числу
    *    
    * @param int $n
    * @return string
    */
    function getPlural($n){
        if($this->langTxt['this']=='russian' || $this->langTxt['this']=='russian-UTF8'){
            $plural = $n%10==1 && $n%100!=11 ? $this->langTxt['plural'][0] : ($n%10>=2 && $n%10<=4 && ($n%100<10 || $n%100>=20) ? $this->langTxt['plural'][1] : $this->langTxt['plural'][2]);
        }else{
            $plural = $n!=1 ? $this->langTxt['plural'][0] : $this->langTxt['plural'][1];
        }
        return $plural;
    }


   /**
    * Формирует HTML-код содержимого корзины
    *    
    * @param string $orderFormPage
    * @param string $thisPage
    * @return string
    */
    function getCartContent($orderFormPage,$thisPage){
        $chunk = explode('<!--tpl_separator-->',$this->fetchTpl($this->config['cartTpl']));
        if(!empty($_SESSION['purchases'])){
          $this_page_url = is_int($thisPage) ? $this->modx->makeUrl($thisPage, '', '', 'full') : $thisPage;
          $url_qs = strpos($this_page_url, "?")!==false ? "&amp;" : "?";
          $purchases = unserialize($_SESSION['purchases']);
          $addit_params = !empty($_SESSION['addit_params']) ? unserialize($_SESSION['addit_params']) : array();
          list($totalItems,$totalPrice) = $this->getTotal($purchases,$addit_params);
          
          $evtOut = $this->modx->invokeEvent('OnSHKcartLoad',array('totalItems'=>$totalItems,'totalPrice'=>$totalPrice));
          $plugin = is_array($evtOut) ? implode('', $evtOut) : '';
          
          $cartInner = $this->getStuffList($purchases,$addit_params,'table',false,$this_page_url);
          $cartInner = $this->cleanPHx($cartInner);
          $chunkArr = array(
            'inner' => $cartInner,
            'price_total' => $this->numberFormat($totalPrice),
            'total_items' => $totalItems,
            'plural' => $this->getPlural($totalItems,$this->langTxt),
            'this_page_url' => $this_page_url,
            'empty_url' => $this_page_url.$url_qs.'shk_action=empty',
            'order_page_url' => $orderFormPage,
            'currency' => $this->config['currency'],
            'plugin' => $plugin
          );
          
          $output = $this->config['cartType']=="small" ? $chunk[2] : $chunk[1];
          
          $this->phx->placeholders = array();
          $this->setPlaceholders($chunkArr);
          $output = $this->phx->Parse($output);
        
        }else{
          
          $evtOut = $this->modx->invokeEvent('OnSHKcartLoad');
          $plugin = is_array($evtOut) ? implode('', $evtOut) : '';
          $output = str_replace("[+plugin+]", $plugin, $chunk[0]);
          
        }
        return $output;
    }
    
    /**
    * Проверяет является ли строка сериализованным масивом
    *     
    * @param string $string
    */
    function is_serialized($string){
        if (!is_string($string)) return false;
        return (@unserialize($string) !== false);
    }

   /**
    * Добавляет данные заказа в письмо
    *    
    * @param array $fields
    */
    function populateOrderData(&$fields){

        if(!empty($_SESSION['purchases'])){

          $purchases = unserialize($_SESSION['purchases']);
          $addit_params = !empty($_SESSION['addit_params']) ? unserialize($_SESSION['addit_params']) : array();
          list($totalItems,$totalPrice) = $this->getTotal($purchases,$addit_params);

          if($this->config['orderDataTpl']){

            $chunkArr = array(
              'totalPrice' => $totalPrice,
              'currency' => $this->config['currency'],
            );
            $mainChunk = $this->fetchTpl($this->config['orderDataTpl']);
            $rowChunk = preg_split('/(\[\+loop\+\]|\[\+end_loop\+\])/s', $mainChunk);
            $chunk = $rowChunk[0].$this->getStuffList($purchases,$addit_params,'list').$rowChunk[2];
            foreach ($chunkArr as $key => $value){
              $chunk = str_replace("[+".$key."+]", $value, $chunk);
            }
            $orderData = $chunk;
          }else{
            $orderData = $this->getStuffList($purchases,$addit_params,'list')."<br /><b>".$this->langTxt['sumTotal'].": ".$totalPrice." ".$this->config['currency']."</b>";
          }

          $order_id = $this->getNextAutoIncrement($this->modx->db->config['table_prefix'].'manager_shopkeeper');
          $fields['orderID'] = $order_id;
          $output = $orderData;

        }else{
          $output = "<i>".$this->langTxt['noSelected']."</i>";
        }

        //plugin
        $evtOut = $this->modx->invokeEvent('OnSHKbeforeSendOrder',array("fields"=>$fields));
        if (!empty($evtOut[0]) && !is_array($evtOut[0])) {
            $fields = unserialize($evtOut[0]);
        }

        $fields['orderData'] = $output;

    }


   /**
    * Сохраняет данные заказа в таблицу модуля управления заказами
    *    
    * @param array $fields
    * @return null
    */
    function sendOrderToManager($fields){

        $reportTpl = !empty($_SESSION['reporttpl']) ? $_SESSION['reporttpl'] : $fields['reportTpl'];
        $userLogged =  isset($_SESSION['webValidated']) ? true : false;
        $ord_email = isset($fields['email']) ? $this->modx->db->escape($fields['email']) : '';
        $ord_phone = isset($fields['phone']) ? $this->modx->db->escape($fields['phone']) : '';
        $ord_pay_method = isset($fields['payment']) ? $this->modx->db->escape($fields['payment']) : 0;

        if (mysql_num_rows(mysql_query("show tables from ".$this->modx->db->config['dbase']." like '".$this->modx->db->config['table_prefix']."manager_shopkeeper'"))==0) return;
        
        $config_query = $this->modx->db->select("*", $this->modx->getFullTableName('manager_shopkeeper_config'), "", "", "");
        while($config = mysql_fetch_array($config_query)){
          $$config[1] = $config[2];
        }
        
        unset($fields['orderData'],$fields['submit'],$fields['formid'],$fields['vericode']);
        $short_txt = is_array($fields) ? $this->modx->db->escape(serialize($fields)) : '';

        if(!empty($_SESSION['purchases'])){

          $curSavedP = unserialize($_SESSION['purchases']);
          $curSavedA = !empty($_SESSION['addit_params']) ? unserialize($_SESSION['addit_params']) : false;
          list($totalItems,$totalPrice) = $this->getTotal($curSavedP,$curSavedA);
          $p_names = $this->getContentData($curSavedP);
          $templateVars = $this->getTmplVars($curSavedP);

          for($i=0;$i<count($curSavedP);$i++){
            $curSavedP[$i][3] = $curSavedP[$i]['catalog']==0 ? $p_names[0][$curSavedP[$i][0]] : $p_names[1][$curSavedP[$i][0]];
            $curSavedP[$i]['tv'] = $curSavedP[$i]['catalog']==0 ? $templateVars[0][$curSavedP[$i][0]] : $templateVars[1][$curSavedP[$i][0]];
          }

          if(count(array_filter($curSavedA))==0){
            $curSavedA = false;
          }
          
          $userLoggedIn = $this->modx->userLoggedIn();
          $userId = $userLoggedIn!==false ? $userLoggedIn['id'] : 0;

          //Save order data
          $ins_fields = array(
            'short_txt' => $short_txt,
            'content' => $this->modx->db->escape(serialize($curSavedP)),
            'allowed' => 'all',
            'addit' => $this->modx->db->escape(serialize($curSavedA)),
            'price' => $totalPrice,
            'currency' => $this->config['currency'],
            'date' => date("Y-m-d H:i:s"),
            'note' => '',
            'email' => $ord_email,
            'phone' => $ord_phone,
            'payment' => $ord_pay_method,
            'tracking_num' => '',
            'status' => 1,
            'userid' => $userId
          );
          $order_id = $this->modx->db->insert($ins_fields, $this->modx->getFullTableName('manager_shopkeeper'));
          
          $this->modx->invokeEvent('OnSHKsaveOrder', array('id' => $order_id,'purchases' => $curSavedP));
          $this->modx->invokeEvent('OnSHKChangeStatus',array('order_id'=>$order_id,'status'=>1));

          //Save for online payment
          $_SESSION['shk_order_id'] = $order_id;
          $_SESSION['shk_payment_method'] =  $ord_pay_method;
          $_SESSION['shk_order_price'] = $totalPrice;
          $_SESSION['shk_currency'] = $this->config['currency'];
          $_SESSION['shk_order_user_id'] = $userId;
          $_SESSION['shk_order_user_email'] = $ord_email;

          $this->emptySavedData();
          $this->modx->setPlaceholder('orderID',$order_id);

        }

    }


}


?>