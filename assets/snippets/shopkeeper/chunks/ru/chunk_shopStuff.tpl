<div class="shop-stuff shk-item">
  <div class="shop-stuff-b">
    <h3>[+pagetitle+]</h3>
    <div class="shs-descr">
      <a href="[+url+]"><img class="shk-image" src="[+image+]" alt="" height="130" width="130" /></a>
      [+introtext+]
      <br /><br />
      <a href="[+url+]">Подробнее &rsaquo;</a>
      <div style="clear:both;"></div>
      <small>На складе: [+inventory+]</small>
    </div>
    <form action="[~[*id*]~]" method="post">
      <fieldset>
        <input type="hidden" name="shk-id" value="[+id+]" />
        <input type="hidden" name="shk-name" value="[+pagetitle+]" />
        <input type="hidden" name="shk-count" value="1" size="2" maxlength="3" />
        <div class="shs-price">
          <button type="submit" class="shk-but">В корзину</button>
          <div>Цена: <span class="shk-price">[+price+]</span> руб.</div>
        </div>
      </fieldset>
    </form>
  </div>
</div>