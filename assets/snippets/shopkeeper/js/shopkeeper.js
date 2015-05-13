
/**************************
* 
* http://modx-shopkeeper.ru/
* Shopkeeper 1.3.5 - shopping cart for MODx Evolution
* 
**************************/

if(typeof(site_url)=='undefined'){
    var site_url = jQuery('base').size()>0
    ? jQuery('base:first').attr('href')
    : window.location.protocol+'//'+window.location.host+'/';
}

var shk_timer;

(function($){

//default settings:
var shkOpt = $.extend({
    stuffCont: 'div.shk-item',
    lang: '',
    cartType: 'full',
    style:'default',
    cartTpl: ['@FILE:assets/snippets/shopkeeper/chunks/ru/chunk_shopCart.tpl','',''],
    flyToCart: 'helper',
    currency: '',
    orderFormPage: '',
    priceTV: 'price',
    noCounter: false,
    changePrice: false,
    counterField: false,
    linkAllow: true,
    noLoader: false,
    debug: false,
    shkHelper: '<div id="stuffHelper"><div><b id="stuffHelperName"></b></div>'
    +"\n"+'<div class="shs-count" id="stuffCount">'+langTxt['count']+' <input type="text" size="2" name="count" value="1" maxlength="3" />'
    +'</div><div><button class="shk-but" id="confirmButton">'+langTxt['continue']+'</button> '
    +"\n"+'<button class="shk-but" id="cancelButton">'+langTxt['cancel']+'</button></div></div>'
    +"\n"
}, shkOptions);

function number_format(number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
	prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
	sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
	dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
	s = '',
	toFixedFix = function (n, prec) {
	    var k = Math.pow(10, prec);
	    return '' + Math.round(n * k) / k;
	};
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
	s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
	s[1] = s[1] || '';
	s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function shk_numFormat(n){
    return number_format(n, (Math.floor(n)===n ? 0 : 2), '.', ' ');
}

var settings_qs = '&site_url='+site_url+'&cart_type='+shkOpt.cartType+'&cart_tpl='+shkOpt.cartTpl[0]+'&cart_row_tpl='+shkOpt.cartTpl[1]+'&addit_data_tpl='+shkOpt.cartTpl[2]+'&currency='+shkOpt.currency+'&price_tv='+shkOpt.priceTV+'&link_allow='+shkOpt.linkAllow+'&nocounter='+shkOpt.noCounter+'&change_price='+shkOpt.changePrice+'&order_page='+shkOpt.orderFormPage;

$.fn.setCounterToField = function(opt){
  var _t = $(this);
  var st = $.extend({style:'default',wrap:null,wrapdiv:false}, opt);
  st.wrap == null && st.wrapdiv && (st.wrap = "<div/>");
  st.wrap && (/^\<.*\>$/.test(st.wrap) || (st.wrap = "<"+st.wrap.replace(/(\<|\/?\>)/g,'')+"/>"));
  !st.wrap && !_t.parent("label").size() && (st.wrap = true);
  st.wrap === true  && ( st.wrap = "<label/>");
  function changeCount(field,action){
    $(field).focus();
    var count = parseInt($(field).val()) || 0;
    var num = action==1 ? count+1 : count-1;
    if(num>=1)
      $(field).val(num);
  }
 var countButs = '\
  <span class="field-arr up" />\
  <span class="field-arr down" />\
';
  var field = _t.prop("autocomplete","off");
  st.wrap && _t.wrap(st.wrap);
  _t
  .after(countButs)
  .keypress(function(e){ return !!((e.which>=48&&e.which<=57)||e.which==8||e.which==0); })
  .keydown(function(e){
     switch(e.keyCode) {
       case 38: changeCount(field,1); break; 
       case 40: changeCount(field,2); break; 
       case 13: $("#confirmButton").click();break; 
       case 27: $("#cancelButton").click();break; 
     }
   });
  _t.parent().find('.field-arr.up').click(function(){
    changeCount(field,1);
  });
  _t.parent().find('.field-arr.down').click(function(){
    changeCount(field,2);
  });
};


$.fn.shopkeeper = function(){
  if(typeof(jQuery.livequery)!='undefined'){
    $('form',$(this)).livequery('submit',function(){
      jQuery.toCart(this);
      return false;
    });
  }else{
    $('form',$(this)).bind('submit',function(){
      jQuery.toCart(this);
      return false;
    });
  }
  if(shkOpt.counterField){
    $(this).each(function(i){
        if($("input[name='shk-count']",$(this)).is(':hidden')==false){
          $("input[name='shk-count']",$(this)).setCounterToField({style:shkOpt.style});
        }
      return this;
    });
  }
  //jQuery.refreshCart(false);
}

if (navigator.cookieEnabled==false){
  alert(langTxt['cookieError']);
}


function showHelper(elem,name,noCounter,func){
  if(typeof($(elem).get(0))=='undefined') return;
  if(shkOpt.debug){
    log.info('showHelper()');
  }
  $('#stuffHelper').remove();
  $('body').append(shkOpt.shkHelper);
  $('#cancelButton').click(function(){
    $('#stuffHelper').fadeOut(300,function(){$(this).remove()});
    return false;
  });
  $('#confirmButton').click(function(){
    func();
    return false;
  });
  if(noCounter){
    $('#stuffCount').remove();
  }else{
    $('input:text','#stuffCount').setCounterToField();
  }
  var elHelper = $('#stuffHelper');
  var btPos = getCenterPos(elHelper,elem);
  if(name){
    $('#stuffHelperName').html(name);
  }else{
    $('#stuffHelperName').remove();
  }
  $('#stuffHelper').css({'top':btPos.y+'px','left':btPos.x+'px'}).fadeIn(500,function(){$(this).find("input").select().focus()});
}


function showLoading(show){
  if(shkOpt.debug){
    log.info('showLoading(), show='+show);
  }
  if(!shkOpt.noLoader){
    if(show==true){
      $('body').append('<div id="shkLoading"></div>');
      var loader = $('#shkLoading');
      var shopCart = $('#shopCart');
      var btPos = getCenterPos(loader,shopCart);
      $('#shkLoading').css({'top':btPos.y+'px','left':btPos.x+'px'}).fadeIn(300);
    }else{
      $('#shkLoading').fadeOut(300,function(){
        $(this).remove();
      });
    }
  }
}


function getPosition(elem){
  var el = $(elem).get(0);
	var p = {x: el.offsetLeft, y: el.offsetTop}
	while (el.offsetParent){
		el = el.offsetParent;
		p.x += el.offsetLeft;
		p.y += el.offsetTop;
		if (el != document.body && el != document.documentElement){
			p.x -= el.scrollLeft;
			p.y -= el.scrollTop;
		}
	}
	return p;
}


function getCenterPos(elA,elB,Awidth,Aheight){
  if(typeof(Awidth)=='undefined') Awidth = $(elA).outerWidth();
  if(typeof(Aheight)=='undefined') Aheight = $(elA).outerHeight();
  posB = new Object();
  cntPos = new Object();
  posB = getPosition(elB);
  var correct;
  cntPos.x = Math.round(($(elB).outerWidth()-Awidth)/2)+posB.x;
  cntPos.y = Math.round(($(elB).outerHeight()-Aheight)/2)+posB.y;
  if(cntPos.x+Awidth>$(window).width()){
    cntPos.x = Math.round($(window).width()-$(elA).outerWidth())-2;
  }
  if(cntPos.x<0){
    cntPos.x = 2;
  }
  return cntPos;
}


function ajaxRequest(params,refresh){
  if(typeof(refresh)=='undefined') var refresh = true;
  if(shkOpt.debug){
    log.debug('ajaxRequest(), params='+params);
  }
  $.ajax({
    type: "POST",
    cache: false,
    url: site_url+'assets/snippets/shopkeeper/ajax-action.php',
    data: params+'&lang='+shkOpt.lang,
    success: function(data){
      showLoading(false);
      if(refresh){
        if(window.location.href.indexOf('/'+shkOpt.orderFormPage)>-1){
          $('#butOrder').hide();
        }
        var cartHeight = $('#shopCart').height();
        $('#shopCart').replaceWith(data);
        setCartActions();
        var cartheightNew = $('#shopCart').height();
        animCartHeight(cartHeight,cartheightNew);
      }
    }
    ,error: function(jqXHR, textStatus, errorThrown){
        alert(textStatus+' '+errorThrown);
    }
  });
}


jQuery.deleteItem = function(num,el,refresh){
  if(typeof(refresh)=='undefined') var refresh = true;
  var thisAction = function(){
    if(shkOpt.debug){
      log.debug('jQuery.deleteItem(), num='+num);
    }
    if(num!='all'){
      showLoading(true);
      var getParams = '&action=delete&index='+num+settings_qs;
      ajaxRequest(getParams,refresh);
    }else{
      jQuery.emptyCart();
    }
    $('#stuffHelper').fadeOut(500,function(){
      $(this).remove();
    });
  }
  if(el!=null){
    showHelper(el,langTxt['confirm'],true,thisAction);
    $('#confirmButton').text(langTxt['yes']);
  }else{
    thisAction();
  }
}


function recountItem(num,el){
  var thisAction = function(){
    var count = $('input:text','#stuffCount').val();
    $('#stuffHelper').fadeOut(500,function(){
      $(this).remove();
    });
    showLoading(true);
    var getParams = '&action=recount&index='+num+'&count='+count+settings_qs;
    ajaxRequest(getParams);
    if(shkOpt.debug){
      log.debug('recountItem(): num:'+num+', count:'+count);
    }
  }
  showHelper(el,false,false,thisAction);
  el.blur();
  var thisCount = $(el).is('a') ? parseInt($(el).text().replace(/\D* /,'')) : parseInt($(el).val().replace(/\D* /,''));
  $('input:text','#stuffCount').val(thisCount);
}


function setCartActions(){
  if(shkOpt.debug){
    log.info('setCartActions()');
  }
  var rows = $('a.shk-del','#shopCart');
  var countElem = $('input.shk-count','#shopCart');
  if($(rows).size()>0){
    $(rows).each(function(i,n){
	if(countElem.eq(i).size()>0){
	    countElem.eq(i).focus(function(){
	      recountItem(i,this);
	      return false;
	    });
	}
	if($('a.shk-del','#shopCart').eq(i).size()>0){
	    $('a.shk-del','#shopCart').eq(i).click(function(){
	      jQuery.deleteItem(i,this);
	      return false;
	    });
	}
    });
  }
    $('#butEmptyCart').click(function(){
      jQuery.deleteItem('all',this);
      return false;
    });
    if(window.location.href.indexOf('/'+shkOpt.orderFormPage)>-1){
      $('#butOrder').hide();
    }
  if(typeof(setCartActionsCallback)=='function')
    setCartActionsCallback();
}


jQuery.fillCart = function(thisForm,count,refresh){
  if(typeof(refresh)=='undefined') var refresh = true;
  if(shkOpt.debug){
    log.info('jQuery.fillCart()');
  }
  var shopCart = $('#shopCart');
  showLoading(true);
  var stuffCount = typeof(count)!='undefined' && count!='' ? '&count='+count : '';
  var getParams = '&action=fill_cart'+settings_qs+stuffCount;
  var formData = typeof(thisForm)=='object' ? $(thisForm).serialize() : 'shk-id='+thisForm;
  ajaxRequest(getParams+'&'+formData,refresh);
  if(typeof(fillCartCallback)=='function')
    fillCartCallback(thisForm);
}



jQuery.toCart = function(thisForm){
  var el = $("input[type='submit'],input[type='image'],button[type='submit']",thisForm).eq(0);
  var name = '';
  if($("input[name='shk-name']",thisForm).size()>0){
    name = $("input[name='shk-name']",thisForm).val();
  }else if($("h3",thisForm).size()>0){
    name = $("h3",thisForm).text();
  }
  if(shkOpt.debug){
    log.debug('jQuery.toCart(), name='+name);
  }
  switch(shkOpt.flyToCart){
    ////////////////////////////////////////////
    //&flyToCart=`helper`
    case 'helper':
      var thisAction = function(){
        var count = $('#stuffCount').is('*') && $('input:text','#stuffCount').val().length>0 ? parseInt($('input:text','#stuffCount').val()) : '';
        $('#stuffHelper').animate({
          top: cartPos.y+'px',
          left: cartPos.x+'px'
        },700).fadeOut(500,function(){
          $(this).remove();
          jQuery.fillCart(thisForm,count);
        });
      }
      showHelper(el,name,shkOpt.noCounter,thisAction);
      var cartPos = getCenterPos($('#stuffHelper'),$('#shopCart'));
    break;
    ////////////////////////////////////////////
    //&flyToCart=`image`
    case 'image':
      var parent = $(thisForm).parents(shkOpt.stuffCont);
      var image = $('img.shk-image:first',parent);
      if($(image).size()>0){
        var cart = $('#shopCart');
        var btPos = getPosition(image);
        var cartPos = getCenterPos(image,cart);
        $('img.shk-image:first',parent)
        .clone(true)
        .appendTo('body')
        .css({'top':btPos.y+'px','position':'absolute','left':btPos.x+'px','opacity':0.75})
        .animate({
          top: cartPos.y+'px',
          left: cartPos.x+'px'
        },700).fadeOut(500,function(){
          $(this).remove();
          jQuery.fillCart(thisForm,0);
        });
      }else{
        jQuery.fillCart(thisForm,0);
      }
      showHelper(el,langTxt['addedToCart'],true,thisAction);
      $('#confirmButton,#cancelButton').hide();
      clearTimeout(shk_timer);
      shk_timer = setTimeout(function(){
        $('#stuffHelper').fadeOut(500,function(){
          $('#stuffHelper').remove();
        });
      },1000);
    break;
    ////////////////////////////////////////////
    //&flyToCart=`nofly`
    case 'nofly':
      jQuery.fillCart(thisForm,0);
      showHelper(el,langTxt['addedToCart'],true,thisAction);
      $('#confirmButton,#cancelButton').hide();
      clearTimeout(shk_timer);
      shk_timer = setTimeout(function(){
        $('#stuffHelper').fadeOut(500,function(){
          $('#stuffHelper').remove();
        });
      },1000);
    break;
    ////////////////////////////////////////////
    default:
      jQuery.fillCart(thisForm,0);
    break;
  }
}


jQuery.additOpt = function(elem){
  var thisName = $(elem).attr('name');
  var thisNameArr = thisName.split('__');
  $('#add_'+thisNameArr[1]).remove();
  var additPriceSum = 0;
  var multiplication = new Array;
  var parent = $(elem).parents('form');
  $('select.addparam,input.addparam:checked',parent).each(function(i){
    var value = $(this).val();
    var valArr = value.split('__');
    var price = valArr[1]!='' && !isNaN(valArr[1]) ? parseFloat(valArr[1]) : 0;
    if(valArr[1]!='' && isNaN(valArr[1]) && valArr[1].indexOf('*')==0){
      multiplication[multiplication.length] = parseFloat(valArr[1].replace('*',''));
    }
    additPriceSum += price;
    if(shkOpt.debug) log.debug('additOpt(): item id='+thisNameArr[1]+', name='+valArr[0]+', price='+price);
  });
  if(additPriceSum!='' && !isNaN(additPriceSum) && !shkOpt.changePrice){
    $('.shk-price:first',parent).after('<sup id="add_'+thisNameArr[1]+'" class="price-add">+'+additPriceSum+'</sup>');
    if(shkOpt.debug) log.debug('additOpt(): item id='+thisNameArr[1]+', additPriceSum='+additPriceSum);
  }else if(!isNaN(additPriceSum) && shkOpt.changePrice){
    var priceTxt = $('.shk-price:first',parent);
    var curPrice = $(priceTxt).is(":has('span')") ? $('span',priceTxt).text() : $(priceTxt).text();
    var splitted = false;
    if(curPrice.indexOf(' ')>-1){
	curPrice = curPrice.replace(/\D* /,'');
	splitted = true;
    }
    var newPrice = parseFloat(curPrice)+additPriceSum;
    for(var i=0;i<multiplication.length;i++){
      newPrice = newPrice*multiplication[i];
    }
    if(splitted){
	newPrice = shk_numFormat(newPrice);
	curPrice = shk_numFormat(curPrice);
    }
    $(priceTxt).empty().append('<span style="display:none;">'+curPrice+'</span>'+newPrice);
    if(shkOpt.debug) log.debug('additOpt(): item id='+thisNameArr[1]+', curPrice='+curPrice+', newPrice='+newPrice);
  }
}


jQuery.emptyCart = function(refresh){
  if(typeof(refresh)=='undefined') var refresh = true;
  if(shkOpt.debug){
    log.info('emptyCart()');
  }
  showLoading(true);
  ajaxRequest('&action=empty&cart_tpl='+shkOpt.cartTpl[0],refresh);
  if(typeof(emptyCartCallback)=='function')
    emptyCartCallback();
}


jQuery.refreshCart = function(loader){
  if(typeof(loader)=='undefined') loader = true;
  if(shkOpt.debug){
    log.info('refreshCart()');
  }
  if(loader) showLoading(true);
  var getParams = '&action=refresh_cart'+settings_qs;
  ajaxRequest(getParams);
}


function animCartHeight(curH,newH){
  $('#shopCart')
  .css({'height':curH+'px','overflow':'hidden'})
  .animate({height:newH+'px'},500,function(){
    $(this).css({'overflow':'visible','height':'auto'});
  });
}


$(document).ready(function(){
  setCartActions();
  if(window.location.href.indexOf('/'+shkOpt.orderFormPage)>-1){
    $('#butOrder').hide();
  }
  $('select.addparam,input.addparam:checked',shkOptions.stuffCont).each(function(){
    jQuery.additOpt(this);
  });
  if(shkOpt.debug){
    log.info('window.location.href = '+window.location.href);
    log.info('navigator.userAgent = '+navigator.userAgent);
  }
});

})(jQuery);

if(jQuery.support.opacity){
  document.execCommand("BackgroundImageCache",false,true);
}