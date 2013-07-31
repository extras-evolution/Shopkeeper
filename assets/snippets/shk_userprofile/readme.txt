
Сниппет SHKUserProfile 1.4.4

Личный кабинет покупателя.

http://modx-shopkeeper.ru/

------------------------------------------------

Сниппет выводит списох текущих и архив заказов пользователя. Пользователь может редактировать свои данные,
указанные при регистрации. Работает совместно со сниппетом Paykeeper. Если статус заказа "Принят к оплате",
то можно оплатить заказ через Robokassa или WebMoney. Также у пользователя есть возможность до оплаты отказаться от заказа или после оплаты повторить предыдущий заказ.
Поддерживается плагин addWebUserFields, с помощью которого можно добавлять доп. поля для пользователя при регистрации.
В шаблонах сниппета поддерживается PHx.

------------------------------------------------

Название сниппета:

SHKUserProfile



Код сниппета:

<?php

return require "assets/snippets/shk_userprofile/shk_userprofile.inc.php";

?>

------------------------------------------------

Парамеры:

&id - ID сниппета. Полезно при вызове несколько раз на одной странице.

&lang - язык. По умолчанию язык системы управления.

&profileTpl - Имя чанка или файл (@FILE:) с шаблоном для страницы редактирования профиля. По умолчанию: @FILE:assets/snippets/shk_userprofile/tpl/profile.tpl.

&ordersListTpl - Шаблон (чанк) со списком заказов. По умолчанию: @FILE:assets/snippets/shk_userprofile/tpl/ordersList.tpl.

&orderDescTpl - Шаблон просмотра состава заказа. По умолчанию: @FILE:assets/snippets/shk_userprofile/tpl/orderDesc.tpl.

&purchasesListTpl - Шаблон для списка товаров в заказе на странице со списком заказов (по умолчанию отключено - `false`). Пример: `@FILE:assets/snippets/shk_userprofile/tpl/purchasesList.tpl`

&menuTpl - Шаблон первого уровня меню. По умолчанию: @FILE:assets/snippets/shk_userprofile/tpl/menu.tpl.

&subMenuTpl - Шаблон второго уровня меню. По умолчанию: @FILE:assets/snippets/shk_userprofile/tpl/subMenu.tpl.

&excepDigitGroup - разделять числа цен в корзине на разряды.

&oneLevelMenu=`true` - строить меню в один уровень.

&additFieldsTpl - Шаблон для дополнительных полей. По умолчанию: @FILE:assets/snippets/shk_userprofile/tpl/addit_fields.tpl.

&additFieldsHide - Не давать редактировать (и не показывать) пользователю доп. поля (чарез запятую). По умолчанию: count_purchase.
Пример: &additFieldsHide=`count_purchase,distribution`.

&showOrder=`true` - показать информацию о заказе и оплатить (если установлен сниппет Paykeeper). Используется на странице оплаты для незарегистрированного покупателя.

&display - число заказов на странице при выводе архива заказов. 

&action - страница по умолчаню. Возможные значения: profile (профиль), curorders (текущие заказы), archorders (архив заказов).

Можно передавать любые параметры сниппету Paykeeper (c префиксом "pay_").
Пример: &pay_payment_method=`robokassa`.
В шаблоне orderDescTpl этот сниппет выводится в месте плейсхолдера [+payment_snippet+].


------------------------------------------------

Примеры вызова:

Шаблоны по умолчанию:
[!SHKUserProfile!]

Вызов с параметрами:
[!SHKUserProfile? &purchaseTpl=`@FILE:assets/snippets/shk_userprofile/tpl/purchase.tpl`&oneLevelMenu=`true`!]

На странице оплаты для незарегистрированного пользователя (вывод информации о заказе):
[!SHKUserProfile? &showOrder=`true`&orderDescTpl=`@FILE:assets/snippets/shk_userprofile/tpl/orderDescNotRegd.tpl`!]


