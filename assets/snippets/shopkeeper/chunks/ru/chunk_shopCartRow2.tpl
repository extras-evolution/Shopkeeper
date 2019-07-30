[+phx:if=`[+name+]`:is=`Доставка`:then=`

<tr>
  <td></td>
  <td>[+name+]: [+price_total+] [+currency+]</td>
  <td></td>
  <td width="10%" align="right">
    <input class="shk-count" type="hidden" name="count[]" value="1" />
    <a href="[+url_del_item+]" title="Удалить" class="shk-del"><img src="assets/snippets/shopkeeper/style/default/img/delete.gif" border="0" width="17" height="17" alt="Удалить" /></a>
  </td>
</tr>

`:else=`

<tr class="cart-order">
  <td width="30%"><img src="[+image+]" width="50" alt="[+name+]" /></td>
  <td width="30%" style="text-align:left;"><b><a href="[+link+]">[+name+]</a></b><br /><small>[+addit_data+]</small><br /> [+price_total+] [+currency+]</td>
  <td width="30%">x <input class="shk-count" type="text" size="2" name="count[]" maxlength="3" title="изменить количество" value="[+count+]" /></td>
  <td width="10%" align="right">
    <a href="[+url_del_item+]" title="Удалить" class="shk-del"><img src="assets/snippets/shopkeeper/style/default/img/delete.gif" border="0" width="17" height="17" alt="Удалить" /></a>
  </td>
</tr>

`+]
