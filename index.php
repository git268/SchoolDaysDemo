<?php
require_once 'Config.php';
require_once 'SignTask.php';//签到
require_once 'MessageCollect.php';//信息收集
require_once 'QueryNotice.php';//辅导员通知
require_once 'CheckChamber.php';//查寝
require_once 'SimulationLogin.php';//模拟登录
date_default_timezone_set('PRC');//设置北京时间
set_time_limit(150);//设置执行时间上限(150秒)
function main_handler(){
    $serviceapi = 'http://www.zimo.wiki:8080/wisedu-unified-login-api-v1.0/api/login';//外置API
    $user = User();
    $url = SchoolMessageURL();
    FindSchoolUrl($url['list'], $user['school'], $url['info']);
    if(isset($_POST['school'])){
        Getcookie($user['username'], $user['password'], $serviceapi);//外置API模拟登陆获取cookie
        //Getcookie($user['username'], $user['password']);//本地模拟登录获取cookie
        /*
        getSignTasks($user, SignAPIS());   //签到
        getCollectTasks($user, CollectAPIS()); //信息收集
        getQueryTasks($user, ConfirmAPIS());//辅导员通知
        getCheckChamber($user, AttendanceAPIS());//查寝
        if(empty($_POST['tips']))$_POST['tips'] = '答卷提交成功！';
        print_r(SendNotice([$_POST['tips'], date('Y-m-d H:i:s')], $user['notice']));
        */
    }else{
        $_POST['tips'] = '找不到学校，请检查配置有无正确填写！';
    }
    echo '<br>填写状态：'.$_POST['tips'];
}
//查找是否支持该学校
function FindSchoolUrl ($list, $name, $info){//查找键值
    $list = json_decode(file_get_contents($list), true)['data'];//获取学校列表
    foreach($list as $key => $value){
        if($value['name'] == $name){//查找并填充学校URL链接
            $msg = json_decode(file_get_contents($info.'?ids='.$value['id']), true)['data'][0];
            if (!(is_numeric(strpos($msg['ampUrl'], 'https')))) {//查找正确的url
                $msg['ampUrl'] = $msg['ampUrl2'];
            }
            $arr = explode('/', $msg['ampUrl']);
            $_POST['school'] = [   'idsUrl' => $msg['idsUrl'], 'host' => $arr[2]   ];
            echo '查找学校链接<br>';
            print_r($_POST['school']);
            break;
        }
    }
}
//主函数，若部署在本地请去掉注释
//main_handler();
?>
