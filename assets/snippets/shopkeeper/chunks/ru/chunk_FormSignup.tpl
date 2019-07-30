<!-- #declare:separator <hr> --> 
<!-- login form section-->
<form method="post" name="websignupfrm" action="[+action+]">

<table cellpadding="5">
  <tr>
    <td>Логин:*</td>
    <td><input type="text" name="username" id="username" size="30" maxlength="30" value="[+username+]" /></td>
  </tr>
  <tr>
    <td>E-mail:*</td>
    <td><input type="text" name="email" id="email" size="30" value="[+email+]" /></td>
  </tr>
  <tr>
    <td>Пароль:*</td>
    <td><input type="password" name="password" id="password" size="30" /></td>
  </tr>
  <tr>
    <td>Проверка пароля:*</td>
    <td><input type="password" name="confirmpassword" id="confirmpassword" size="30" /></td>
  </tr>
  <tr>
    <td>Ф.И.О.:</td>
    <td><input type="text" name="fullname" id="fullname" size="30" maxlength="100" value="[+fullname+]" /></td>
  </tr>
  <tr>
    <td>Адрес:</td>
    <td><textarea name="state" cols="35" rows="4">[+state+]</textarea></td>
  </tr>
  <tr>
    <td>Почтовый индекс:</td>
    <td><input type="text" name="zip" maxlength="50" size="30" value="[+zip+]" /></td>
  </tr>
  <tr>
    <td>Код подтверждения:* </td>
    <td>
      <a href="[+action+]" title="Обновить код"><img align="top" src="[(site_manager_url)]includes/veriword.php" width="148" height="60" alt="" style="border: 1px solid #039" /></a>
      <br /><br style="line-height:0.5" />
      <input type="text" name="formcode" size="20" />
    </td>
  </tr>
  <tr>
    <td colspan="2"><input type="submit" value="Отправить" name="cmdwebsignup" /></td>
  </tr>
</table>

<br />
<p>* - поля обязательные для заполнения.</p>

</form>

<hr>
<!-- notification section -->
<p class="message">Спасибо за регистрацию в нашем интернет-магазине!<br />
Теперь вы можете авторизоваться.</p>
