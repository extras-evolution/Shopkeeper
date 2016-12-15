<?php

/*
    * description: format a numeric value
    * usage: [+variable:num_format=`decimals|dec_point|thousands_sep`+]
    * 
*/
list($decimals,$dec_point,$thousands_sep) = explode("|",$options,3);
if (!$decimals) $decimals = 2;
if (!$dec_point) $dec_point = ".";

return number_format((float)$output,$decimals,$dec_point,$thousands_sep);

?>