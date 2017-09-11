<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
  </html>
  <body style="margin:20px;">

<p>
  Продавец: <b>Интернет-магазин &laquo;Всё для всех&raquo;</b> (ИП Иванов И.И.)
  <br>
  Телефон: 8-XXX-XXX-XX-XX
</p>


<h3>[+today+] г. Товарный чек № [+orderID+] </h3>

<p><b>Покупатель:</b> [+contact+]</p>

<table width="100%" border="1" cellpadding="5" bordercolor="#000" style="border-collapse:collapse;">
  <col bordercolor="#000" span="6">
  <tr>
    <th>№</th>
    <th>Наименование</th>
    <th>Ед. изм</th>
    <th>Кол-во</th>
    <th>Цена</th>
    <th>Сумма</th>
  </tr>
  
  [+loop+]
  <tr>
    <td>[+num+]</td>
    <td>[+name+] [+addit_data+]</td>
    <td>шт.</td>
    <td>[+count+]</td>
    <td>[+price+] [+currency+]</td>
    <td>[+price_count+] [+currency+]</td>
  </tr>
  [+end_loop+]
  
  <tr>
    <td colspan="3">Итого:</td>
    <td>[+total_count+]</td>
    <td></td>
    <td>[+totalPrice+] [+currency+]</td>
  </tr>
</table>

<p><b>Сумма прописью:</b> [+totalPrice_propis+]. Без НДС.</p>

<div style="float:left;">Принял (Ф.И.О.)_______________</div>

<div style="float:right;">Выдал (Ф.И.О.)________________</div>



  </body>
</html>