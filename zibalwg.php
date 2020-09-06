<?php

/* Zibal Payment Gateway Plugib for MyBB Ver:1.0
Author : Yahya Kangi 
*/
	
	define("IN_MYBB", "1");
	require("./global.php");	
	global $mybb;
	$ui = $mybb->user['uid'];
	$ug = $mybb->user['usergroup'];
	
	if (!$mybb->user['uid'])
	{
	error_no_permission();
	}
	$ban = explode(",",$mybb->settings['myzibal_ban']) ;
	if(in_array($ui,$ban))
	{
	error_no_permission();
	}
	$bang = explode(",",$mybb->settings['myzibal_bang']) ;
	if(in_array($ug,$bang))
	{
	error_no_permission();
	}
	
$query = $db->simple_select('usergroups', 'title, gid', '', array('order_by' => 'gid', 'order_dir' => 'asc'));
while($group = $db->fetch_array($query, 'title, gid'))
{
	$groups[$group['gid']] = $group['title'];
}


$query = $db->simple_select('myzibal', '*', '', array('order_by' => 'price', 'order_dir' => 'ASC'));
while ($myzibal = $db->fetch_array($query))
{
	$bgcolor = alt_trow();
	$myzibal['num'] = intval($myzibal['num']);
	$myzibal['title'] = htmlspecialchars_uni($myzibal['title']);
	$t= " تومان ";
	$myzibal['price'] = floatval($myzibal['price'])."$t";
	$myzibal['usergroup'] = $groups[$myzibal['group']];

	if($myzibal['time']== 1)
	{
	$time= "روز";
}	
	if($myzibal['time']== 2)
	{
	$time= "هفته";
}	
	if($myzibal['time']== 3)
	{
	$time= "ماه";
}	
	if($myzibal['time']== 4)
	{
	$time= "سال";
}	

	$period = intval($myzibal['period']);
	$myzibal['period'] = intval($myzibal['period'])." ".$time;
	$uid = $mybb->user['uid'];
$query5 = $db->query("SELECT * FROM ".TABLE_PREFIX."myzibal_tractions WHERE uid=$uid AND stauts = 1");
$check5 = $db->fetch_array($query5);
if ($check5)
{
$note = "<div class=\"red_alert\">به دلیل اینکه شما قبلاً یکی از این بسته ها را خریداری کرده اید و زمان عضویت شما به پایان نرسیده است ، نمی توانید  بسته ی جدیدی را خریداری نمایید </div>";
$buybutton = "
					<input type='image' src='{$mybb->settings['bburl']}/images/buy-pack.png' border='0'  name='submit'alt='خرید بسته {$myzibal['title']}' />";

}
else{
$buybutton = " 							<form action='{$mybb->settings['bburl']}/zibal1wg.php' method='post'>
<input type='hidden' name='myzibal_num' value='{$myzibal['num']}' /> 
					<input type='image' src='{$mybb->settings['bburl']}/images/buy-pack.png' border='0'  name='submit'alt='خرید بسته {$myzibal['title']}' />

					</form>
";
	
}	
	eval("\$list .= \"".$templates->get('myzibal_list_table')."\";");
}

if (!$list)
{
	eval("\$list = \"".$templates->get('myzibal_no_list')."\";");
}

eval("\$myzibalpage = \"".$templates->get('myzibal_list')."\";");
output_page($myzibalpage);
?>