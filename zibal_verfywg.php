<?php

/* Zibal Payment Gateway Plugib for MyBB Ver:1.0
Author : Yahya Kangi
*/

	// include_once('nusoap.php');
	include_once('zibal_functions.php');

    // if ($_SERVER['REQUEST_METHOD']!="POST") {
    //     die("Forbidden!");
    // }
    
    define("IN_MYBB", "1");
    require("./global.php");
    if (!$mybb->user['uid']) {
        error_no_permission();
    }
    
    if (isset($_GET['trackId']) && isset($_GET['status'])) {
		// $au = $_GET['Authority'];
		$trackId = $_GET['trackId'];
        $merchant = $mybb->settings['myzibal_merchant'];
        $num = $_GET['num'];
        $query0 = $db->query("SELECT * FROM ".TABLE_PREFIX."myzibal WHERE num=$num");
        $myzibal0 = $db->fetch_array($query0);
        $amount = $myzibal0['price'];
        $gid = $myzibal0['group'];
        $pgid = $mybb->user['usergroup'];
        $uid = $mybb->user['uid'];
        $time = $myzibal0['time'];
        $period = $myzibal0['period'];
        $status = $_GET['status'];
        $bank = $myzibal0['bank'];
        if ($status == "2") {
            // if ($mybb->settings['myzibal_soap'] == 0) {
            //     $client = new SoapClient('https://de.zibal.com/pg/services/WebGate/wsdl', array('encoding'=>'UTF-8'));
            //     $res = $client->PaymentVerification(
            //         array(
            //                         'MerchantID'	 => $merchant ,
            //                         'Authority' 	 => $au ,
            //                         'Amount'	 	 => $amount
            //                     )
            //     );
            // }
            // if ($mybb->settings['myzibal_soap'] == 1) {
            //     $client = new nusoap_client('https://de.zibal.com/pg/services/WebGate/wsdl', 'wsdl');
            //     $res = $client->call("PaymentVerification", array(
            //                     array(
            //                         'MerchantID'	 => $merchant ,
            //                         'Authority' 	 => $au ,
            //                         'Amount'	 	=> $amount
            //                     )
            //         ));
            // }
			$data = array(
				'merchant' => $merchant,
				'trackId' => $trackId
			);	
			$result = postToZibal('verify', $data);
			
            $res = $result->result;
            $refNumber = $res->refNumber;
        } else {
            $res = 0;
            $refNumber = 0;
            //$info = "عملیات پرداخت توسط کاربر کنسل شده است";
        }
                        
        $query1 = $db->simple_select("myzibal_tractions", "*", "trackid='$trackId'");
        $check1 = $db->fetch_array($query1);
        if ($check1) {
            $info = "این تراکنش قبلاً ثبت شده است. بنابراین شما نمی‌توانید به صورت غیر مجاز از این سیستم استفاده کنید.";
        } else {
            $query2 = $db->simple_select("myzibal", "*", "`num` = '$num'");
            while ($check = $db->fetch_array($query2)) {
                if ($amount != $res->amount) {
                    $info = "اطلاعات داده شده اشتباه می باشد . به همین دلیل عضویت انجام نشد.";
				}
				
				if ($res != 100) {
					$info = resultCodes($res);
				}

                // if ($res == -1) {
                //     $info = "اطلاعات وارد شده کامل نمي باشد . به همين دليل عضويت شما انجام نشد.";
                // }

                // if ($res == -2) {
                //     $info = "نحوه دستيابي شما به اين صفحه معتبر نمي باشد . به همين دليل عضويت شما انجام نشد.";
                // }

                // if ($res == 0) {
                //     $info = "عمليات پرداخت شما به طور کامل انجام نشده است. به همين دليل عضويت شما انجام نشد.";
                // }

                // if ($res == -11) {
                //     $info = "شما از يک تراکنش جعلي براي عضويت استفاده کرده ايد.";
                // }

                // if ($res == -12) {
                //     $info = "عمليات پرداخت به طور کامل انجام نشده است . به همين دليل عضويت شما انجام نشد.";
                // }

                if ($res == 100) {
                    $query1 = $db->simple_select('usergroups', 'title, gid', '1=1');
                    while ($group = $db->fetch_array($query1)) {
                        $groups[$group['gid']] = $group['title'];
                    }
                    $query5 = $db->simple_select('users', 'username, uid', '');
                    while ($uname1 = $db->fetch_array($query5, 'username, uid')) {
                        $usname[$uname1['uid']] = $uname1['username'];
                    }
                }
                if ($time == "1") {
                    $dateline = strtotime("+{$period} days");
                }

                if ($time == "2") {
                    $dateline = strtotime("+{$period} weeks");
                }
                if ($time == "3") {
                    $dateline = strtotime("+{$period} months");
                }
                if ($time == "4") {
                    $dateline = strtotime("+{$period} years");
                }
                $stime = time();
                $add_traction = array(
					'packnum' => $num,
					'uid' => $uid,
					'gid' => $gid ,
					'pgid' => $pgid ,
					'stdateline' => $stime,
					'dateline' => $dateline,
					'trackid' => $trackId,
					'payed' => $amount,
					'stauts' => "1",
					);
                if ($db->table_exists("bank_pey") && $bank != 0) {
                    $query7 = $db->simple_select("bank_pey", "*", "`uid` = '$uid'");
                    $bankadd = $db->fetch_array($query7);
                    $bank_traction = array(
						'uid' => $uid,
						'tid' => 0,
						'pid' => 0,
						'pey' => $bank ,
						'type' => '<img src="'.$mybb->settings['bburl'].'/images/inc.gif">',
						'username' => "مدیریت",
						'time' => $stime,
						'info' => "خرید از درگاه زیبال",
					);
    
                    if (!$bankadd) {
                        $add_money = array(
							'uid' => $uid,
							'username' => $usname[$uid],
							'pey' => $bank ,
						);
                        $db->insert_query("bank_pey", $add_money);
                        $db->insert_query("bank_buy", $bank_traction);
                    }
                    if ($bankadd) {
                        $pey = $bankadd['pey'];
                        $type='<img src="'.$mybb->settings['bburl'].'/images/inc.gif">';
                        $db->query("update ".TABLE_PREFIX."bank_pey set pey=$pey+$bank where uid=$uid");
                        $db->insert_query("bank_buy", $bank_traction);
                    }
                } else {
                    $bank = "0";
                }
                $db->insert_query("myzibal_tractions", $add_traction);
                $db->update_query("users", array("usergroup" => $gid), "`uid` = '$uid'");
                $expdate = my_date($mybb->settings['dateformat'], $dateline).", ".my_date($mybb->settings['timeformat'], $dateline);
                $profile_link = "[url={$mybb->settings['bburl']}/member.php?action=profile&uid={$uid}]{$usname[$uid]}[/url]";
                $profile_link1 = build_profile_link($usname[$uid], $uid, "_blank");
                $info = preg_replace(
                    array(
                '#{username}#',
                '#{group}#',
                '#{refNumber}#',
                '#{expdate}#',
                '#{bank}#',
                
            ),
                    array(
                $profile_link1,
                $groups[$gid],
                $refNumber,
                $expdate,
                $bank,
                
            ),
                    $mybb->settings['myzibal_note']
                );
                $username = $mybb->user['username'];
                // Notice User By PM
                require_once MYBB_ROOT."inc/datahandlers/pm.php";
                $pmhandler = new PMDataHandler();
                $from_id = intval($mybb->settings['myzibal_uid']);
                $recipients_bcc = array();
                $recipients_to = array(intval($uid));
                $subject = "گزارش پرداخت";
                $message = preg_replace(
                    array(
                '#{username}#',
                '#{group}#',
                '#{refNumber}#',
                '#{expdate}#',
                '#{bank}#',
                
            ),
                    array(
                $profile_link,
                $groups[$gid],
                $refNumber,
                $expdate,
                $bank,
                
            ),
                    $mybb->settings['myzibal_pm']
                );
                $pm = array(
            'subject' => $subject,
            'message' => $message,
            'icon' => -1,
            'fromid' => $from_id,
            'toid' => $recipients_to,
            'bccid' => $recipients_bcc,
            'do' => '',
            'pmid' => ''
        );
        
                $pm['options'] = array(
            "signature" => 1,
            "disablesmilies" => 0,
            "savecopy" => 1,
            "readreceipt" => 1
        );
    
                $pm['saveasdraft'] = 0;
                $pmhandler->admin_override = true;
                $pmhandler->set_data($pm);
                if ($pmhandler->validate_pm()) {
                    $pmhandler->insert_pm();
                }

                // Notice Admin By PM
                require_once MYBB_ROOT."inc/datahandlers/pm.php";
                $pmhandler = new PMDataHandler();
                $uidp=$mybb->settings['myzibal_uid'];
                $from_id = intval($mybb->settings['myzibal_uid']);
                $recipients_bcc = array();
                $recipients_to = array(intval($uidp));
                $subject = "عضویت کاربر در گروه ویژه";
                $message = preg_replace(
                    array(
                '#{username}#',
                '#{group}#',
                '#{refNumber}#',
                '#{expdate}#',
                '#{bank}#',
                
            ),
                    array(
                $profile_link,
                $groups[$gid],
                $refNumber,
                $expdate,
                $bank,
                
            ),
                    "کاربر [B]{username}[/B] با شماره تراکنش [B]{refNumber}[/B] در گروه [B]{group}[/B] عضو شد.
			تاریخ پایان عضویت:[B]{expdate}[/B]"
                );
                $pm = array(
            'subject' => $subject,
            'message' => $message,
            'icon' => -1,
            'fromid' => $from_id,
            'toid' => $recipients_to,
            'bccid' => $recipients_bcc,
            'do' => '',
            'pmid' => ''
        );
        
                $pm['options'] = array(
            "signature" => 1,
            "disablesmilies" => 0,
            "savecopy" => 1,
            "readreceipt" => 1
        );
    
                $pm['saveasdraft'] = 0;
                $pmhandler->admin_override = true;
                $pmhandler->set_data($pm);
        
                if ($pmhandler->validate_pm()) {
                    $pmhandler->insert_pm();
                }
            }
        }
        eval("\$verfypage = \"".$templates->get('myzibal_payinfo')."\";");
        output_page($verfypage);
    }

?>	
