
http://modx-shopkeeper.ru/

---------------------------------------------------

addWebUserFields 1.5.2

Плагин для MODx (1.x.x) + Shopkeeper (0.9.x)

---------------------------------------------------

Сохранение и редактирование дополнительной информации для веб-пользователей.

---------------------------------------------------

Установка
---------

1. Поместить папку addWebUserFields в папку с плагинами assets/plugins/.

2. В системе управления открыть "Элементы" -> "Управление элементами" -> "Плагины" -> "Создать плагин".

3.
   Код плагина (php):
   require MODX_BASE_PATH.'assets/plugins/addWebUserFields/addWebUserFields_plugin.php';
  
   Название плагина:
   addWebUserFields
  
   Системные события:
   OnWUsrFormRender, OnWebSaveUser

4. В код сниппета WebSignup (assets/snippets/weblogin/websignup.inc.php) после строки 26:
   else if ($isPostBack){
   добавить:

   //added for addWebUserFields plugin
   $modx->invokeEvent("OnWUsrFormRender"); 

5. В шаблон формы регистрации добавить дополнительные поля с префиксом "addit__".
   Пример:
   <input type="text" name="addit__address" size="30" value="[+addit__address+]" />


---------------------------------------------------

Создание чекбокса в доп. полях.
---------

Пример:

<input type="checkbox" name="addit__sms__checkbox" value="1" />
<input type="hidden" name="addit__sms__checkbox_h" value="0" />

Если чекбокс не отмечен, в базу данных сохранится значение "0" из поля "addit__sms__checkbox_h".



