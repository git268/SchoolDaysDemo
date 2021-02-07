<?php
require_once  'Config.php';
require_once 'SignTask.php';
require_once 'CollectMessage.php';
date_default_timezone_set('PRC');//设置时间
set_time_limit(100);//设置执行时间上限(100秒)
function main_handler(){
    $user = User();
    $url = SchoolMessageURL();
    $schoollist = json_decode(file_get_contents($url['list']), true)['data'];
    $schoolname = $user['school'];
    $result = FindSchoolId($schoollist, $schoolname);
    if($result != '找不到学校，请检查配置有无正确填写！'){
        $msg = json_decode(file_get_contents($url['info'].'?ids='.$result), true);
        FindAllUrl($msg['data'][0]);
        //ToolsKey();
        //getSignTasks($user, SignAPIS());//执行签到
        //getCollectTasks($user, CollectAPIS());//执行信息收集
    }else{
        echo $result;
    }
    $_POST = [];//清空超全局变量
    echo '<br>执行完毕!';
}
//查找是否支持该学校
function FindSchoolId ($schoollist, $schoolname){//查找键值
    foreach($schoollist as $key => $value){
        if($value['name'] == $schoolname)return $value['id'];
    }
    return '找不到学校，请检查配置有无正确填写！';
}
//查找学校URL链接
function FindAllUrl($msg){
    if ($msg['ampUrl'] == null) {
        $msg['ampUrl'] = $msg['ampUrl2'];
    }
    $arr = explode('/', $msg['ampUrl']);
    $_POST['school'] = [   'idsUrl' => $msg['idsUrl'],
                            'scheme' => substr($arr[0], 0, strlen($arr[0])-1),
                            'host' => $arr[2]];
    echo "查找学校链接<br>";
    print_r($_POST['school']);
}
//主函数，若部署在本地请去掉注释
//main_handler();
?>
