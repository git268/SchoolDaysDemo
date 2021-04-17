<?php
require_once 'SimulationLogin.php';
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
    $text = bin2hex(random_bytes(32)).$text;//加密明文前需要64位随机字符串
    $pad = 16 - (strlen($text) % 16);
    $text = $text . str_repeat(chr($pad), $pad);//PKCS5填充
    $res = openssl_encrypt($text, 'AES-128-CBC', $key, OPENSSL_NO_PADDING, $iv);//加密
    return base64_encode($res);//base64编码
}
//分析模拟登录类型
function AnalysisType($url, $name, $pwd){
    switch($url){
        case is_numeric(strpos($url, 'iap')):
            IapSchoolLogin($name, $pwd);//iap模拟登陆
            break;
        case is_numeric(strpos($url, 'ids.henu.edu.cn'))://CAS河南大学模拟登陆
            HenuSchoolLogin($name, $pwd);
            break;
        case is_numeric(strpos($url, 'ehall.ahjzu.edu.cn')):
            NotAdaptedSchool($name, $pwd);
            break;
        case is_numeric(strpos($url, 'ehall.kmu.edu.cn')):
            NotAdaptedSchool($name, $pwd);
            break;
        case is_numeric(strpos($url, 'cas.whpu.edu.cn')):
            NotAdaptedSchool($name, $pwd);
            break;
        case is_numeric(strpos($url, 'ehall.sduc.edu.cn')):
            NotAdaptedSchool($name, $pwd);
            break;
        case is_numeric(strpos($url, 'jwgl.cuit.edu.cn')):
            NotAdaptedSchool($name, $pwd);
            break;
        case is_numeric(strpos($url, 'ehall.hfnu.edu.cn')):
            NotAdaptedSchool($name, $pwd);
            break;
        case is_numeric(strpos($url, 'uth.hfut.edu.cn')):
            NotAdaptedSchool($name, $pwd);
            break;
        default:
            NormalCasSchoolLogin($name, $pwd);//常规CAS模拟登陆
            break;
    }
    CheckFinallCookie($_POST['headers']);//检查有无最终的cookie MOD_AUTH_CAS并获取
}
//获取最终的cookie  MOD_AUTH_CAS
function CheckFinallCookie($response){
    if(is_numeric(strpos($response, 'MOD_AUTH_CAS')))//判断是否存在获取MOD_AUTH_CAS
        $_COOKIE = PregMatchMsg($response, 'Set-Cookie:', ';');//正则匹配cookie;
    else
        $_POST['tips'] = '账号密码错误或不支持该类学校模拟登录。';//返回错误信息
}
?>