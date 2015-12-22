

Плагин SHKsendMailPaykeeper 1.0 для Shopkeeper (>=0.9.6 beta5)

http://modx-shopkeeper.ru/

------------------------------------------------

Плагин в письмо об изменении статуса заказа на "Принят к оплате" добавляет ссылку на страницу, на которой НЕзарегистрированный или зарегистрированный пользователь могут оплатить заказ. Для оплаты используется сниппет Paykeeper (>=1.5).

------------------------------------------------

Название плагина: SHKsendMailPaykeeper

Системное событие: OnSHKMailApprovedForPayment

Конфигурация плагина:
&mail_tpl=Mail template;string;@FILE:assets/plugins/SHKsendMailPaykeeper/mail_body.tpl &pay_doc=Paykeeper doc ID;int;1 &user_orders_doc=User orders doc ID;int;1 &mail_user_text=Text for user;string;<p>Вы можете оплатить заказ в <a [+pay_link_href+]>личном кабинете</a>.</p>

Код плагина (php):
require_once (MODX_BASE_PATH.'assets/plugins/SHKsendMailPaykeeper/SHKsendMailPaykeeper.php');






