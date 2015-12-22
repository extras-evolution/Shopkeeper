<?php

//тип контента (documents|products)
$cf_config['content_type'] = 'documents';

//разбивка по столбцам при импорте и экспорте (content|tv|category)
$cf_config['content_row'] = array(
    0 => array('Наименование',array('pagetitle','content')),
    1 => array('Краткое описание',array('introtext','content')),
    2 => array('Подробное описание',array('content','content')),
    3 => array('Цена',array(1,'tv')),
    4 => array('Кол-во на складе',array(7,'tv')),
    5 => array('Картинка',array(4,'tv')),
    6 => array('Цвет',array(2,'tv'))
);

//значения по умолчанию при импорте и проверка на соответствие при экспорте
$cf_config['imp_content_default'] = array(
    'content' => array(
      'published' => 1,
      'template' => 13,
      'createdon' => strtotime("now")
      //'publishedon' => strtotime("now")
      //'pub_date' => strtotime("now")
      //'editedby' => 1
      //'editedon' => strtotime("now")
    ),
    'tv' => array(
      //7 => 0
    )
);

//первая строка - названия полей
$cf_config['include_captions'] = true;

//разбивать по категориям
$cf_config['include_categories'] = false;

//удалять дочерние категории при очистке и обновлении каталога
$cf_config['delete_subcategories'] = true;

//по какому полю проверять соответствие товара при обновлении. false - не проверять (очистка категории при обновлении).
$cf_config['imp_chk_field'] = 'pagetitle';

//проверять соответствие товара при обновлении по значению TV. Указать ID TV. false - не проверять (очистка категории при обновлении).
$cf_config['imp_chk_tvid_val'] = false;

//удалять HTML-теги при экспорте
$cf_config['exp_strip_tags'] = false;

//автоматически генерировать псевдоним (alias) при импорте
//false - выключено; true - генерировать с переводом в транслит; 'notranslit' - генерировать без перевода в транслит.
$cf_config['imp_autoalias'] = true;

//удалить файл после экспорта (скачивания)
$cf_config['exp_delete_file'] = false;

//кодировка CSV-файла при экспорте
$cf_config['exp_csv_charset'] = 'UTF-8'; //'windows-1251'

//тестирование конфигурации (без записи в БД)
$cf_config['imp_testmode'] = false;


//функция для фильтрации значений при ИМПОРТЕ
function filter_import($value_arr){
    $output_arr = $value_arr;
    /*
    if(isset($output_arr['content']['pagetitle']))
        $output_arr['content']['pagetitle'] = mb_strtoupper($output_arr['content']['pagetitle'], 'UTF-8');
    */
    return $output_arr;
}


//функция для фильтрации значений при ЭКСПОРТЕ
function filter_export($value_arr,$doc_id=0){
    $output_arr = $value_arr;
    //var_dump($value_arr,$output_arr);
    //exit;
    /*
    if(isset($output_arr['price']))
        $output_arr[1] = floatval($output_arr[1]) - 200;
    */
    return $output_arr;
}


?>