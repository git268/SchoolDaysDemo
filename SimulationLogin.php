<?php
require_once 'ToolsHelper.php';
require_once 'Config.php';
function GetCookie($name, $pwd, $loginapi=''){//模拟登录
    $loginurl = $_POST['school']['idsUrl'].'/login?service=https://'.$_POST['school']['host'].'/portal/login';//云端登录入口
    //构造登录请求参数
    $_POST['params'] = [   'login_url'=> $loginurl, 'needcaptcha_url'=> '',
                            'captcha_url'=> '', 'username'=> $name, 'password'=> $pwd  ];
    if(empty($loginapi)){//使用本地模拟登录获取cookie
        if(is_numeric(strpos($_POST['school']['idsUrl'], 'iap')))
            IapSchoolLogin($name, $pwd);//适配所有iap学校
       else
            AuthserverSchoolLogin($name, $pwd);//适配部分Authserver学校
    }else{//使用zimo服务器模拟登录获取cookie
        $res =  json_decode(SendRequest($loginapi, [], $_POST['params']), true);
        if(!($res['msg']=='login success!' || $res['msg']=='SUCCESS')) $_POST['tips'] = $res['msg'];//判断获取有无异常
        $_COOKIE = $res['cookies'];
    }
    echo"<br>cookie<br>";
    print_r($_COOKIE);
}
//iap学校模拟登录
function IapSchoolLogin($name, $pwd){
    SendRequest($_POST['params']['login_url'], [], '', 'GET', 1);//获取CONVERSATION
    $_COOKIE = PregMatchMsg($_POST['headers'], 'Set-Cookie:', ';');//正则匹配cookie
    $lt = substr(explode("_2lBepC=", $_POST['headers'])[1], 0, -4);//提取响应头中的lt
    $form = [ 'password'=>$pwd,'captcha'=> '', 'mobile'=>'','lt'=>$lt,//构造参数
                'rememberMe'=>'false', 'username'=>$name, 'dllt'=>''];
    $url = 'https://'.$_POST['school']['host'].'/iap/doLogin';//登录url
    $header = DoLoginHeader($url);//登录请求头
    $res = SendRequest($url, $header, http_build_query($form), 'POST', 1);//获取MOD_AUTH_CAS
    CheckFinallCookie($res);//检查有无最终的cookie MOD_AUTH_CAS并获取
}
//authserver学校模拟登录
function AuthserverSchoolLogin($name, $pwd){
    $res = SendRequest($_POST['params']['login_url'], [], '', 'GET', 1);//获取CONVERSATION
    $_COOKIE = substr(explode('Cache-Control',strstr($_POST['headers'], 'route='))[0], 0, -2);//因为这个route非常特殊，很难截取
    $_COOKIE .= ';'.PregMatchMsg($_POST['headers'], 'Set-Cookie:', ';').';org.springframework.web.servlet.i18n.CookieLocaleResolver.LOCALE=zh_CN';//正则匹配其他cookie
    $key = PregMatchMsg($res, '"pwdDefaultEncryptSalt" value="', '"', true);//获取AES加密的密钥
    if(isset($key))$pwd = AESEncrypt($pwd, $key);//AES加密
    $header = DoLoginHeader('https://'.$_POST['school']['host'].'/portal/login');//登录请求头
    $form = [   'username'=> $name, 'password'=> $pwd, 'lt'=> PregMatchMsg($res, '="lt" value="', '"', true),
        'dllt'=> 'userNamePasswordLogin', 'execution'=> 'e1s1', '_eventId'=> 'submit', 'rmShown'=> '1'];//构造参数
    SendRequest($_POST['params']['login_url'], $header, http_build_query($form), 'POST', 1);//获取MOD_AUTH_CAS
    CheckFinallCookie($_POST['headers']);//检查有无最终的cookie MOD_AUTH_CAS并获取
}
//获取最终的cookie  MOD_AUTH_CAS
function CheckFinallCookie($response){
    if(is_numeric(strpos($response, 'MOD_AUTH_CAS')))//判断是否存在获取MOD_AUTH_CAS
        $_COOKIE = PregMatchMsg($response, 'Set-Cookie:', ';');//正则匹配cookie;
    else
        $_POST['tips'] = '账号密码错误或不支持该类学校模拟登录。';//返回错误信息
}
//正则匹配响应头
function PregMatchMsg($responsehead, $start, $end, $flag=false) {
    $preg = '/'.$start.'(.*?)'.$end.'/m';
    preg_match_all($preg, $responsehead, $text);
    if($flag)
        return $text[1][0];//只返回第一次匹配的信息
    else
        return implode(';', $text[1]);//返回全部匹配的信息，使用;分隔
}
//AES加密
function AESEncrypt($text, $key){
    $iv = '0000000000000000';//初始向量
    $text = bin2hex(random_bytes(32)).$text;//加密明文前需要64位随机数
    $pad = 16 - (strlen($text) % 16);
    $text = $text . str_repeat(chr($pad), $pad);//PKCS5填充
    $res = openssl_encrypt($text, 'AES-128-CBC', $key, OPENSSL_NO_PADDING, $iv);
    return base64_encode($res);
}
?>
