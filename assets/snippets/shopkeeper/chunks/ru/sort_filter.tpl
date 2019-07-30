
<div[+phx:if=`[+phx:get=`p`+]`:ifnotempty=` style="display:none"`+]>
    Сортировать:
    <a href="[~[*id*]~]?page=[+currentPage+]&amp;sortby=pagetitle&amp;sortdir=[+sortDirOther+]&amp;filter=[+filter+]">
      [+phx:if=`[+sortBy+]`:is=`pagetitle`:then=`
          <b>по названию [+phx:if=`[+sortDir+]`:is=`desc`:then=`&or;`:else=`&and;`+]</b>
      `:else=`
          по названию
      `+]
    </a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href="[~[*id*]~]?page=[+currentPage+]&amp;sortby=price&amp;sortdir=[+sortDirOther+]&amp;sorttype=integer&amp;filter=[+filter+]">
      [+phx:if=`[+sortBy+]`:is=`price`:then=`
          <b>по цене [+phx:if=`[+sortDir+]`:is=`desc`:then=`&or;`:else=`&and;`+]</b>
      `:else=`
          по цене
      `+]
    </a>
    
    <br />
    
    Показать:
    <a href="[~[*id*]~]?page=1&amp;sortby=[+sortBy+]&amp;sortdir=[+sortDir+]">
      [+phx:if=`[+filter+]`:is=``:then=`
          <b>все</b>
      `:else=`
          все
      `+]
    </a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href="[~[*id*]~]?page=1&amp;sortby=[+sortBy+]&amp;sortdir=[+sortDir+]&amp;filter=inventory,0,2">
      [+phx:if=`[+filter+]`:is=`inventory,0,2`:then=`
          <b>в наличии</b>
      `:else=`
          в наличии
      `+]
    </a>
    &nbsp;&nbsp;|&nbsp;&nbsp;
    <a href="[~[*id*]~]?page=1&amp;sortby=[+sortBy+]&amp;sortdir=[+sortDir+]&amp;filter=inventory,0,4">
      [+phx:if=`[+filter+]`:is=`inventory,0,4`:then=`
          <b>под заказ</b>
      `:else=`
          под заказ
      `+]
    </a>
    
</div>

