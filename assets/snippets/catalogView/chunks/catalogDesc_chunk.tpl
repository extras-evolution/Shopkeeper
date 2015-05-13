<h2>[+pagetitle+]</h2>

[+content+]

<br clear="all" />

<p>Цена: <b>[+price:num_format+]</b> руб.</p>

<div class="shk-item">
    <form action="[+thisPageUrl+]" method="post">
        <fieldset>
        <input type="hidden" name="shk-catalog" value="1" />
        <input type="hidden" name="shk-id" value="[+id+]" />
        <input type="hidden" name="shk-name" value="[+pagetitle+]" />
        <input type="hidden" name="shk-count" value="1" size="2" maxlength="3" />
        <button type="submit" class="shk-but">В корзину</button>
        </fieldset>
    </form>
</div>