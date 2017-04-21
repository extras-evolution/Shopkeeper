<p class="error">[+validationmessage+]</p><br />

<form method="post" action="[~[*id*]~]" id="shopOrderForm" name="shopOrderForm">
<input type="hidden" name="formid" value="shopOrderForm" />
<input type="hidden" name="reportTpl" value="shopOrderReportWebUser" />

<table cellpadding="3">
<tr>
  <td>Lieferweg*:</td>
  <td>
    <select name="delivery">
    <option value="Post">Post</option>
    <option value="UPS">UPS</option>
    </select>
  </td>
</tr>
<tr>
  <td>Zahlmethode*:</td>
  <td>
    <select name="payment">
    <option value="Auf Rechnung">Auf Rechnung</option>
    </select>
  </td>
</tr>
<tr>
  <td>Nachricht:</td>
  <td><textarea name="message" class="textfield" rows="4" cols="30"></textarea></td>
</tr>
<tr>
  <td>Sicherheitscode*:</td>
  <td></td>
</tr>
<tr>
  <td><img src="[+verimageurl+]" alt="" /></td>
  <td><input type="text" name="vericode" class="textfield" size="20" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" name="submit" class="button" value="senden" /></td>
</tr>
</table>

</form>