<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo $mod_name; ?></title>
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

    <link rel="stylesheet" type="text/css" href="media/style/<?php echo $theme; ?>/style.css" />
    <link rel="stylesheet" type="text/css" href="<?php echo MODULE_PATH.'tpl/valums-file-uploader/'; ?>fileuploader.css" />

    <script type="text/javascript" src="media/script/mootools/mootools.js"></script>
    <script type="text/javascript" src="media/script/mootools/moodx.js"></script>
    <script type="text/javascript" src="media/script/tabpane.js"></script>
    <script type="text/javascript" src="<?php echo MODULE_PATH.'tpl/valums-file-uploader/'; ?>fileuploader.js"></script>
    <script type="text/javascript">
    function setMoveValue(pId, pName){
        $('parentId').set({'value':pId});
        $('parentName').innerHTML = '<big><b>'+pName+'</b></big>';
        setTimeout(function(){
            setFieldAction(document.getElementById('docButSelect'));
        },100);
    }
    function setFieldAction(elem){
        if(elem.childNodes[0].nodeValue=="Выбрать"){
            elem.childNodes[0].nodeValue = "Стоп";
            parent.tree.ca = "move";
        }else{
            parent.tree.ca = "open";
            elem.childNodes[0].nodeValue = "Выбрать";
        }
    }
    function importAction(){
	if($('parentId').value==''){
            alert('Выберите в дереве документов категорию каталога');
        }else if($('file').value=='' && $('file_path').value==''){
            alert('Выберите файл для импорта');
        }else if($('configFile').value==''){
            alert('Выберите конфигурацию');
        }else{
            postForm('import',null,null);
        }
    }
    function exportAction(){
        if($('parentId').value==''){
            alert('Выберите в дереве документов категорию каталога');
        }else if($('configFile').value==''){
            alert('Выберите конфигурацию');
        }else{
            postForm('export',null,null);
        }
    }
    function cleanAction(){
        if($('configFile').value==''){
            alert('Выберите конфигурацию');
        }else{
            if(confirm('Вы уверены?')){postForm('clean_parent',null,null);}
        }
    }
    function postForm(action, id, value){
        document.cfmodule.cf_action.value = action;
        if (id != null) document.cfmodule.item_id.value = id;
        if (value != null) document.cfmodule.item_val.value = value;
        document.cfmodule.submit();
    }
    setTimeout(function(){
        if($('mod_message')!=null) $('mod_message').setStyle('display','none');
    },4000);
    </script>
	
</head>
<body>
<br />
<div class="sectionHeader"><?php echo $mod_name; ?></div>
<div class="sectionBody">
    <div style="min-height:250px;">

<?php if(!empty($catalogFill->message)) echo '<p id="mod_message"><b>'.$catalogFill->message.'</b></p>'; ?>

  <br />
  <div id="parentName" style="height:20px;">Выберите раздел каталога.</div>
  <br /><br />

<form name="cfmodule" action="<?php echo $cf_config['mod_page']; ?>" enctype="multipart/form-data" method="post">
    <input name="cf_action" type="hidden" value="" />
    <input name="item_id" type="hidden" value="" />
    <input name="item_val" type="hidden" value="" />
    <input id="parentId" type="hidden" name="parent" value="" />
    <button id="docButSelect" type="button" style="width:120px;" onclick="setFieldAction(this);return false">Выбрать</button>
    &nbsp;|&nbsp;
    
    <select id="configFile" name="config" style="width:120px;">
        <option value="">--конфигурация--</option>
        <?php echo $config_list; ?>
    </select>
    
    <br /><br />
    
    <div id="catalogFillPane" class="tab-pane">
	<script type="text/javascript"> 
	    tpCatalogFill = new WebFXTabPane(document.getElementById('catalogFillPane'), true); 
	</script>
	<!-- import -->
	<div id="tabImport" class="tab-page">
	    <h2 class="tab">Импорт</h2>
	    <script type="text/javascript">tpCatalogFill.addTabPage(document.getElementById('tabImport'));</script>
	    
	    <div>
		Выберите файл для импорта.
		<br /><br />
		<div id="file_uploader"></div>
		<script type="text/javascript">
		function createUploader(){
		    var uploader = new qq.FileUploader({
			element: document.getElementById('file_uploader'),
			action: '<?php echo MODULE_PATH.'tpl/valums-file-uploader/'; ?>upload.php',
			allowedExtensions: ['csv','xls','xlsx'],
			sizeLimit: 0, // max size
			minSizeLimit: 0, // min size
			multiple: false,
			template: '<div class="qq-uploader">'
			    +'<div class="qq-upload-drop-area"><span>Drop files here to upload</span></div>'
			    +'<div class="qq-upload-button">Обзор</div>'
			    +'<ul class="qq-upload-list"></ul>'
			 +'</div>',
			fileTemplate: '<li>'
			    +'<span class="qq-upload-file"></span>'
			    +'<span class="qq-upload-spinner"></span>'
			    +'<span class="qq-upload-size"></span>'
			    +'<a class="qq-upload-cancel" href="#">Отмена</a>'
			    +'<span class="qq-upload-failed-text">Ошибка</span>'
			+'</li>',
			onComplete: function(id, fileName, responseJSON){
			    if(typeof(responseJSON.success)=='boolean' && responseJSON.success==true){
			      $$('#file').set({value:responseJSON.filename});
			      $$('div.qq-upload-button, div.qq-upload-drop-area').setStyle('display','none');
			    }
			},
			debug: false
		    });
		}
		window.onload = createUploader;
		</script>
		<!--input id="file" type="file" name="file" /-->
		<input id="file" type="hidden" name="file" value="" />
		<label><input type="radio" name="cleanimport" value="1" checked="checked" /> Обновить</label>
		<label><input type="radio" name="cleanimport" value="0" /> Добавить</label>
		<br /><br />
		<select id="file_path" name="file_path" style="width:120px;">
		    <option value="">--файлы--</option>
		    <?php echo $files_list; ?>
		</select>
		<br /><br />
	    </div>
	    <div>
		<button style="width:120px;" id="buttonImport" type="button" onclick="importAction();return false;">Импорт</button>
		<button style="width:190px;" id="buttonClean" type="button" onclick="cleanAction();return false;">Очистить категорию</button>
	    </div>
	    
	</div>
	<!-- /import -->
	<!-- export -->
	<div id="tabExport" class="tab-page">
	    <h2 class="tab">Экспорт</h2>
	    <script type="text/javascript">tpCatalogFill.addTabPage(document.getElementById('tabExport'));</script>
	    
	    <div>
		Формат экспорта.
		<br /><br />
		<label><input type="radio" name="exp_type" value="csv" checked="checked" /> CSV</label>
		<label><input type="radio" name="exp_type" value="xls" /> XLS</label>
		<label><input type="radio" name="exp_type" value="xlsx" /> XLSX</label>
		<br /><br />
	    </div>
	    <div>
		<button style="width:120px;" id="buttonExport" type="button" onclick="exportAction();return false;">Экспорт</button>
	    </div>
	    
	</div>
	<!-- /export -->
    </div>

    
</form>

    </div>
    <br /><br />
    <hr />
    В папке import файлов: <?php echo $imp_count; ?> <a href="#" onclick="if(confirm('Вы уверены?')){postForm('clean','import',null);}return false;">очистить</a>
    <br />
    В папке export файлов: <?php echo $exp_count; ?> <a href="#" onclick="if(confirm('Вы уверены?')){postForm('clean','export',null);}return false;">очистить</a>
</div>
</body>
</html>