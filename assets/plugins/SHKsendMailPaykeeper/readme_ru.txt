

������ SHKsendMailPaykeeper 1.0 ��� Shopkeeper (>=0.9.6 beta5)

http://modx-shopkeeper.ru/

------------------------------------------------

������ � ������ �� ��������� ������� ������ �� "������ � ������" ��������� ������ �� ��������, �� ������� �������������������� ��� ������������������ ������������ ����� �������� �����. ��� ������ ������������ ������� Paykeeper (>=1.5).

------------------------------------------------

�������� �������: SHKsendMailPaykeeper

��������� �������: OnSHKMailApprovedForPayment

������������ �������:
&mail_tpl=Mail template;string;@FILE:assets/plugins/SHKsendMailPaykeeper/mail_body.tpl &pay_doc=Paykeeper doc ID;int;1 &user_orders_doc=User orders doc ID;int;1 &mail_user_text=Text for user;string;<p>�� ������ �������� ����� � <a [+pay_link_href+]>������ ��������</a>.</p>

��� ������� (php):
require_once (MODX_BASE_PATH.'assets/plugins/SHKsendMailPaykeeper/SHKsendMailPaykeeper.php');






