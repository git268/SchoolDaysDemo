<?php
require_once  'Config.php';
require_once 'SubmitForm.php';
require_once 'ToolsHelper.php';
require_once 'SimulationLogin.php';
function getSignTasks($user){
    $apis = SignAPIS();//获取对应API
    echo"<br>第一次请求获取cookie<br>";
    $params = ['login_url'=> $apis['login-url'], 'needcaptcha_url'=> '',
        'captcha_url'=> '', 'username'=> $user['username'], 'password'=> $user['password']];
    $cookie = json_decode(SendRequest($apis['login-api'], [], $params), true);//从子墨服务器获取cookie
    //$cookie = StartLogin();//从本地获取cookie
    $headers = Headers($cookie['cookies']);//获取请求头部
    print_r($cookie);
    echo"<br>第二次请求获取签到任务<br>";
    $datas = json_decode(SendRequest($apis['datas-url'], $headers, json_encode($params)), true);//获取任务
    //print_r($datas);
    $latestTask = $datas ['datas']['unSignedTasks'][0];
    $params = ['signInstanceWid'=>$latestTask['signInstanceWid'],'signWid'=>$latestTask['signWid']];
    echo"<br>当前任务<br>";
    $res = json_decode(SendRequest($apis['task-url'], $headers, json_encode($params)), true)['datas'];
    //print_r($res);
    if(!isset($res['signInstanceWid']) || empty($res['signInstanceWid'])){//判断有无任务
        $title = '当前没有签到任务';
        if(empty($cookie))$title = '模拟登录API超时或云端被禁用，错误代码：'.$cookie['msg'];
        print_r(SendNotice($title, date('Y-m-d H:i:s'), 'ServerChan'));   //Qmsg酱推送
    }else{
        $form = SignForm();
        echo"<br>填充表单<br>";
        $form['signInstanceWid'] = $res['signInstanceWid'];
        if($res['isNeedExtra'] == 1){//填充附加信息
            $form['extraFieldItems'] = FillTaskKey($res['extraField'], $form['extraFieldItems']);
        }
        print_r($form);
        SubmitTask($apis['submit-url'], $cookie['cookies'], $form, '签到');//提交表单信息
    }
}
//填写任务答案
function FillTaskKey($fillarr, $keyarr){//查找额外项wid并返回ID数组
    $k1 = 0;
    $correctkey = [];//定义答案数组
    foreach ($fillarr as $fillkey => $fillvalue) {
        foreach ($fillvalue['extraFieldItems'] as $itemkey => $itemvalue){
            if ($itemvalue['content'] == $keyarr[$k1]) {
                $correctkey[] = ['extraFieldItemValue'=> $keyarr[$k1], 'extraFieldItemWid'=> $itemvalue['wid']];//填充fieldItems数组
                $k1++;
                break;
            }
        }
    }
    return $correctkey;
}
?>
