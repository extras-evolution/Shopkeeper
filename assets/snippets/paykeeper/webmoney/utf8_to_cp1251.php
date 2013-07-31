<?php

//For IE

header("Content-Type: text/html; charset=windows-1251"); 

$output = "<html>\n<body onload=\"document.forms[0].submit();\">\n"
         ."<form method=\"post\" action=\"https://merchant.webmoney.ru/lmi/payment.asp\">\n";

foreach($_POST as $k => $v){
  $value = $k != "LMI_PAYMENT_DESC" ? $v : htmlspecialchars(stripslashes(iconv('UTF-8','cp1251',$v)));
  $output .= $k != "submit" ? "<input type=\"hidden\" name=\"$k\" value=\"$value\">\n" : "";
}

$output .= "</form>\n</body>\n</html>";

echo $output;

?>