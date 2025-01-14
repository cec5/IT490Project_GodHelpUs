<?php

require_once('../rabbitmq_files/path.inc');
require_once('../rabbitmq_files/get_host_info.inc');
require_once('../rabbitmq_files/rabbitMQLib.inc');

function createRabbitMQClientDMZ($request){

	//Use this to handle rmq requests to the dmz
	//Still using testServer for now as it works but may change later
	
	$client = new rabbitMQClient("../rabbitmq_files/rabbitMQ_dmz.ini","testServer");

	if (isset($argv[1])){
	       	$msg = $argv[1];
	}
	else{
		$msg = "default message for dmz-bound client";
	}

	$response = $client->send_request($request);

	return $response;
}
?>
