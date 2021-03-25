<?php
require_once 'LoginHelper.php';
require_once 'ToolsHelper.php';
require_once 'Config.php';
function GetCookie($name, $pwd, $loginapi=''){//模拟登录
    $loginurl = $_POST['school']['idsUrl'].'/login?service=https://'.$_POST['school']['host'].'/portal/login';//云端登录入口
    //构造登录请求参数
    $_POST['params'] = [   'login_url'=> $loginurl, 'needcaptcha_url'=> '',
        'captcha_url'=> '', 'username'=> $name, 'password'=> $pwd  ];
    if(empty($loginapi)){//使用本地模拟登录获取cookie
        AnalysisType($_POST['school']['idsUrl'],$name, $pwd);//分析学校类型
    }else{//使用zimo服务器模拟登录获取cookie
        $res =  json_decode(SendRequest($loginapi, [], $_POST['params']), true);
        if(!($res['msg']=='login success!' || $res['msg']=='SUCCESS')) $_POST['tips'] = $res['msg'];//判断获取有无异常
        $_COOKIE = $res['cookies'];
    }
}
//iap学校模拟登录
function IapSchoolLogin($name, $pwd){
    SendRequest($_POST['params']['login_url'], [], '', 'GET', 1);
    $_COOKIE = PregMatchMsg($_POST['headers'], 'Set-Cookie:', ';');//正则匹配cookie
    $lt = substr(explode("_2lBepC=", $_POST['headers'])[1], 0, -4);//提取响应头中的lt
    $url = 'https://'.$_POST['school']['host'].'/iap/doLogin';//登录url
    $header = DoLoginHeader($url);//登录请求头
    $form = [ 'password'=>$pwd,'captcha'=> '', 'mobile'=>'','lt'=>$lt,//构造参数
        'rememberMe'=>'false', 'username'=>$name, 'dllt'=>''];
    SendRequest($url, $header, http_build_query($form), 'POST', 1);//获取MOD_AUTH_CAS
}
//普通cas学校模拟登录
function NormalCasSchoolLogin($name, $pwd){
    $res = SendRequest($_POST['params']['login_url'], [], '', 'GET', 1);//获取CONVERSATION
    $_COOKIE = 'org.springframework.web.servlet.i18n.CookieLocaleResolver.LOCALE=zh_CN;'.strstr($_POST['headers'], 'route=');
    $_COOKIE = substr($_COOKIE, 0, 109).';'.PregMatchMsg($_COOKIE, 'Set-Cookie:', ';');//正则匹配其他cookie
    $key = PregMatchMsg($res, '"pwdDefaultEncryptSalt" value="', '"', true);//获取AES加密的密钥
    if(isset($key))$pwd = AESEncrypt($pwd, $key);//AES加密
    $header = DoLoginHeader('https://'.$_POST['school']['host'].'/portal/login');//登录请求头
    $form = [   'username'=> $name, 'password'=> $pwd, 'lt'=> PregMatchMsg($res, '="lt" value="', '"', true),
                'dllt'=> 'userNamePasswordLogin', 'execution'=> 'e1s1', '_eventId'=> 'submit', 'rmShown'=> '1'];//构造参数
    SendRequest($_POST['params']['login_url'], $header, http_build_query($form), 'POST', 1);//获取MOD_AUTH_CAS
}
//CAS特殊情况之一：河南大学模拟登陆
function HenuSchoolLogin($name, $pwd){
    $res = SendRequest($_POST['school']['idsUrl'].'/login', [], '', 'GET', 1);//获取CONVERSATION
    $_COOKIE = 'org.springframework.web.servlet.i18n.CookieLocaleResolver.LOCALE=zh_CN;'.strstr($_POST['headers'], 'route=');
    $_COOKIE = substr($_COOKIE, 0, 109).';'.PregMatchMsg($_COOKIE, 'Set-Cookie:', ';');//正则匹配其他cookie
    $header = DoLoginHeader('https://'.$_POST['school']['host'].'/portal/login');//登录请求头
    $form = [   'username'=> $name, 'password'=> AESEncrypt($pwd, PregMatchMsg($res, '"pwdEncryptSalt" value="', '"', true)), 'lt'=> '',
        'cllt'=> 'userNameLogin', 'execution'=> PregMatchMsg($res, 'execution" value="', '"', true), '_eventId'=> 'submit', 'captcha'=> ''];//构造参数
    SendRequest($_POST['params']['login_url'], $header, http_build_query($form), 'POST', 1);//获取MOD_AUTH_CAS
    $_COOKIE .= ';'.PregMatchMsg($_POST['headers'], 'Set-Cookie: ', ';');//更新cookie
    SendRequest($_POST['params'][ 'login_url'], ['Cookie: '.$_COOKIE], '', 'GET', 1);//获取location
    SendRequest(substr(strstr($_POST['headers'], 'https://'), 0, -4), $header, '', 'GET', 1);//获取HWWAFSESID
}
//该种特殊情况未作适配，请联系开发者
function NotAdaptedSchool($name, $pwd){
    $_POST['tips'] = '该学校模拟登陆功能暂未完善，请提供账号给开发者作开发。';
    echo '<br>'.$_POST['tips'].'<br>';
}
?>