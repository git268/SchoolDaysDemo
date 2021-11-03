<?php
require_once 'Config.php';
require_once 'ToolsHelper.php';
function SubmitTask($url, $form, $user, $type, $headers = ''){
    switch($type){
        case '1'://信息收集
            $extension = [  'model'=> MOBILETYPE, 'appVersion'=> APPVERSION, 'systemVersion'=> '11.0.2',
                'userId'=> $user['username'], 'systemName'=> 'android', 'lon'=> $user['lon'],
                'lat'=> $user['lat'], 'deviceId'=> UUID()   ];
            ksort($extension,SORT_STRING);
            $headers = SubmitHeader(DESEncrypt(json_encode($extension)));//信息收集提交请求头
            $headers[] = 'Host:'.$_POST['school']['host'];//添加请求头信息
            break;
        case '2'://签到
            $extension = [  'appVersion' => APPVERSION, 'systemName' => 'android',  'model' => MOBILETYPE,
                'lon' => $user['lon'], 'systemVersion' => '11.0.2', 'deviceId' => UUID(), 'lat' => $user['lat']  ];
            ksort($extension,SORT_STRING);
            $headers = SubmitHeader(DESEncrypt(json_encode($extension)));  //签到提交请求头
            break;
        case '3'://辅导员通知&查寝
            $extension = [  'lon' => $user['lon'], 'model' => MOBILETYPE,'appVersion' => APPVERSION,
                'systemVersion' => '11.0.2', 'userId'=> $user['username'],'systemName' => 'android',
                'lat' => $user['lat'],'deviceId' => UUID() ];
            ksort($extension,SORT_STRING);
            $headers = SubmitHeader(DESEncrypt(json_encode($extension)));  //签到提交请求头
            $headers[] = 'Host:'.$_POST['school']['host'];//添加请求头信息
            break;
    }
    //file_put_contents('../SaveFile/'.$user['username'].'form.txt', json_encode($form, JSON_UNESCAPED_UNICODE));//保存答卷到本地，请勿在云函数使用！
    $aesver = true;
    $data4sign = array(); //给sign加密用的数据
    $data4bs = $form; //给bodyString加密用的数据
    if($aesver){
        //BODYSTRING
        $data4bs['longitude'] = $user['lon'];
        $data4bs['latitude'] = $user['lat'];
        $data4bs['isMalposition'] = '0';
        $data4bs['abnormalReason'] = $user['abnormalReason'];
        $data4bs['position'] = $user['address'];
        $data4bs['uaIsCpadaily'] = true;
        $bodyString = AESEncrypt2(json_encode($data4bs),AESKEY);
        //SIGN
        $data4sign['appVersion'] = APPVERSION;
        $data4sign['bodyString'] = $bodyString;
        $data4sign['deviceId'] = $extension['deviceId'];
        $data4sign['lat'] = $user['lat'];
        $data4sign['lon'] = $user['lon'];
        $data4sign['model'] = MOBILETYPE;
        $data4sign['systemName'] = 'android';
        $data4sign['systemVersion'] = '11.0.2';
        $data4sign['userId'] = $user['username'];
        ksort($data4sign,SORT_STRING);
        $sign_tmp = http_build_query(($data4sign)).'&'.AESKEY;
        //print_r($sign_tmp);
        $sign = md5($sign_tmp);
        //FINAL RESULT
        $forSubmit = $extension;
        $forSubmit['calVersion'] = 'firstv';
        $forSubmit['version'] = 'first_v2';
        $forSubmit['sign'] = $sign;
        $forSubmit['bodyString'] = $bodyString;
        ksort($forSubmit,SORT_STRING);
    }

    if (empty($_POST['tips']))
        $res = json_decode(SendRequest($url, $headers, json_encode($forSubmit)), true);//返回提交状态
        //print_r($res);
        //die();
    if (isset($res) && $res['message'] != 'SUCCESS') $_POST['tips'] = '答卷提交失败，原因是：'.$res['message'];
}
//签到&查寝任务答卷
function SignForm($wid, $user){
    $form = [   'signPhotoUrl'=> $user['photo'],//图片路径
        'extraFieldItems'=> [],
        'signInstanceWid'=> $wid, 'longitude'=> $user['lon'], 'latitude'=> $user['lat'], 'isMalposition'=> '0',
        'abnormalReason'=> $user['abnormalReason'], 'position'=> $user['address'], 'uaIsCpadaily'=> true, 'sign'=> ''];
    return $form;
}
//信息收集答卷
function CollectForm($fwid, $cwid, $swid, $user){//三个必填wid+地址经纬度
    $data = [
        'formWid'=> $fwid, 'address'=> $user['address'],
        'collectWid'=> $cwid, 'schoolTaskWid'=> $swid,
        'form'=> [
            'xx省/xx市/xx区',
            'xx省xx市xx区xx路xx号',
            '2077-01-01/12:00',
            ['公交'],
            ['早餐', '午餐', '晚餐'],
            ['否']   ],
        'uaIsCpadaily'=> true, 'latitude'=> $user['lat'], 'longitude'=> $user['lon'] ];
    return $data;
}
//DES加密
function DESEncrypt($text, $key = DESKEY){
    $iv = "\x01\x02\x03\x04\x05\x06\x07\x08";//初始向量
    $pad = 8 - (strlen($text) % 8);
    $text =$text . str_repeat(chr($pad), $pad);//PKCS5填充
    $res = openssl_encrypt($text, 'DES-CBC', $key, OPENSSL_NO_PADDING, $iv);//加密
    return base64_encode($res);//base64编码
}
//AES加密
function AESEncrypt($text, $key=AESKEY){
    $iv = '0000000000000000';//初始向量
    $text = bin2hex(random_bytes(32)).$text;//加密明文前需要64位随机字符串
    $pad = 16 - (strlen($text) % 16);
    $text = $text . str_repeat(chr($pad), $pad);//PKCS5填充
    $res = openssl_encrypt($text, 'AES-128-CBC', $key, OPENSSL_NO_PADDING, $iv);//加密
    return base64_encode($res);//base64编码
}
//AES加密2(表单用)
function AESEncrypt2($text, $key=AESKEY){
    $iv = "\x01\x02\x03\x04\x05\x06\x07\x08\x09\x01\x02\x03\x04\x05\x06\x07";//初始向量
    //$text = bin2hex(random_bytes(32)).$text;//加密明文前需要64位随机字符串
    //$pad = 16 - (strlen($text) % 16);
    //$text = $text . str_repeat(chr($pad), $pad);//PKCS5填充
    $res = openssl_encrypt($text, 'AES-128-CBC', $key,OPENSSL_RAW_DATA, $iv);//加密
    return base64_encode($res);//base64编码
}
//系统随机数，任务唯一标识
function UUID($prefix=""){
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr ($chars, 0, 8) . '-' . substr ($chars, 8, 4) . '-' .
        substr ($chars, 12, 4) . '-' . substr ($chars, 16, 4) . '-' . substr ($chars, 20, 12);
    return $prefix.$uuid ;
}
?>