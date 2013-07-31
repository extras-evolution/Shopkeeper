
<div>
  [+up_menu+]
</div>
<br />

<h2>Профиль</h2>

<p>[+message+] [+success_mess+]</p>


<form action="[+action+]" method="post">

<table cellpadding="5">
  <col width="30%" />
  <col width="70%" />
	<tr>
    <td>
      <label>
        <input type="checkbox" name="chpwd" value="1" onclick="document.getElementById('changePassword').style.display = this.checked ? 'block' : 'none';" />
        Изменить пароль
      </label>
    </td>
    <td>
      <div id="changePassword" style="display:none;">
        Старый пароль:
        <br />
        <input type="password" name="oldpassword" value="" />
        <br />
        Новый пароль:
        <br />
        <input type="password" name="password" value="" />
        <br />
        Повторите пароль:
        <br />
        <input type="password" name="repassword" value="" />
      </div>
    </td>
  </tr>
  <tr>
    <td>Полное имя:</td>
    <td><input type="text" name="fullname" value="[+fullname+]" /></td>
  </tr>
  <tr>
  	<td>Адрес e-mail:</td>
  	<td>
    	<input type="text" name="email" value="[+email+]" />
  	</td>
  </tr>
  <tr>
  	<td>Номер телефона:</td>
  	<td><input type="text" name="phone" value="[+phone+]" /></td>
  </tr>
  <tr>
  	<td>Номер мобильного телефона:</td>
  	<td><input type="text" name="mobilephone" value="[+mobilephone+]" /></td>
  </tr>
  <tr>
  	<td>Факс:</td>
  	<td><input type="text" name="fax" value="[+fax+]" /></td>
  </tr>
  <tr>
  	<td>регион/провинция/область/район:</td>
  	<td><input type="text" name="state" value="[+state+]" /></td>
  </tr>
  <tr>
  	<td>Почтовый индекс:</td>
  	<td><input type="text" name="zip" value="[+zip+]" /></td>
  </tr>
  <tr>
  	<td>Страна:</td>
  	<td><input type="text" name="country" value="[+country+]" /></td>
	</tr>
	<tr>
  	<td>Комментарий:</td>
  	<td>
  		<textarea type="text" name="comment" rows="5" cols="30">[+comment+]</textarea>
  	</td>
  </tr>
  
  [+addit_fields+]
  
  <tr>
  	<td><input type="submit" value="Сохранить" /></td>
  	<td></td>
  </tr>
</table>


</form>





