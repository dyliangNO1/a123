<?php
header("content-type:text/html;charset=utf-8");
function is_level(){
	$url=CONTROLLER_NAME.'/'.ACTION_NAME;
	$map = array('url'=>$url);
	$row = M('menu')->where($map)->find();
	if(!in_array($row['id'], $_SESSION['level_ids'])){
		redirect(U('/Admin/user/login'), 1, '用户没有权限操作...');
	}
}



	$myinforow=M('voucher')->where(array('v_id'=>$id))->find();
	return date('Y-m-d',$myinforow['create']);
	}

function is_login(){

	if(!$_SESSION['name']){
		redirect(U('User/login'), 2, '用户没有登录，请登陆...');
	}
}



function shareSign($url){
	$wechat=M('Wechat')->where(array('b_id'=>$_SESSION['bank_id']))->find();
    $jssdk = new \Common\Common\jssdk($wechat['appid'],$wechat['appsecret'],$url);
    $signPackage = $jssdk->GetSignPackage();
    return $signPackage;
}

function creatShareJson($sign,$timeLine,$appMessage){
    $val = $sign;
    $val['timeLine'] = $timeLine;
    $val['appMessage'] = $appMessage;
    return json_encode($val);
}

/*

：author：Treasure
时间是否已到期

*/
function overdue($time){
	//echo $time;
	 $overtime=time()-3600*24;
	if($time<$overtime){
		$data='已过期';
		}else{

		$data='未过期';
	}
return $data;
}

function interests($id){
	$row=M('shop')->where(array('s_id'=>$id))->find();

	 $overtime=time()-3600*24;
	if($row['create']<$overtime){
		$data=false;
		}else{

		$data=true;
	}
	return $data;
	}



function whether($sex){
	//echo $sex;exit;
	if($sex==1){
	$xinbie='是';
	}else{ $xinbie='否';}
return $xinbie;
}


/*信用卡类型*/
function banktype($type){
	if($type=='1'){
		$type='储蓄卡';
		}else{

		$type='贷记卡（信用卡）';
			}

	return $type;
	}



function anngle($sex){
	//echo $sex;exit;
	if($sex==1){
	$xinbie='男';
	}
	if($sex==2){
	$xinbie='女';
	}
	if(!$sex){
	$xinbie='';
	}
	return $xinbie;
}


function get_username($uid = 0){
    static $list;
    if(!($uid && is_numeric($uid))){ //获取当前登录用户名
        return session('user_auth.username');
    }

    /* 获取缓存数据 */
    if(empty($list)){
        $list = S('sys_active_user_list');
    }

    /* 查找用户信息 */
    $key = "u{$uid}";
    if(isset($list[$key])){ //已缓存，直接使用
        $name = $list[$key];
    } else { //调用接口获取用户信息
        $User = new User\Api\UserApi();
        $info = $User->info($uid);
        if($info && isset($info[1])){
            $name = $list[$key] = $info[1];
            /* 缓存用户 */
            $count = count($list);
            $max   = C('USER_MAX_CACHE');
            while ($count-- > $max) {
                array_shift($list);
            }
            S('sys_active_user_list', $list);
        } else {
            $name = '';
        }
    }
    return $name;
}

function delta($sttime,$ettime){
$startdate=strtotime($sttime);
$enddate=strtotime($ettime);
$days=round(($enddate-$startdate)/3600/24);
if($days<=0){
	return false;
}
return $days;//days为得到的天数;
}



/**
 * 把返回的数据集转换成Tree
 * @param array $list 要转换的数据集
 * @param string $pid parent标记字段
 * @param string $level level标记字段
 * @return array
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $root = 0) {
    // 创建Tree
    $tree = array();
    if(is_array($list)) {
        // 创建基于主键的数组引用
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            // 判断是否存在parent
            $parentId =  $data[$pid];
            if ($root == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if (isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 * @author yangweijie <yangweijiester@gmail.com>
 */
function tree_to_list($tree, $child = '_child', $order='id', &$list = array()){
    if(is_array($tree)) {
        foreach ($tree as $key => $value) {
            $reffer = $value;
            if(isset($reffer[$child])){
                unset($reffer[$child]);
                tree_to_list($value[$child], $child, $order, $list);
            }
            $list[] = $reffer;
        }
        $list = list_sort_by($list, $order, $sortby='asc');
    }
    return $list;
}

/**
 * 邮件发送函数
 */
