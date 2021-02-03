<?php
require_once  'Config.php';
require_once  'SubmitForm.php';
require_once 'ToolsHelper.php';
require_once 'SimulationLogin.php';
function getCollectTasks($user) {
    $apis = CollectAPIS();//获取对应API
    echo "<br>第一次请求获取cookie<br>";
    $params = ['login_url' => $apis['login-url'], 'needcaptcha_url' => '',
        'captcha_url' => '', 'username' => $user['username'], 'password' => $user['password']];
    $cookie =  json_decode(SendRequest($apis['login-api'], [], $params), true);//从子墨服务器获取cookie
    //$cookie = StartLogin();//从本地获取cookie
    $headers = Headers($cookie['cookies']);//获取请求头部
    print_r($cookie);
    echo "<br>第二次请求获取信息收集任务<br>";
    $datas = json_decode(SendRequest($apis['datas-url'], $headers, json_encode(['pageSize' => 6, 'pageNumber' => 1])), true);//获取任务
    //print_r($datas['datas']);
    $collectWid = $datas['datas']['rows'][0]['wid'];
    $formWid = $datas['datas']['rows'][0]['formWid'];
    $schoolTaskWid = json_decode(SendRequest($apis['task-url'], $headers, json_encode(['collectorWid' => $collectWid])), true)['datas'];
    //判断有无信息收集
    if(!isset($schoolTaskWid['collector']['schoolTaskWid']) || empty($schoolTaskWid['collector']['schoolTaskWid'])){
        $title = '当前没有签到任务';
        if(empty($cookie))$title = '模拟登录API超时或云端被禁用，错误代码：'.$cookie['msg'];
        print_r(SendNotice($title, date('Y-m-d H:i:s'), 'Qmsg'));   //Qmsg酱推送
    }else{
        echo "<br>第三次请求具体信息收集任务<br>";
        $params = json_encode(['pageSize' => 100, 'pageNumber' => 1, 'formWid' => $formWid, 'collectorWid' => $collectWid]);
        $res = json_decode(SendRequest($apis['form-url'], $headers, $params), true);//获取信息收集详情
        echo "<br>填充表单<br>";
        $form = CollectForm($formWid, $collectWid, $schoolTaskWid['collector']['schoolTaskWid'], $user['lat'], $user['lon']);
        $form['form'] = FillForm($res['datas']['rows'], $form['form']);
        print_r($form);
        SubmitTask($apis['submit-url'], $cookie['cookies'], $form, '信息收集');//提交表单
    }
}
//填充必填项表单答案
function FillForm($fillarr, $keyarr){
    if ($fillarr == null || $keyarr == null) return [];
    $k1 = 0;//任务表单必填项下标
    foreach($fillarr as $keyname => $keyvalue){
        if ($keyvalue['isRequired'] == 1) {
            switch ($keyvalue['fieldType']) {//判断类型
                case 1://文本类型
                    $fillarr[$keyname]['value'] = $keyarr[$k1];//填充答案
                    break;
                case 4://图片
                    echo '<br>上传图片功能未必能使用!!!<br>';
                    $fillarr[$keyname]['value'] = $keyarr[$k1];
                    break;
                case 5://文本类型
                    $fillarr[$keyname]['value'] = $keyarr[$k1];
                    break;
                default://选择题
                    $correctkey = DelSurplusOption($fillarr[$keyname]['fieldItems'], $keyarr[$k1]);
                    $fillarr[$keyname]['fieldItems'] = [];//清空选项
                    for($k2 = 0; $k2 < count($correctkey); $k2++){
                        $fillarr[$keyname]['value'] .= $keyarr[$k1][$k2].' ';//填充答案
                        $fillarr[$keyname]['fieldItems'][] = $correctkey[$k2];//覆盖fieldItems数组
                    }
            }
            $k1++;//记录必填项
        }else{
            unset($fillarr[$keyname]);//删除非必填项
        }
    }
    sort($fillarr);//序列化数组
    return $fillarr;
}
//删除多余项
function DelSurplusOption($option, $keyarr, $flag = 0){
    $correctkey = [];//定义答案数组
    foreach ($option as $optkey => $optvalue) {
        if ($optvalue['content'] == $keyarr[$flag]) {
            $correctkey[] = $optvalue;//填充fieldItems数组
            if(count($keyarr) > 1) $flag++;//多选
        }
    }
    return $correctkey;
}
?>
