<!-- #declare:separator <hr> --> 
<!-- login form section-->
<form name="loginfrm" method="post" action="[+action+]" style="margin: 0px; padding: 0px;"> 
<fieldset>
<input type="hidden" value="[+rememberme+]" name="rememberme" /> 
<table border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td><b>Логин:</b></td>
    <td><input type="text" name="username" tabindex="1" onkeypress="return webLoginEnter(document.loginfrm.password);" size="8" style="width: 150px;" value="[+username+]" /></td>
  </tr>
  <tr>
    <td><b>Пароль:</b></td>
    <td><input type="password" name="password" tabindex="2" onkeypress="return webLoginEnter(document.loginfrm.cmdweblogin);" style="width: 150px;" value="" /></td>
  </tr>
  <tr>
    <td><label for="chkbox" style="cursor:pointer">Запомнить меня</label></td>
    <td>
    <table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td valign="top"><input type="checkbox" id="chkbox" name="chkbox" tabindex="4" size="1" value="" [+checkbox+] onclick="webLoginCheckRemember()" /></td>
        <td align="right">                                    
        <input type="submit" value="[+logintext+]" name="cmdweblogin" /></td>
      </tr>
    </table>
    </td>
  </tr>
  <tr>
    <td colspan="2"><a href="#" onclick="webLoginShowForm(2);return false;">Забыли пароль?</a></td>
  </tr>
  <tr>
    <td colspan="2"><a href="[~20~]">Регистрация</a></td>
  </tr>
</table>
</fieldset>
</form>
<hr>
<!-- log out hyperlink section -->
<a href='[+action+]'>[+logouttext+]</a>
<hr>
<!-- Password reminder form section -->
<form name="loginreminder" method="post" action="[+action+]" style="margin: 0px; padding: 0px;">
<fieldset>
<input type="hidden" name="txtpwdrem" value="0" />
<table border="0" cellpadding="3">
    <tr>
      <td>Введите свой электронный адрес, указанный при регистрации и через несколько минут Вы получите письмо с паролем.</td>
    </tr>
    <tr>
      <td><input type="text" name="txtwebemail" size="24" /></td>
    </tr>
    <tr>
      <td align="right">
        <input type="submit" value="Отправить" name="cmdweblogin" />
        <input type="reset" value="Отмена" name="cmdcancel" onclick="webLoginShowForm(1);" />
      </td>
    </tr>
  </table>
</fieldset>
</form>

