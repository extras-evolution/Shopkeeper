

<div>
  [+up_menu+]
</div>
<br />


<h2>[+title+]</h2>


[+phx:if=`[+notempty+]`:is=`true`:then=`

  <table class="data-table" cellpadding="3">
    <tr>
    	<th>ID</th>
    	<th>Дата, время</th>
      <th>Состав заказа</th>
    	<th>Общая стоимость</th>
    	<th>Статус</th>
    	<th></th>
    </tr>
  [+loop+]
    <tr>
      <td>[+id+]</td>
      <td>[+date+]</td>
      <td>[+purchases_list+]</td>
      <td>[+price+] [+currency+]</td>
      <td style="background-color:[+phaseColor+];">[+phaseName+] [+tracking_num+]</td>
      <td><a href="[+link+]">Смотреть</a></td>
    </tr>
  [+end_loop+]
  </table>

[+pages+]

`:else=`

  <br />
  <p>Нет заказов.</p>

`+]



