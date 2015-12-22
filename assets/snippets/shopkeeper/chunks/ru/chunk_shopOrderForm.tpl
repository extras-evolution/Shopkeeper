<div class="error">[+validationmessage+]</div><br />

<form method="post" action="[~[*id*]~]" id="shopOrderForm">

<fieldset>

<input type="hidden" name="formid" value="shopOrderForm" />
<input type="hidden" name="reportTpl" value="shopOrderReport" eform="::::" />

<table cellpadding="3">
<tr>
  <td>Адрес:</td>
  <td><input name="address" size="30" class="textfield" type="text" /></td>
</tr>
<tr>
  <td>Способ доставки*:</td>
  <td>
    <select name="delivery">
    <option value="На дом">Доставка на дом (по городу)</option>
    <option value="Почта России">Почта России</option>
<option value="Служба междугородней доставки">Служба междугородней доставки</option>
    </select>
  </td>
</tr>
<tr>
  <td>Способ оплаты*:</td>
  <td>
    <select name="payment">
    <option value="При получении">При получении</option>
    <option value="WebMoney">WebMoney</option>
    </select>
  </td>
</tr>
<tr>
  <td>Ф.И.О.*:</td>
  <td><input name="name" size="30" class="textfield" type="text" eform="Ф.И.О.::1" /></td>
</tr>
<tr>
  <td>E-mail*:</td>
  <td><input name="email" size="30" class="textfield" type="text" eform="E-mail:email:1" /> </td>
</tr>
<tr>
  <td>Телефон*:</td>
  <td><input name="phone" size="30" class="textfield" type="text" eform="Номер телефона::1" /></td>
</tr>
<tr>
  <td>Сообщение:</td>
  <td><textarea name="message" class="textfield" rows="4" cols="30"></textarea></td>
</tr>
<tr>
  <td>Код подтверждения*:</td>
  <td></td>
</tr>
<tr>
  <td><img src="[+verimageurl+]" alt="" /></td>
  <td><input type="text" name="vericode" class="textfield" size="20" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" name="submit" class="button" value="Отправить" /></td>
</tr>
</table>

</fieldset>

</form>