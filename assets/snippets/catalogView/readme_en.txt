
catalogView 1.2.5

Snippet to display the items in the catalog Shopkeeper (> = 1.0) or documents MODx (& dataType = `documents`).
Can be used instead Ditto.

-------------------

http://modx-shopkeeper.ru/

-------------------------------------

Snippet code (php):

<?php
$output = '';
require MODX_BASE_PATH.'assets/snippets/catalogView/catalogView.inc.php';
return $output;
?>

-------------------------------------

Parameters:

lang - language. Default - russian-UTF-8.
id - ID for the current call snippet. Need for pagination, if a page has a snippet called multiple times.
noResult - text displayed if the category is empty. Default - $langTxt['noResult'] (Nothing was found.).
parents - ID product category (document MODx). Can be more one separated by commas.
products - ID of the goods. Can be more one separated by commas.
tpl - the name of the chunk for the goods. You can use the "@FILE:", "@CODE:". By default - @FILE:assets/snippets/catalogView/chunks/catalogRow_chunk.tpl.
descTpl - the name of the chunk with the template page of detailed information about the product. By default - @FILE:assets/snippets/catalogView/chunks/catalogDesc_chunk.tpl.
paginate - use the pagination (1|0).
randomize - display products in a random order (1|0). Default - 0 (disabled).
where - SQL condition for sampling. Example: &where=`(tvc.tmplvarid @eq '1' AND tvc.value @eq '300')` - looking for all the goods with a value of '300' TV with ID '1'. "@eq" - a "=". sc. - products field (document), tvc. - TV-parameters.
filter - filtration products like Ditto - http://ditto.modxcms.com/tutorials/basic_filtering.html.
sqlFilter - set the first filter (&filter) to SQL (1|0). Disabled by default (0).
display - the number of products per page.
sortBy - field name to sort (id|pagetitle|parent|template|menuindex|createdon). By default - id. You can specify the name of the TV-parameter.
sortDir - the sort direction (asc|desc). Default - asc (ascending).
sortBy_type - sort type (string|integer). `string` - line (in alphabetical order), `integer` - number.
pageParentClass - CSS-class for the parent pages. By default - pages.
pageClass - CSS-class links on the page. By default, the - page.
currentPageClass - CSS-class of the current page. By default - current.
dataType - the type of output data (documents|products). documents - documents MODx, products - products from the catalog Shopkeeper.
renderTVDisplayFormat - to translate the values of TV in the format "visual component" (1|0). The default is off (0). 
fetchContent - include in the query from the database value of the "content" (1|0).
toPlaceholder - put the snippet in the output placeholder. Specify the name of the placeholder. Useful, for example, if you want to display pagination above (and below) Product List (see example below).
skipDesc - no print description page.


-------------------------------------

Placeholder on the page calling the snippet:

[+pages+] - pagination.
[+total+] - the number of products.
[+currentPage+] - the current page.
[+display+] - how many shown on the page.
[+totalPages+] - total pages.
[+sortBy+] - the field for the current sort order.
[+sortDir+] - the direction of the current sort order.
[+sortDirOther+] - the sort direction opposite to the current.
[+pagesQueryString+] - query string (GET parameters) for pagination.
[+filter+] - Line filter.

When using the &id all placeholder snippet (not the chunk) must be specified with the prefix "ID." (enter your ID and point). For example, if &id=`main`, then the placeholder page navigation will be: [+main.pages+]

---

Плейсхолдеры в чанке сниппета:

[+pagetitle+] - name of the product.
[+introtext+] - a short description of the goods.
[+url+] - link to the detailed description.
[+id+] - ID of the goods.
[+createdon+] - date of creation.
[+parent+] - ID of category of goods.
[+cv_iteration+] - serial number of the element of zero;
[+cv_item_num+] - serial number of the element of one unit;
[+active+] - if the ID of the element coincides with the ID of the current page, displays the "active";

And as any named TV, for example [+price+].

-------------------------------------

GET-parameters are used:

sortby, sortdir, page, display, start, sorttype, filter

An example of the address bar, sorted by price:

http://[site address]/category.html?sortby=price&sortdir=asc&sorttype=integer&page=1

-------------------------------------

Example call with pagination:

[!catalogView? &parents=`87`&paginate=`1`&sortBy=`pagetitle`&sortDir=`asc`!]

[+pages+]

------------

If the plugin installed PHx, he can cut placeholder.

Then for the display of pagination you can use a snippet "include":

[!include? &placeholder=`pages`!]

-------------------------------------
-------------------------------------

Snippet include

Insert non-cached chunks, snippets of PHP-files and placeholder.

Parameters:

file - php-snippet file (the path from the root site).
chunk - chunk. Name or code chunk (@CODE:...).
parse - to include a chunk parser (1|0).
placeholder - placeholder. Needed if the plugin installed PHx, as he can cut placeholder.

-------------------------------------
-------------------------------------

An example of the withdrawal of pagination top and bottom of the list of goods:

[!catalogView? &id=`main`&tpl=`catalogRow`&paginate=`1`&toPlaceholder=`catalogViewOutput`&display=`10`!]

[!include? &placeholder=`main.pages`!]

[!include? &placeholder=`main.catalogViewOutput`!]

<br clear="all" />

[!include? &placeholder=`main.pages`!]

-------------------------------------
-------------------------------------

Snippet getChildParents

Displays a list of ID of child documents of the parent. Recommended to call only cached.

Parameters:

parent - ID "parent" document.
onlyFolders - only a "container" that contains child documents (1|0). Disabled by default (`0`).

An example of the search for the goods on all the nested categories:

[!catalogView?
&parents=`[[include? &file=`assets/snippets/catalogView/elements/getChildParents.php`&parent=`2`&onlyFolders=`1`]]`
&paginate=`1`
&sortBy=`pagetitle`
&sortDir=`asc`
!]
