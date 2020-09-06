<?php

/* Zibal Payment Gateway Plugib for MyBB Ver:1.0
Author : Yahya Kangi 
*/

	// include_once('nusoap.php');
	include_once('zibal_functions.php');
	define("IN_MYBB", "1");
	require("./global.php");
	
	// if($_SERVER['REQUEST_METHOD']!="POST") die("Forbidden!");

	$merchantID = $mybb->settings['myzibal_merchant'];
	$num = $_POST['myzibal_num'];
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."myzibal WHERE num=$num");
    $myzibal = $db->fetch_array($query);
	$amount = $myzibal['price']; //Amount will be based on Toman
	$callBackUrl = "{$mybb->settings['bburl']}/zibal_verfywg.php?num={$myzibal['num']}";
	$desc = "{$myzibal['description']}  ({$mybb->user['username']})";
	
if ($mybb->settings['myzibal_soap'] == 0)
{
	// $client = new SoapClient('https://de.zibal.com/pg/services/WebGate/wsdl', array('encoding'=>'UTF-8'));
	// $res = $client->PaymentRequest(
	// array(
	// 				'MerchantID' 	=> $merchantID ,
	// 				'Amount' 		=> $amount ,
	// 				'Description' 	=> $desc ,
	// 				'Email' 		=> '' ,
	// 				'Mobile' 		=> '' ,
	// 				'CallbackURL' 	=> $callBackUrl

	// 	)
	// );

	$data = array(
		'merchant' 	=> $merchantID ,
		'amount' 		=> $amount ,
		'description' 	=> $desc ,
		'callbackUrl' 	=> $callBackUrl
	);
	$res = postToZibal('request', $data);

}
// if ($mybb->settings['myzibal_soap'] == 1)
// {
// 	$client = new nusoap_client('https://de.zibal.com/pg/services/WebGate/wsdl', 'wsdl');
// 	$res = $client->call('PaymentRequest', array(
// 			array(
// 					'MerchantID' 	=> $merchantID ,
// 					'Amount' 		=> $amount ,
// 					'Description' 	=> $desc ,
// 					'Email' 		=> '' ,
// 					'Mobile' 		=> '' ,
// 					'CallbackURL' 	=> $callBackUrl

// 		)
	
	
// 	));
// }
	
	
	if($res->result == 100){
	Header('Location: https://gateway.zibal.ir/start/' . $res->trackId );
	}else{
		echo'ERR:'.$res->result.' '.resultCodes($res->result);
	}
?>