function sendmail($title, $content, $tomail){
error_reporting(E_STRICT);

date_default_timezone_set('PRC');  //设置一个时区

require_once('./Public/Uploads/phpmail/class.phpmailer.php');   // 包括一个phpmailer进来
include("./Application/Common/Common/mail.php"); // optional, gets called from within class.phpmailer.php if not already loaded


$mail             = new PHPMailer();  //实例化一个对象

$mail->CharSet = "utf8";
$body             = $content;    // file_get_contents('contents.html');//获取html内容    邮件的内容
//$body             = eregi_replace("[\]",'',$body);    //不区分大小写的正则表达式替换

$mail->IsSMTP(); // telling the class to use SMTP 使用smtp协议发送
//$mail->Host       = "smtp.126.com"; // SMTP server
//$mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                           // 1 = errors and messages
                                           // 2 = messages only
$mail->SMTPAuth   = true;                  // enable SMTP authentication
$mail->Host       = $mail_config['Host']; // sets the SMTP server     //
$mail->Port       = $mail_config['Port'];                    // set the SMTP port for the GMAIL server
$mail->Username   = $mail_config['Username']; // SMTP account username
$mail->Password   = $mail_config['Password'];        // SMTP account password

$mail->SetFrom($mail_config['Username'], $mail_config['Truename']);//设置接收来源  发件人：

$mail->AddReplyTo($mail_config['Username'], $mail_config['Truename']);//回复邮箱

$mail->Subject    = $title;//标题

//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test

$mail->MsgHTML($body);//内容使用html格式

$address = $tomail;//发送地址，相当于收件人
$mail->AddAddress($address, $truename);//有多个邮箱地址，使用多次   收件人:

//$mail->AddAttachment("images/phpmailer.gif");      // attachment 附件
//$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment 多个附件执行多次

if(!$mail->Send()) {
  return array('status'=>0, 'msg'=>'Mailer Error: ' . $mail->ErrorInfo);
} else {
  return array('status'=>1, 'msg'=>'Message sent!');
}
}


/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if(function_exists("mb_substr"))
        $slice = mb_substr($str, $start, $length, $charset);
    elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str,$start,$length,$charset);
        if(false === $slice) {
            $slice = '';
        }
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

function msmd5($ps){
	$ps=md5($ps);

	 return $ps;
	}
     /*积分函数*/
function integral($uid){

	$list=M('draw')->field('m_draw')->where(array('uid'=>$uid,'status'=>1))->select();
	//echo '<pre>';
	//print_r($list);exit;
	foreach($list as $key=>$val){

		$total=$total+$val['m_draw'];
	//echo'<br>';
		}
		if(!$total){$total=0;}
	return $total;
	}


//获取验证码
function sms_code($mobile){
	    $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
	    $mobile = $mobile;
		$send_code = random(6,1);
		$mobile_code       = rand(30000,99999);
		$data['yzm']       = $mobile_code;
		$data['phone']     = $mobile;
		$data['overdue']   = time()+1800;
		$data['create']= time();



	if(empty($mobile)){
		exit('手机号码不能为空');
	}

	if(empty($send_code)){
		//防用户恶意请求
		exit('请求超时，请刷新页面后重试');
	}

	$post_data = "account=cf_gdsm&password=shangmeng123456&mobile=".$mobile."&content=".rawurlencode("您的验证码是：".$mobile_code."。请不要把验证码泄露给其他人。如非本人操作，可不用理会！");
	//密码可以使用明文密码或使用32位MD5加密
	$gets =  xml_to_array(Post($post_data, $target));
	if($gets['SubmitResult']['code']==2){
		$_SESSION['mobile'] = $mobile;
		$_SESSION['mobile_code'] = $mobile_code;
	}
	 if($gets['SubmitResult']['msg'] == '提交成功'){
		   $re=M('coge')->add($data);
		   $arr['msg']='1';
		   }else{$arr['msg']='-1';}

	return $arr;
}





function smsange_code($mobile){
	    $target = "http://106.ihuyi.cn/webservice/sms.php?method=Submit";
	    $mobile = $mobile;
		$send_code = random(6,1);
		$mobile_code       = rand(30000,99999);
		$data['yzm']       = $mobile_code;
		$data['phone']     = $mobile;
		$data['overdue']   = time()+1800;
		$data['create']= time();



	if(empty($mobile)){
		exit('手机号码不能为空');
	}

	if(empty($send_code)){
		//防用户恶意请求
		exit('请求超时，请刷新页面后重试');
	}

	$post_data = "account=cf_gdsm&password=shangmeng123456&mobile=".$mobile."&content=".rawurlencode("您的核销码是：".$mobile_code."。请不要把核销码泄露给其他人。如非本人操作，可不用理会！");
	//密码可以使用明文密码或使用32位MD5加密
	$gets =  xml_to_array(Post($post_data, $target));
	if($gets['SubmitResult']['code']==2){
		$_SESSION['mobile'] = $mobile;
		$_SESSION['mobile_code'] = $mobile_code;
	}
	 if($gets['SubmitResult']['msg'] == '提交成功'){

		   $arr['msg']='1';
		   $arr['mobile_code']=$mobile_code;
		   }else{$arr['msg']='-1';}

	return $arr;
}








           /*短信接口*/
