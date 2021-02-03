<?php
require_once 'ToolsHelper.php';
require_once 'Config.php';
function StartLogin(){
    $url = $_POST['school']['idsUrl'].'/login?service=https%3A%2F%2F'.$_POST['school']['host'].'%2Fportal%2Flogin';
    SendRequest($url, [], '', 'GET', 1);
    $cookie = explode('Set-Cookie:', $_POST['headers']);
    $_POST['cookie'] = [explode(';', $cookie[1])[0], explode(';', $cookie[2])[0]];
    $_POST['location'] = explode('Location:', $_POST['headers'])[1];
    $lt = substr(explode("=",$_POST['location'])[1], 0, -4);
    $cookie = $_POST['cookie'][0].';'.$_POST['cookie'][1];
    return DoLogin($cookie, $lt);
}
function CheckNeedCaptcha($url, $username, $captchaurl, $cookie, $lt){
    $needcaptchaurl = $_POST['school']['scheme'].$url.'?username='.$username;
    $res = json_decode(SendRequest($needcaptchaurl, []), true);
    $code = '';
    if($res['needCaptcha'] == true){
        echo '<br>识别验证码<br>';
        for($count = 0; $count < 3; $count++){
            $captchaurl = $captchaurl.'?ltId='.$lt;
            $captchaheader = CaptchaHeader($cookie);
            $code = BaiDuOCRCaptcha(SendRequest($captchaurl, $captchaheader, []));
            if(strlen($code) == 5)break;
        }
    }
    return $code;
}
function DoLogin($cookie, $lt){
    $user = User();
    $resulturl = LoginEntity($_POST['school']['host']);
    $code = CheckNeedCaptcha($resulturl['needcaptchaUrl'], $user['username'], $resulturl['captchaUrl'], $cookie, $lt);
    $cookie = ['cookies'=> $cookie, 'msg'=>'SUCCESS', 'code'=> 1];
    if(strlen($code) !=5 && !empty($code)){
        $cookie['msg'] = '验证码识别有误，请重试!';
        return $cookie;
    }
    $params = [ 'password'=>$user['password'],'captcha'=> $code, 'mobile'=>'','lt'=>$lt,
                'rememberMe'=>'false', 'username'=>$user['username'], 'dllt'=>''];
    $url = $_POST['school']['scheme'].'://'.$resulturl['doLoginUrl'];
    $dlheader = DoLoginHeader($url, $cookie['cookies']);
    $res = SendRequest($url, $dlheader, http_build_query($params), 'POST', 1);
    $CASTGC = explode(';', explode('Set-Cookie:', $res)[1])[0];
    $MOD_AUTH_CAS = explode(';', explode('Set-Cookie:', $res)[2])[0];
    $cookie['cookies'] .= ';'.$CASTGC.';'.$MOD_AUTH_CAS;
    return $cookie;
}
function LoginEntity($hosturl){
    $resulturl = [];//初始化
    $resulturl['doLoginUrl'] = $hosturl.'/iap/doLogin';
    $resulturl['needcaptchaUrl'] = $hosturl.'/iap/checkNeedCaptcha';
    $resulturl['captchaUrl'] = $hosturl.'/iap/generateCaptcha';
    return $resulturl;
}
?>