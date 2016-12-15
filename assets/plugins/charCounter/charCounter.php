<?php

//***********************************
//charCounter plugin v1.0 for MODx 1.0.x
//***********************************
//Andchir  http://wdevblog.net.ru
//***********************************
// Configuration: &maxlength=maxlength;int;200
// System Events: OnDocFormRender
//***********************************

defined('IN_MANAGER_MODE') or die();

if(!isset($maxlength)) $maxlength = 200;
$e = &$modx->Event;

if ($e->name == 'OnDocFormRender') {

$output = <<< OUT

<!-- charCounter -->
<script type="text/javascript">
window.addEvent('domready', function(){
  var counterElement  = new Element('span', {id: 'counter_number'});
  var br = new Element('br');
	$('mutate').getElements('textarea[name=introtext]')
  .addEvent('keyup', function() {
		var max_chars = $maxlength;
		var current_value	= $(this).value;
		var current_length	= current_value.length;
		var remaining_chars = max_chars-current_length;
		$('counter_number').innerHTML = remaining_chars;
		if(remaining_chars<=5){
			$('counter_number').setStyle('color', '#990000');
		} else {
			$('counter_number').setStyle('color', '#666666');
		}
	})
	.getParent()
	.adopt(br,counterElement);
});
</script>
<!-- /charCounter -->

OUT;

$e->output($output);

}


?>