function Post($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
}
function xml_to_array($xml){
	$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
	if(preg_match_all($reg, $xml, $matches)){
		$count = count($matches[0]);
		for($i = 0; $i < $count; $i++){
		$subxml= $matches[2][$i];
		$key = $matches[1][$i];
			if(preg_match( $reg, $subxml )){
				$arr[$key] = xml_to_array( $subxml );
			}else{
				$arr[$key] = $subxml;
			}
		}
	}
	return $arr;
}
function random($length = 6 , $numeric = 0) {
	PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
	if($numeric) {
		$hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
	} else {
		$hash = '';
		$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789abcdefghjkmnpqrstuvwxyz';
		$max = strlen($chars) - 1;
		for($i = 0; $i < $length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
	}
	return $hash;
}

function merchant_timename($id){

	$row=M('voucher')->where(array('v_id'=>$id))->find();
	return date('Y-m-d',$row['create']);

	}

function voreher_count($id){

	$sum=M('voucheruid')->where(array('vid'=>$id))->count();

	return $sum;
	}

function order_name($teid){
	$ret=M('together')->where(array('tid'=>$teid))->find();
	return $ret['unit'];
	}

function singen($id){
	$reselt=M('gord')->where(array('g_id'=>$id))->find();
	if($reselt['gound']=='1'){
		return true;
		}else{

		return false;
	}
	}

function message_time($time){
//print_r($url);exit;
echo '<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf8">
<script type="text/javascript">


var i = '."$time-1".';
var intervalid;
intervalid = setInterval("count_down()", 1000);

function count_down()
{
if (i == 0)
{
window.location.href = url;
clearInterval(intervalid);
}
document.getElementById("show_sec_div").innerHTML = i;
i--;
}
</script>
<title>信息提示</title>
</head>
<body">
<div class="refresh" style="float:left;">

</div>
<div  style="margin-left: 0px; float: left; margin-top: 17px;">剩余<span id="show_sec_div" >'."$time".'</span>秒</div>
</body>
</html>';

}
function Debug($array){
	echo '<pre>';
	print_r($array);exit;
	}
//邮费
function popular_fell($city_id,$m_id){
	$row=M('postage')->field('postage_total')->where(array('city_id'=>$city_id,'machant_id'=>$m_id))->find();
	if($row){
		$total=$row['postage_total'];
		}else{
		$total=0;
	}
return $total;
	}
function popular_sum($orderon){
	$yinfo=M('goods_orders')->where(array('ordersn'=>$orderon))->find();
	$mylist=M('goodscat')->where(array('ordersn'=>$orderon))->select();

    foreach($mylist as $key=>$val){
	$top+=$val['goods_picre']*$val['total_sum'];
	}
	if($yinfo['order_price']-$top>0){ $tofei= $yinfo['order_price']-$top;}else{ $tofei= 0;}
	return $tofei;
	}


function attr_type($TYPE_ID){

	$mytype=M('goodstype')->where(array('type_id'=>$TYPE_ID))->find();
	return $mytype['type_name'];
	}

function type_attr($tyid,$dd,$tyname){
	$mai['good_ids']=$dd;
	$mai['attr_bute']=$tyid;
	$mai['attr_size']=$tyname;
	$attr=M('good_attr')->where($mai)->find();
	return $attr['attr_picre'];
	}

	//将用户名进行处理，中间用星号表示
	function substr_cut($user_name){  
		//获取字符串长度
		$strlen = mb_strlen($user_name, 'utf-8');
		//如果字符创长度小于2，不做任何处理
		if($strlen<2){
			return $user_name;
		}else{
			//mb_substr — 获取字符串的部分
			$firstStr = mb_substr($user_name, 0, 1, 'utf-8');
			$lastStr = mb_substr($user_name, -1, 1, 'utf-8');
			//str_repeat — 重复一个字符串
			return $strlen == 2 ? $firstStr . str_repeat('*', mb_strlen($user_name, 'utf-8') - 1) : $firstStr . str_repeat("*", $strlen - 2) . $lastStr;
		}
	}
