<p class="error">[+validationmessage+]</p><br />

<form method="post" action="[~[*id*]~]" id="shopOrderForm">

<fieldset>

<input type="hidden" name="formid" value="shopOrderForm" />
<input type="hidden" name="reportTpl" value="shopOrderReportWebUser" eform="::::" />

<input type="hidden" name="address" value="[*phx:input=`&_PHX_INTERNAL_&`:userinfo=`zip`*] [*phx:input=`&_PHX_INTERNAL_&`:userinfo=`state`*]" eform="::::" />
<input type="hidden" name="name" value="[*phx:input=`&_PHX_INTERNAL_&`:userinfo=`fullname`*]" eform="::::" />
<input type="hidden" name="email" value="[*phx:input=`&_PHX_INTERNAL_&`:userinfo=`email`*]" eform="::::" />
<input type="hidden" name="phone" value="[*phx:input=`&_PHX_INTERNAL_&`:userinfo=`phone`*] [*phx:input=`&_PHX_INTERNAL_&`:userinfo=`mobilephone`*]" eform="::::" />

<table cellpadding="3">
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
  <td>Комментарий:</td>
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