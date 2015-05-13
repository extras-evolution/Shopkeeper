<p class="error">[+validationmessage+]</p><br />

<form method="post" action="[~[*id*]~]" id="shopOrderForm" name="shopOrderForm">
<input type="hidden" name="formid" value="shopOrderForm" />
<input type="hidden" name="reportTpl" value="shopOrderReportWebUser" />

<table cellpadding="3">
<tr>
  <td>Delivery method*:</td>
  <td>
    <select name="delivery">
    <option value="Home">Home delivery (by city)</option>
    <option value="Service delivery distance">Service delivery distance</option>
    </select>
  </td>
</tr>
<tr>
  <td>Payment*:</td>
  <td>
    <select name="payment">
    <option value="Upon receipt">Upon receipt</option>
    </select>
  </td>
</tr>
<tr>
  <td>Message:</td>
  <td><textarea name="message" class="textfield" rows="4" cols="30"></textarea></td>
</tr>
<tr>
  <td>Confirmation*:</td>
  <td></td>
</tr>
<tr>
  <td><img src="[+verimageurl+]" alt="" /></td>
  <td><input type="text" name="vericode" class="textfield" size="20" /></td>
</tr>
<tr>
  <td></td>
  <td><input type="submit" name="submit" class="button" value="Submit" /></td>
</tr>
</table>

</form>