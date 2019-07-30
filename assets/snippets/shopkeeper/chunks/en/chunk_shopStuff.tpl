<div class="shop-stuff shk-item">
  <div class="shop-stuff-b">
    <h3>[+title+]</h3>
    <div class="shs-descr">
      <a href="[~[+id+]~]"><img class="shk-image" src="[+image+]" alt="" height="130" width="130" /></a>
      [+introtext+]<br />
      <a href="[~[+id+]~]">More &rsaquo;</a>
      <br clear="all" />
      <small>Stock level: [+inventory+]</small>
    </div>
    <form action="[~[*id*]~]" method="post">
      <input type="hidden" name="shk-id" value="[+id+]" />
      <input type="hidden" name="shk-name" value="[+pagetitle+]" />
      <input type="hidden" name="shk-count" value="1" size="2" maxlength="3" />
      <div class="shs-price">
        <button type="submit" name="shk-submit" class="shk-but">Add to cart</button>
        <div>Price: <span class="shk-price">[+price+]</span> USD</div>
      </div>
    </form>
  </div>
</div>