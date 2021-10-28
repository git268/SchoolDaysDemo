<?php
require_once 'Config.php';//配置文件
require_once 'SignTask.php';//签到
require_once 'MessageCollect.php';//信息收集
require_once 'QueryNotice.php';//辅导员通知
require_once 'CheckChamber.php';//查寝
require_once 'SimulationLogin.php';//模拟登录
date_default_timezone_set('PRC');//设置北京时间
set_time_limit(900);//设置执行时间上限(900秒)
function handler(){
    //$serviceapi = 'http://www.zimo.wiki:8080/wisedu-unified-login-api-v1.0/api/login';//外置API
    $user = User();
    $url = SchoolMessageURL();
    $ua = ['User-Agent:Mozilla/5.0 (Linux; Android 6.0.1; vivo Y66L Build/MMB29M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/51.0.2704.81 Mobile Safari/537.36  cpdaily/8.2.20 wisedu/8.2.20'];
    $list = json_decode(SendRequest($url['list'], $ua, '', 'GET'), true)['data'];
    for($i = count($user) - 1; $i >= 0; $i--){
        FindSchoolUrl($list, $user[$i]['school'], $url['info']);
        if(isset($_POST['school'])){
            //Getcookie($user['username'], $user['password'], $serviceapi);//外置API模拟登陆获取cookie
            Getcookie($user[$i]['username'], $user[$i]['password']);//本地模拟登录获取cookie
            switch ($user[$i]['mode']) {
                case 1://
                    $type = "签到";
                    getSignTasks($user[$i], SignAPIS());   //签到
                    break;
                case 2://信息收集
                    $type =  "信息收集";
                    getCollectTasks($user[$i], CollectAPIS()); //信息收集
                    break;
                case 3://辅导员通知
                    $type =  "辅导员通知";
                    getQueryTasks($user[$i], ConfirmAPIS());//辅导员通知
                    break;
                case 4://查寝
                    $type =  "查寝";
                    getCheckChamber($user[$i], AttendanceAPIS());//查寝
                    break;
                default:
                    $type =  "错误";
                    break;
            }
            echo $type."\t";
            if(empty($_POST['tips']))$_POST['tips'] = '答卷提交成功！';
            SendNotice([$_POST['tips'], $user[$i]['username']], $user[$i]['notice']);
        }else{
            $_POST['tips'] = '找不到学校，请检查配置有无正确填写！';
        }
        $tips = $user[$i]['school'].$user[$i]['username']."\t\t填写状态：".$_POST['tips'];
        echo $tips."\n";
        $_POST = [];//清空超全局变量
    }
}
//查找是否支持该学校
function FindSchoolUrl ($list, $name, $info){//查找键值
    foreach($list as $key => $value){
        if($value['name'] == $name){//查找并填充学校URL链接
            $msg = json_decode(file_get_contents($info.'?ids='.$value['id']), true)['data'][0];
            if (!(is_numeric(strpos($msg['ampUrl'], 'https')))) {//查找正确的url
                $msg['ampUrl'] = $msg['ampUrl2'];
            }
            $arr = explode('/', $msg['ampUrl']);
            $_POST['school'] = [   'idsUrl' => $msg['idsUrl'], 'host' => $arr[2]   ];
            break;
        }
    }
}
//主函数，若部署在本地请去掉注释
//handler();
?>