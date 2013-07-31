
<div>
  [+up_menu+]
</div>
<br />


<h2>[+title+]</h2>

<p>Дата и время: [+date+]</p>

<p>Номер заказа: [+id+]</p>

<p>Статус: <span style="background-color:[+phaseColor+];">[+status+]</span></p>

<hr />

<ul>

[+loop+]
  <li>[+<s>+]<b><a href="[+link+]" target="_blank">[+name+]</a></b> ([+price+])[+addit_data+] <b> x [+count+] шт.</b>[+</s>+]</li>
[+end_loop+]

</ul>

<p>Общая стоимость: <b>[+totalPrice+]</b> [+currency+]</p>

<hr />

<p>
  <b>Контактная информация</b>:
  <br />
  [+contact+]
</p>

[+phx:if=`[+cancel_allowed+]`:is=`true`:then=`

<div align="right">
	<form action="[+backLink+]&amp;pid=[+id+]" method="post" onsubmit="if(confirm('Вы уверены, что хотите отменить заказ?')) return true; else return false;">
	  <input type="hidden" name="pid" value="[+id+]" />
	  <input type="submit" name="shk_del" value="X &nbsp; Отменить заказ" />
	</form>
</div>
<br />

`+]

[+phx:if=`[+refresh_allowed+]`:is=`true`:then=`

<div align="right">
	<form action="[+backLink+]&amp;pid=[+id+]" method="post">
	  <input type="hidden" name="pid" value="[+id+]" />
	  <input type="submit" name="shk_refresh" value="Повторить заказ" />
	</form>
</div>
<br />

`+]

[+payment+]

<br />



<a href="[+backLink+]">Назад</a>


<br /><br />
