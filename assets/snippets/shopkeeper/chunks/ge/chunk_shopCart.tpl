<div id="shopCart" class="shop-cart">
  <div class="shop-cart-head"><b>Warenkorb</b></div>
  <div id="cartInner" class="empty">
    <div id="cartEmpty" align="center">Leer</div>
  </div>
</div>
<!--tpl_separator-->
<div id="shopCart" class="shop-cart">
  <div class="shop-cart-head"><b>Warenkorb</b></div>
  <div id="cartInner" class="full">
    <div align="right">
      <a href="[+this_page_url+]?shk_action=empty" id="butEmptyCart">Warenkorb leeren</a>
    </div>
    <table width="100%">
      <tbody>
        [+inner+]
      </tbody>
    </table>
    <div align="right">Gesamtsumme: <b>[+price_total+]</b> [+currency+]</div>
    <noscript>
      <input type="submit" name="shk_recount" value="Berechnen" />
    </noscript>
    <div class="cart-order">
      <a href="[+order_page_url+]" id="butOrder">Bestellung abschicken</a>
    </div>
  </div>
</div>
<!--tpl_separator-->
<div id="shopCart" class="shop-cart">
  <div class="shop-cart-head"><b>Warenkorb</b></div>
  <div id="cartInner" class="full">
    <div align="right">
      <a href="[+this_page_url+]?shk_action=empty" id="butEmptyCart">Warenkorb leeren</a>
    </div>
    <div class="shop-cart-body">Sie haben ausgewдhlt: <b>[+total_items+]</b> [+plural+]</div>
    <div align="right">Gesamtsumme: <b>[+price_total+]</b> [+currency+]
    </div>
    <div class="cart-order">
      <a href="[+order_page_url+]" id="butOrder">Bestellung abschicken</a>
    </div>
  </div>
</div>