<div class="shop-stuff shk-item">
  <div class="shop-stuff-b">
    <h3>[+title+]</h3>
    <div class="shs-descr">
      <img class="shk-image" src="[+image+]" alt="" height="130" width="130" />
      [+introtext+]<br />
      <a href="[~[+id+]~]">Weiter &rsaquo;</a>
    </div>
    <form action="[~[*id*]~]" method="post">
      <input type="hidden" name="shk-id" value="[+id+]" />
      <input type="hidden" name="shk-count" value="1" size="2" maxlength="3" />
      <div class="shs-price">
        <button type="submit" name="shk-submit" class="shk-but">In den Warenkorb</button>
        <div>Preis: <span class="shk-price">[+price+]</span> EUR</div>
      </div>
    </form>
  </div>
</div>