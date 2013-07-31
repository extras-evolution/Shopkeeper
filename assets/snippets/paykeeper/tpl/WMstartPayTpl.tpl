
<br />

<p>Вы можете оплатить заказ электронными деньгами.<p>

<form method="post" action="[+action+]">

  <input type="hidden" name="action" value="payment" />
  
  <script type="text/javascript">
  <!--
  document.write("<button onclick=\"this.style.display='none';getElementById('paymentFields').style.display='block';return false\">[+payment_button+]</button>");
  //-->
  </script>
  
  <div id="paymentFields" style="display:none">
    Ваш e-mail: <input type="text" name="email" value="[+email+]" size="20" onkeypress="this.form.submit.disabled=0" />
    <input type="submit" name="submit"[+disabled+] value="Перейти к оплате" />
  </div>

</form>

<noscript>
  <form method="post" action="[+action+]">
    <input type="hidden" name="action" value="payment" />
    Ваш e-mail: <input type="text" name="email" value="[+email+]" size="20" />
    <input type="submit" name="submit" value="Перейти к оплате" />
  </form>
</noscript>

