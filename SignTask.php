<?php
require_once 'SubmitForm.php';
require_once 'ToolsHelper.php';
require_once 'SimulationLogin.php';
function getSignTasks($user, $apis){
    echo"<br>第一次请求获取cookie<br>";
    $params = ['login_url'=> $apis['login-url'], 'needcaptcha_url'=> '',
        'captcha_url'=> '', 'username'=> $user['username'], 'password'=> $user['password']];
    $cookie = json_decode(SendRequest($apis['login-api'], [], $params), true);//从子墨服务器获取cookie
    //$cookie = StartLogin();//从本地获取cookie
    $headers = Headers($cookie['cookies']);//获取请求头部
    print_r($cookie);
    echo"<br>第二次请求获取签到任务<br>";
    $datas = json_decode(SendRequest($apis['datas-url'], $headers, json_encode($params)), true)['datas']['unSignedTasks'];//获取任务
    print_r($datas);
    if(isset($datas[0])){
        $params = ['signInstanceWid'=> $datas[0]['signInstanceWid'],'signWid'=> $datas[0]['signWid']];
        echo"<br>当前任务<br>";
        $res = json_decode(SendRequest($apis['task-url'], $headers, json_encode($params)), true)['datas'];
        //print_r($res);
        $form = SignForm($res['signInstanceWid'], $user['lat'], $user['lon']);
        echo"<br>填充表单<br>";
        if($res['isNeedExtra'] == 1)$form['extraFieldItems'] = FillTaskKey($res['extraField'], $form['extraFieldItems']);
        print_r($form);
        SubmitTask($apis['submit-url'], $cookie['cookies'], $form, '签到');//提交表单信息
    }else {
        $title = '当前没有签到任务。';
        if($cookie['msg'] != 'SUCCESS') $title = '模拟登录API超时或云端被禁用，错误代码：' . $cookie['msg'];
        print_r(SendNotice($title, date('Y-m-d H:i:s'), 'ServerChan'));   //Qmsg酱推送
    }
}
//填写任务答案
function FillTaskKey($fillarr, $keyarr, $flag = 0, $correctkey = []){
    foreach ($fillarr as $fillkey => $fillvalue) {//遍历附加项项答卷结构
        foreach ($fillvalue['extraFieldItems'] as $itemkey => $itemvalue){//遍历选项
            if ($itemvalue['content'] == $keyarr[$flag]) {//填充答案
                $correctkey[] = ['extraFieldItemValue'=> $keyarr[$flag], 'extraFieldItemWid'=> $itemvalue['wid']];//填充fieldItems数组
                $flag++;
                break;
            }
        }
    }
    return $correctkey;
}
?>
