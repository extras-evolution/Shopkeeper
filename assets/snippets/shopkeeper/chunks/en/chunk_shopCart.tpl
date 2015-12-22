<div id="shopCart" class="shop-cart">
  <div class="shop-cart-head"><b>Shopping cart</b></div>
  <div id="cartInner" class="empty">
    <div id="cartEmpty" align="center">Empty</div>
  </div>
</div>
<!--tpl_separator-->
<div id="shopCart" class="shop-cart">
  <div class="shop-cart-head"><b>Shopping cart</b></div>
  <div id="cartInner" class="full">
    <div align="right">
      <a href="[+this_page_url+]?shk_action=empty" id="butEmptyCart">Empty cart</a>
    </div>
    <table width="100%">
      <tbody>
        [+inner+]
      </tbody>
    </table>
    <div align="right">Total: <b>[+price_total+]</b> [+currency+]</div>
    <noscript>
      <input type="submit" name="shk_recount" value="Calculate" />
    </noscript>
    <div class="cart-order">
      <a href="[+order_page_url+]" id="butOrder">Checkout</a>
    </div>
  </div>
</div>
<!--tpl_separator-->
<div id="shopCart" class="shop-cart">
  <div class="shop-cart-head"><b>Shopping cart</b></div>
  <div id="cartInner" class="full">
    <div align="right">
      <a href="[+this_page_url+]?shk_action=empty" id="butEmptyCart">Empty cart</a>
    </div>
    <div class="shop-cart-body">You have chosen: <b>[+total_items+]</b> [+plural+]</div>
    <div align="right">Total: <b>[+price_total+]</b> [+currency+]
    </div>
    <div class="cart-order">
      <a href="[+order_page_url+]" id="butOrder">Checkout</a>
    </div>
  </div>
</div>