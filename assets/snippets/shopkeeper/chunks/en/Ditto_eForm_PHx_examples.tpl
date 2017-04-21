
[!Ditto? &parents=`[*id*]`&tpl=`shopStuff`!]
[!Ditto? &tpl=`shopStuff`&extenders=`SHKwidget`&wtTVname=`param1,param2`&wtFormat=`select,checkbox`!]


[+phx:if=`[*id*]`:ne=`10`:then=`[!Shopkeeper? &cartType=`small`&orderFormPage=`10`!]`+]


[+phx:if=`[[UltimateParent? &topLevel=`1`]]`:ne=`[*id*]`:then=`{{shopToCart}}`+]


[!Shopkeeper? &orderFormPage=`10`!]
[!eForm? &formid=`shopOrderForm`&tpl=`shopOrderForm`&report=`shopOrderReport`&vericode=`1`&ccsender=`1`&gotoid=`11`&subject=`Order report`&eFormOnBeforeMailSent=`populateOrderData`&eFormOnMailSent=`sendOrderToManager`!]


<!-- If using registration (Customers - webgroup) -->
<!-- Page - not Cacheable! -->
[!Shopkeeper? &orderFormPage=`10`&gotoid=`11`!]
[+phx:mo=`Customers`:then=`[!eForm? &noemail=`0`&formid=`shopOrderForm`&protectSubmit=`0`&tpl=`shopOrderFormWebUser`&report=`shopOrderReportWebUser`&vericode=`0`&ccsender=`1`&subject=`Order report`&eFormOnBeforeMailSent=`populateOrderData`&eFormOnMailSent=`sendOrderToManager`!]`:else=`[!eForm? &noemail=`0`&formid=`shopOrderForm`&protectSubmit=`0`&tpl=`shopOrderForm`&report=`shopOrderReport`&vericode=`0`&ccsender=`1`&subject=`Order report`&eFormOnBeforeMailSent=`populateOrderData`&eFormOnMailSent=`sendOrderToManager`!]`+]
