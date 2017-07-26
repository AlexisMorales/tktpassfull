<?php	
require_once 'paths.php';
require_once ROOT.'/vendor/autoload.php';
require_once API.'config.php';
require_once 'IO.php';

	$event = getEvent("26");
	 
	echo base64_decode(base64_encode('tktpass | '. $event['title']));
	?>
	