
var log = log4javascript.getLogger("main");
var appender = new log4javascript.InPageAppender();
log.addAppender(appender);
appender.setShowHideButton(true);
appender.setShowCloseButton(true);
appender.setHeight('190px');

appender.addEventListener('load', function(){
  //alert('log4javascript loaded');
});

//appender.evalCommandAndAppend('alert(1);');

/*
try {
		//throw new Error("Faking something going wrong!");
}catch (e) {
		log.error("An error occurred", e);
}
*/




