<?php
require_once 'Config.php';
require_once 'ToolsHelper.php';
function SubmitTask($url, $form, $user, $type, $headers = ''){
    switch($type){
        case '1'://信息收集
            $extension = [  'model'=> 'OPPO R11 Plus', 'appVersion'=> '8.2.14', 'systemVersion'=> '7.0.1',
                'userId'=> $user['username'], 'systemName'=> 'android', 'lon'=> $user['lon'],
                'lat'=> $user['lat'], 'deviceId'=> UUID()   ];
            $headers = Headers2(DESEncrypt(json_encode($extension)));//信息收集提交请求头
            $headers[] = 'Host:'.$_POST['school']['host'];//添加请求头信息
            break;
        case '2'://签到
            $extension = [  'appVersion' => '8.2.14', 'systemName' => 'android',  'model' => 'OPPO R11 Plus',
                'lon' => $user['lon'], 'systemVersion' => '7.0.1', 'deviceId' => UUID(), 'lat' => $user['lat']  ];
            $headers = Headers2(DESEncrypt(json_encode($extension)));  //签到提交请求头
            break;
        case '3'://辅导员通知&查寝
            $extension = [  'lon' => $user['lon'], 'model' => 'OPPO R11 Plus','appVersion' => '8.2.14',
                'systemVersion' => '7.0.1', 'userId'=> $user['username'],'systemName' => 'android',
                'lat' => $user['lat'],'deviceId' => UUID() ];
            $headers = Headers2(DESEncrypt(json_encode($extension)));  //签到提交请求头
            $headers[] = 'Host:'.$_POST['school']['host'];//添加请求头信息
            break;
    }
    echo"<br>答卷结果<br>";
    //file_put_contents('savefile/'.$user['username'].'form.txt', json_encode($form, JSON_UNESCAPED_UNICODE));//保存答卷到本地，请勿在云函数使用！
    if (empty($_POST['tips']))$res = json_decode(SendRequest($url, $headers, json_encode($form)), true);//返回提交状态
    if (isset($res) && $res['message'] != 'SUCCESS') $_POST['tips'] = '答卷提交失败，原因是：'.$res['message'];
}
//签到&查寝任务答卷
function SignForm($wid, $user){
    $form = [   'signPhotoUrl'=> '',//图片路径
        'extraFieldItems'=> ['599.88KG'],
        'signInstanceWid'=> $wid, 'longitude'=> $user['lon'], 'latitude'=> $user['lat'], 'isMalposition'=> '0',
        'abnormalReason'=> $user['abnormalReason'], 'position'=> $user['address'], 'uaIsCpadaily'=> true ];
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
//系统随机数，任务唯一标识
function UUID($prefix=""){
    $chars = md5(uniqid(mt_rand(), true));
    $uuid = substr ($chars, 0, 8) . '-' . substr ($chars, 8, 4) . '-' .
        substr ($chars, 12, 4) . '-' . substr ($chars, 16, 4) . '-' . substr ($chars, 20, 12);
    return $prefix.$uuid ;
}
?>
