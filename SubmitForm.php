<?php
require_once 'Config.php';
require_once 'ToolsHelper.php';
function SubmitTask($url, $cookie, $form, $type){
    $headers = '';
    if($type == '信息收集'){
        $headers = Headers2($cookie, RequestExtension('信息收集'));//信息收集表头
        $headers[] = 'Host:'.$_POST['school']['host'];
    }else if($type == '签到'){
        $headers = Headers2($cookie, RequestExtension());  //签到表头
    }
    echo"<br>答卷结果<br>";
    $message = json_decode(SendRequest($url, $headers, json_encode($form)), true);
    $title = '答卷提交成功!';
    if($message['message'] != 'SUCCESS') $title = '答卷提交失败，原因是：'.$message['message'];
    print_r(SendNotice($title, date('Y-m-d H:i:s'), 'Qmsg'));   //Qmsg酱推送
}
//签到任务答卷
function SignForm($wid, $lat, $lon){
    $user = User();
    $form = [   'signPhotoUrl'=> null,  //暂不支持图片上传
                'extraFieldItems'=> [  '10kg以下',
                    '周八',
                    '否'],
        'signInstanceWid'=> $wid, 'longitude'=> $lon, 'latitude'=> $lat, 'isMalposition'=> '0',
        'abnormalReason'=> $user['abnormalReason'], 'position'=> $user['address'], 'uaIsCpadaily'=> true];
    return $form;
}
//信息收集答卷
function CollectForm($formWid, $collectWid, $schoolTaskWid, $lat, $lon){
    $data = [
        'formWid'=> $formWid, 'address'=> User()['address'],
        'collectWid'=> $collectWid, 'schoolTaskWid'=> $schoolTaskWid,
        'form'=> [
            'xx省/xx市/xx区',
            'xx省xx市xx区xx路xx号',
            '2077-01-01/12:00',
            ['公交'],
            ['早餐', '午餐', '晚餐'],
            "../images/CSGO.png",
            ['否']   ],
        'uaIsCpadaily'=> true, 'latitude'=> $lat, 'longitude'=> $lon];
    return $data;
}
//Extension参数
function RequestExtension($type = '签到'){
    $user = User();
    if($type == '信息收集'){
        $extension = [  'model'=> 'OPPO R11 Plus', 'appVersion'=> '8.2.14', 'systemVersion'=> '7.0.1',
            'userId'=> $user['username'], 'systemName'=> 'android', 'lon'=> $user['lon'],
            'lat'=> $user['lat'], 'deviceId'=> UUID()   ];
    }else{//两者加密后密文因键值顺序而并不相同，请勿更改
        $extension = [  'appVersion' => '8.2.14', 'systemName' => 'android',  'model' => 'OPPO R11 Plus',
            'lon' => $user['lon'], 'systemVersion' => '7.0.1', 'deviceId' => UUID(),
            'lat' => $user['lat']  ];
    }
    return DESEncrypt(json_encode($extension));//DES加密Extension参数
}
//系统随机数，任务唯一标识
function UUID($prefix=""){
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr ($chars, 0, 8) . '-'
        . substr ($chars, 8, 4) . '-'
        . substr ($chars, 12, 4) . '-'
        . substr ($chars, 16, 4) . '-'
        . substr ($chars, 20, 12);
    return $prefix.$uuid ;
}
?>
