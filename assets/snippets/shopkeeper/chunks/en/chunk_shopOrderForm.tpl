<p class="error">[+validationmessage+]</p><br />

<form method="post" action="[~[*id*]~]" id="shopOrderForm" name="shopOrderForm">
<input type="hidden" name="formid" value="shopOrderForm" />
<input type="hidden" name="reportTpl" value="shopOrderReport" />

<table cellpadding="3">
<tr>
  <td>Address:</td>
  <td><input name="address" size="30" class="textfield" type="text" /></td>
</tr><tr>
  <td>Delivery method: *</td>
  <td>
    <select name="delivery">
    <option value="Home">Home delivery (by city)</option>
    <option value="Mail Russia">Mail Russia</option>
    <option value="Service delivery distance">Service delivery distance</option>
    </select>
  </td>
</tr><tr>
  <td>Payment: *</td>
  <td>
    <select name="payment">
    <option value="Upon receipt">Upon receipt</option>
    <option value="WebMoney">WebMoney</option>
    </select>
  </td>
</tr><tr>
  <td>Name: *</td>
  <td><input name="name" size="30" class="textfield" type="text" eform="Name::1" /></td>
</tr><tr>
  <td>E-mail: *</td>
  <td><input name="email" size="30" class="textfield" type="text" eform="E-mail:email:1" /> </td>
</tr><tr>
  <td>Phone: *</td>
  <td><input name="phone" size="30" class="textfield" type="text" eform="Phone::1" /></td>
</tr><tr>
  <td>Message:</td>
  <td><textarea name="message" class="textfield" rows="4" cols="20"></textarea></td>
</tr><tr>
  <td>Confirmation: *</td>
  <td></td>
</tr><tr>
  <td><img src="[+verimageurl+]" alt="" /></td>
  <td><input type="text" name="vericode" class="textfield" size="20" /></td>
</tr><tr>
  <td></td>
  <td><input type="submit" name="submit" class="button" value="Submit" /></td>
</tr></table>

</form>