<?php

/*

Проверка добавлен ли товар к сравнению

*/

if(!isset($options)) $options = 'checked';

$compareIds_arr = !empty($_COOKIE['shkCompareIds']) ? explode(',',str_replace(' ','',$_COOKIE['shkCompareIds'])) : array();

return in_array($output,$compareIds_arr) ? $options : '';

?>