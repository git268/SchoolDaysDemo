<?php
require_once  'SubmitForm.php';
require_once 'ToolsHelper.php';
require_once 'SimulationLogin.php';
function getCollectTasks($user, $apis) {
    echo "<br>第一次请求获取cookie<br>";
    $params = [ 'login_url'=> $apis['login-url'], 'needcaptcha_url'=> '', 'captcha_url'=> '',
                'username'=> $user['username'], 'password'=> $user['password']    ];
    $cookie = json_decode(SendRequest($apis['login-api'], [], $params), true);//从子墨服务器获取cookie
    //$cookie = StartLogin();//从本地获取cookie
    $headers = Headers($cookie['cookies']);//获取请求头部
    print_r($cookie);
    echo "<br>第二次请求获取信息收集任务<br>";
    $datas = json_decode(SendRequest($apis['datas-url'], $headers, json_encode(['pageSize' => 6, 'pageNumber' => 1])), true)['datas']['rows'];//获取任务
    print_r($datas);
    if (isset($datas[0])){//判断有无任务
        $collectWid = $datas[0]['wid'];
        $formWid = $datas[0]['formWid'];
        $schoolTaskWid = json_decode(SendRequest($apis['task-url'], $headers, json_encode(['collectorWid' => $collectWid])), true)['datas'];
        echo "<br>第三次请求具体信息收集任务<br>";
        $params = json_encode(['pageSize' => 100, 'pageNumber' => 1, 'formWid' => $formWid, 'collectorWid' => $collectWid]);
        $res = json_decode(SendRequest($apis['form-url'], $headers, $params), true);//获取信息收集详情
        //print_r($res);
        echo "<br>填充表单<br>";
        $form = CollectForm($formWid, $collectWid, $schoolTaskWid['collector']['schoolTaskWid'], $user);
        $form['form'] = FillForm($res['datas']['rows'], $form['form']);
        print_r($form);
        SubmitTask($apis['submit-url'], $cookie['cookies'], $form, $user, '信息收集');//提交表单
    }else{
        $title = '当前没有信息收集任务。';
        if ($cookie['msg'] != 'SUCCESS')$title = '模拟登录API超时或云端被禁用，错误代码：'.$cookie['msg'];
        print_r(SendNotice($title, date('Y-m-d H:i:s'), 'Qmsg'));   //Qmsg酱推送
    }
}
//填充必填项表单答案
function FillForm($fillarr, $keyarr){
    if ($fillarr == null || $keyarr == null) return [];
    $k1 = 0;//任务表单必填项下标
    foreach ($fillarr as $keyname => $keyvalue){
        if ($keyvalue['isRequired'] == 1) {//必填项
            if ($keyvalue['fieldType']==2 || $keyvalue['fieldType']==3) {//填充单选/多选
                $correctkey = DelSurplusOption($fillarr[$keyname]['fieldItems'], $keyarr[$k1]);//删除多余选项
                $fillarr[$keyname]['fieldItems'] = [];//清空选项
                for ($k2 = 0; $k2 < count($correctkey); $k2++) {
                    $fillarr[$keyname]['value'] .= $keyarr[$k1][$k2] . ' ';//填充答案
                    $fillarr[$keyname]['fieldItems'][] = $correctkey[$k2];//覆盖fieldItems数组
                }
            }else{//填充文本、图片。图片功能并未作测试
                $fillarr[$keyname]['value'] = $keyarr[$k1];
            }
            $k1++;//记录必填项
        }else{
            unset($fillarr[$keyname]);//删除非必填项
        }
    }
    sort($fillarr);//序列化数组
    return $fillarr;
}
//选择题删除多余项
function DelSurplusOption($option, $keyarr, $flag = 0, $correctkey = []){
    foreach ($option as $optkey => $optvalue) {
        if ($optvalue['content'] == $keyarr[$flag]) {
            $correctkey[] = $optvalue;//填充fieldItems数组
            if (count($keyarr) > 1) $flag++;//多选
        }
    }
    return $correctkey;
}
?>
