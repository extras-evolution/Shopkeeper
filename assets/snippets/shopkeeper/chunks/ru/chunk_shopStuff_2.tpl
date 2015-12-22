<div class="shop-stuff shk-item">
  <div class="shop-stuff-b">
    <h3>[+title+]</h3>
    <div class="shs-descr">
      <img class="shk-image" src="[+image+]" alt="" height="130" width="130" />
      
<div>
  Продажа:<br /><span class="shk-price">[+price+]</span> руб.
</div>

<br />

<div>
  Прокат:<br /><span class="shk-price">[+price_rent+]</span> руб.
</div>

      <div style="clear:both;"></div>
    </div>
    <div style="text-align:right;">
      
      <form action="[~[*id*]~]" method="post" style="display:inline;">
        <fieldset style="display:inline;">
          <input type="hidden" name="shk-id" value="[+id+]" />
          <input type="hidden" name="shk-price" value="[+price+]" />
          <input type="hidden" name="shk-count" value="1" size="2" maxlength="3" />
          <button type="submit" class="shk-but">Куплю</button>
        </fieldset>
      </form>
      
      <form action="[~[*id*]~]" method="post" style="display:inline;">
        <fieldset style="display:inline;">
          <input type="hidden" name="shk-id" value="[+id+]__price_rent" />
          <input type="hidden" name="shk-price" value="[+price_rent+]" />
          <input type="hidden" name="shk-count" value="1" size="2" maxlength="3" />
          <input type="hidden" name="rent__[+id+]__add" value="напрокат" />
          <button style="width:110px;" type="submit" class="shk-but button-rent">Напрокат</button>
        </fieldset>
      </form>
    
    </div>

  </div>
</div>