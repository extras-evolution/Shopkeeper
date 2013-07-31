
catalogView 1.2.5

Сниппет для вывода товаров в каталоге Shopkeeper (>=1.0) или документов MODx.
Может использоваться вместо Ditto.

-------------------

http://modx-shopkeeper.ru/

-------------------------------------

Код сниппета:

<?php
$output = '';
require MODX_BASE_PATH.'assets/snippets/catalogView/catalogView.inc.php';
return $output;
?>

-------------------------------------

Параметры:

lang - язык. По умолчанию - russian-UTF-8.
id - ID для текущего вызова сниппета. Нужно для постраничной навигации, если на странице сниппет вызывается несколько раз.
noResult - текст, выводимый, если в категории нет товаров. По умолчанию - $langTxt['noResult'] (Ничего не найдено.).
parents - ID категории товаров (документ MODx). Можно несколько через запятую.
products - ID товара. Можно несколько через запятую.
tpl - имя чанка для товаров. Можно использовать "@FILE:", "@CODE:". По умолчанию - @FILE:assets/snippets/catalogView/chunks/catalogRow_chunk.tpl.
descTpl - имя чанка с шаблоном страницы подробной информации о товаре. По умолчанию - @FILE:assets/snippets/catalogView/chunks/catalogDesc_chunk.tpl.
paginate - использовать постраничную разбивку (1|0).
randomize - выводить товары в случайном порядке (1|0). По умолчанию - 0 (отключено).
where - SQL условие для выборки. Пример: &where=`(tvc.tmplvarid @eq '1' AND tvc.value @eq '300')` - ищет все товары со значением '300' TV с ID 1. "@eq" - это "=". sc. - поля товара (документа), tvc. - TV-параметры.
filter - фильтрация товаров как у Ditto - http://ditto.modxcms.com/tutorials/basic_filtering.html.
sqlFilter - переводить первый фильтр (&filter) в SQL (1|0). По умолчанию выключено (0). Если производится фильтрация по TV и используются значения по умолчанию, рекомендуется отключить.
display - число товаров на странице. По умолчанию не ограничего.
sortBy - имя поля для сортировки (id|pagetitle|parent|template|menuindex|createdon). По умолчанию - id. Можно указать имя TV-параметра.
sortDir - направления сортировки (asc|desc). По умолчанию - asc (по возрастанию).
sortBy_type - тип сортировки (string|integer). `string` - строка (по алфавиту), `integer` - число.
pageParentClass - CSS-класс для родительского элемента страниц. По умолчанию - pages.
pageClass - CSS-класс ссылок на страницы. По умолчанию - page.
currentPageClass - CSS-класс текущей страницы. По умолчанию - current.
dataType - тип выводимых данных (documents|products). documents - документы MODx, products - товары из каталога Shopkeeper.
renderTVDisplayFormat - переводить значения TV в формат "визуального компонента" (1|0). По умолчанию выключено (0).
fetchContent - включить в выборку из БД содержимое поля "content" (1|0).
toPlaceholder - поместить вывод сниппета в плейсхолдер. Указать название плейсхолдера. Полезно, например, если нужно вывести постраничную навигацию сверху (и снизу) списка товаров (см. пример ниже).
skipDesc - не выводить страницу с подробным описанием. Полезно когда используется вложенный вызов catalogView в чанке descTpl.

-------------------------------------

Плейсхолдеры на странице вызова сниппета:

[+pages+] - постраничная навигация.
[+total+] - всего товаров.
[+currentPage+] - текущая страница.
[+display+] - сколько показано на странице.
[+totalPages+] - всего страниц.
[+sortBy+] - поле для текущей сортировки.
[+sortDir+] - направление текущей сортировки.
[+sortDirOther+] - направление сортировки обратное текущему.
[+pagesQueryString+] - query string (GET параметры) для постраничной навигации.
[+filter+] - строка фильтрации.

При использовании &id все плейсхолдеры сниппета (не в чанке) нужно указывать с префиксом "ID." (ваш ID и точка). Например если &id=`main`, то плейсхолдер постраничной навигации будет: [+main.pages+]

---

Плейсхолдеры в чанке сниппета:

[+pagetitle+] - наименование товара;
[+introtext+] - краткое описание товара;
[+url+] - ссылка на подробное описание;
[+id+] - ID товара;
[+createdon+] - дата создания;
[+parent+] - ID категории товара;
[+cv_iteration+] - порядковый номер элемента от нуля;
[+cv_item_num+] - порядковый номер элемента от еденицы;
[+active+] - если ID элемента совпадает с ID текущей страницы, выводит "active";
А также любые по имени TV, например [+price+].

-------------------------------------

Используемые GET-параметры:

sortby, sortdir, page, display, start, sorttype, filter

Пример адресной строки с сортировкой по цене:

http://[адрес сайта]/category.html?sortby=price&sortdir=asc&sorttype=integer&page=1

-------------------------------------

Пример вызова с постраничной разбивкой:

[!catalogView? &parents=`87`&paginate=`1`&sortBy=`pagetitle`&sortDir=`asc`!]

[+pages+]

------------

Если установлен плагин PHx, он может вырезать плейсхолдеры.

Тогда для вывода постраничной навигации можно использовать сниппет "include":

[!include? &placeholder=`pages`!]

------------

Пример с выводом постраничной навигации сверху и снизу списка товаров:

[!catalogView? &id=`main`&tpl=`catalogRow`&paginate=`1`&toPlaceholder=`catalogViewOutput`&display=`10`!]

[!include? &placeholder=`main.pages`!]

[!include? &placeholder=`main.catalogViewOutput`!]

<br clear="all" />

[!include? &placeholder=`main.pages`!]


-------------------------------------
-------------------------------------

Сниппет include

Вставка некэшируемых чанков, сниппетов из PHP-файлов, плейсхолдеров и данных из сессии.

Параметры:

file - php-файл сниппета (путь от корня сайта).
chunk - чанк. Имя чанка, файл (@FILE:...) или код (@CODE:...).
placeholder - плейсхолдер. Нужен, если установлен плагин PHx, т.к. он может вырезать плейсхолдеры.
session - сессия.
parse - включить парсер чанка (1|0).

-------------------------------------
-------------------------------------

Сниппет getChildParents

Выводит список ID дочерних документов по родителю. Рекомендуется вызывать только кэшированным.

Параметры:

parent - ID "родительского" документа.
onlyFolders - только "контейнеры", содержащие дочерние документы (1|0). По умолчанию выключено (`0`).
depth - число вложенностей.

Пример с поиском товаров по всем вложенным категориям:

[!catalogView?
&tpl=`@FILE:assets/snippets/catalogView/chunks/catalogRow_chunk.tpl`
&parents=`[[include? &file=`assets/snippets/catalogView/elements/getChildParents.php`&parent=`2`&onlyFolders=`1`]]`
&paginate=`1`
&sortBy=`pagetitle`
&sortDir=`asc`
!]

-------------------------------------
-------------------------------------

Название товара из каталога Shopkeeper в тайтле.

Сниппет shk_pagetitle

Пример:

<title>[(site_name)] | [*pagetitle*][!include? &file=`assets/snippets/catalogView/elements/shk_pagetitle.php`&out_prefix=` | `!]</title>



