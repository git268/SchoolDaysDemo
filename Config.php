<?php
require_once 'ToolsHelper.php';
//用户信息
function User(){
    $user = ['username'=> '账号', 'password'=>'密码', 'address'=>'地址',
        'email'=> 'None', 'school'=> '', 'lon'=> '经度',
        'lat'=> '纬度', 'abnormalReason'=> '在学校'  ];
    return $user;
}
function ToolsKey(){
    $_POST['Tools'] = ['ServerChanKey'=> '',    //Server酱油key
                        'QmsgKey'=> '',           //Qmsg酱key
        'BaiDuOCRKey'=> [ 'grant_type' => 'client_credentials',     //百度OCR默认参数
        'client_id' => '',                   //百度OCR API KEY
        'client_secret' => ''   ]];//百度OCR Secret KEY
}
//签到API
function SignAPIS(){
    $url = $_POST['school'];
    $apis = [   'login-api'=> 'http://www.zimo.wiki:8080/wisedu-unified-login-api-v1.0/api/login',
                'login-url'=> $url['idsUrl'].'/login?service='.$url['scheme'].'%3A%2F%2F'.$url['host'].'%2Fportal%2Flogin',
                'datas-url'=> $url['scheme'].'://'.$url['host'].'/wec-counselor-sign-apps/stu/sign/getStuSignInfosInOneDay',
                'task-url'=> $url['scheme'].'://'.$url['host'].'/wec-counselor-sign-apps/stu/sign/detailSignInstance',
                'submit-url'=> $url['scheme'].'://'.$url['host'].'/wec-counselor-sign-apps/stu/sign/submitSign'];
    return $apis;
}
//信息收集API
function CollectAPIS(){
    $url = $_POST['school'];
    $apis = [   'login-api'=>'http://www.zimo.wiki:8080/wisedu-unified-login-api-v1.0/api/login',
                'login-url'=> $url['idsUrl'].'/login?service='.$url['scheme'].'%3A%2F%2F'.$url['host'].'%2Fportal%2Flogin',
                'datas-url'=> $url['scheme'].'://'.$url['host'].'/wec-counselor-collector-apps/stu/collector/queryCollectorProcessingList',
                'task-url'=> $url['scheme'].'://'.$url['host'].'/wec-counselor-collector-apps/stu/collector/detailCollector',
                'form-url'=> $url['scheme'].'://'.$url['host'].'/wec-counselor-collector-apps/stu/collector/getFormFields',
                'submit-url'=> $url['scheme'].'://'.$url['host'].'/wec-counselor-collector-apps/stu/collector/submitForm'   ];
    return $apis;
}
//获取校园信息URL
function SchoolMessageURL(){
    $url = [    'list'=> 'https://mobile.campushoy.com/v6/config/guest/tenant/list',
                'info'=> 'https://mobile.campushoy.com/v6/config/guest/tenant/info' ];
    return $url;
}
//表头
function Headers($cookie){
    $header = [ 'Accept:application/json, text/plain, */*',
                'User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36',
                'Accept-Encoding:gzip,deflate','content-type:application/json', 'Cookie:'.$cookie,
                'Accept-Language:zh-CN,en-US;q=0.8', 'Content-Type:application/json;charset=UTF-8'  ];
    return $header;
}
//提交签到表单head
function Headers2($cookie, $extension){
    $header = [ 'User-Agent:Mozilla/5.0 (Linux; Android 7.0.1; OPPO R11 Plus Build/KTU84P) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/33.0.0.0 Safari/537.36 okhttp/3.12.4',
                'CpdailyStandAlone:0', 'extension:1', 'Cpdaily-Extension:'.$extension,'Cookie:'.$cookie,
                'Content-Type:application/json; charset=utf-8', 'Accept-Encoding:gzip', 'Connection:Keep-Alive' ];
    return $header;
}
//模拟登录
function DoLoginHeader($referer, $cookie){
    $header = [ 'Accept: application/json, text/plain, */*',
                'Accept-Encoding: gzip,deflate, br',
                'Accept-Language: zh-CN,zh;q=0.8',
                'Connection: keep-alive',
                'Referer: '.$referer,
                'Cookie: '.$cookie  ];
    return $header;
}
function CaptchaHeader($cookie){
    $header = [ 'Accept: text/html, image/gif, image/jpeg, *; q=.2, */*; q=.2',
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36',
                'Content-Type=image/jpeg;charset=UTF-8',
                'Cookie: '.$cookie  ];
    return $header;
}
?>