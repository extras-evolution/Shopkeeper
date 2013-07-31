<div class="shop-stuff shk-item">
  <div class="shop-stuff-b">
    <form action="[+thisPageUrl+]" method="post">
      <fieldset>
        <h3>[+pagetitle+]</h3>
        <div class="shs-descr">
          <a href="[+url+]"><img class="shk-image" src="[+image+]" alt="" height="130" width="130" /></a>
          [+introtext+]
          <br /><br />
          <a href="[+url+]">Подробнее &rsaquo;</a>
          <br /><br />
          [+param1:shk_widget=`select:param1`+]
          <div style="clear:both;"></div>
          Наличие: [+inventory:eq=`0`:then=`под заказ`:else=`в наличии`+]
        </div>
        <input type="hidden" name="shk-catalog" value="1" />
        <input type="hidden" name="shk-id" value="[+id+]" />
        <input type="hidden" name="shk-name" value="[+pagetitle+]" />
        <input type="hidden" name="shk-count" value="1" size="2" maxlength="3" />
        <div class="shs-price">
          <button type="submit" class="shk-but">В корзину</button>
          <div>Цена: <span class="shk-price">[+price:num_format+]</span> руб.</div>
        </div>
      </fieldset>
    </form>
  </div>
</div>