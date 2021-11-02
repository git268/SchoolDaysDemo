<?php
require_once 'Config.php';//配置文件
require_once '../Task/SignTask.php';//签到
require_once '../Task/MessageCollect.php';//信息收集
require_once '../Task/QueryNotice.php';//辅导员通知
require_once '../Task/CheckChamber.php';//查寝
require_once '../Login/SimulationLogin.php';//模拟登录
date_default_timezone_set('PRC');//设置北京时间
set_time_limit(900);//设置执行时间上限(900秒)
function main_handler(){
    //$serviceapi = 'http://xxx.com/wisedu-unified-login-api-v1.0/api/login';//外置API
    $time = date('Y-m-d H:i:s');//起始时间
    $user = User();
    $url = SchoolMessageURL();
    $error = [];//错误日志
    echo '开始时间 : '.$time."\t  合计".count($user)."人\n";
    $i = count($user) - 1;//获取用户组用户数量
    $rank = RandomList($i);//生成随机唯一id
    $list = json_decode(file_get_contents('../SaveFile/list.txt'), true);//本地获取list
    for(; $i >= 0; $i--){
        Timer([1, 3]);//随机延时1-2秒
        FindSchoolUrl($list, $user[$rank[$i]]['school'], $url['info']);
        if(isset($_POST['school'])){
            //Getcookie($user[$rank[$i]]['username'], $user[$rank[$i]]['password'], $serviceapi);//外置API模拟登陆获取cookie
            Getcookie($user[$rank[$i]]['username'], $user[$rank[$i]]['password']);//本地模拟登录获取cookie
            switch ($user[$rank[$i]]['mode']) {
                case 1://
                    $type = "签到";
                    getSignTasks($user[$rank[$i]], SignAPIS());   //签到
                    break;
                case 2://信息收集
                    $type =  "信息收集";
                    getCollectTasks($user[$rank[$i]], CollectAPIS()); //信息收集
                    break;
                case 3://辅导员通知
                    $type =  "辅导员通知";
                    getQueryTasks($user[$rank[$i]], ConfirmAPIS());//辅导员通知
                    break;
                case 4://查寝
                    $type =  "查寝";
                    getCheckChamber($user[$rank[$i]], AttendanceAPIS());//查寝
                    break;
                default:
                    $type =  "错误";
                    break;
            }
            echo $type."\t";
            if(empty($_POST['tips']))$_POST['tips'] = '答卷提交成功！';
                SendNotice([$_POST['tips'], $user[$rank[$i]]['username']], $user[$rank[$i]]['notice']);
        }else{
            $_POST['tips'] = '找不到学校，请检查配置有无正确填写！';
        }
        $tips = $user[$rank[$i]]['school'].$user[$rank[$i]]['username']."\t\t填写状态：".$_POST['tips'];
        if(!($_POST['tips']=='当前没有任务。' || $_POST['tips']=='答卷提交成功！'))$error[] = $tips;
        echo $tips."\n";
        $_POST = [];//清空超全局变量
    }
    $time = strtotime(date('Y-m-d H:i:s')) - strtotime($time);//结束时间
    echo '任务完成，耗时 : '.$time."秒\n";
    
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
//对用户组生成随机id
function RandomList($num){
    $count = 0;
    while($count <= $num){
        $arr[] = mt_rand(0, $num);
        $arr = array_flip(array_flip($arr));
        $count = count($arr);
    }
    shuffle($arr);
    return $arr;
}
main_handler();
?>