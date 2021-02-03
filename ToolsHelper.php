<?php
//结果推送
function SendNotice($title, $desp, $type){
    $url = '';
    if($type == 'Qmsg'){  //Qmsg酱
        $url = 'https://qmsg.zendee.cn/send/'.$_POST['Tools']['QmsgKey'].'?'.http_build_query(['msg'=> $title.'  ,  '.$desp]);
    }else if($type == 'ServerChan'){    //Server酱
        $url = 'https://sc.ftqq.com/'.$_POST['Tools']['ServerChanKey'].'.send?'.http_build_query(['text'=> $title, 'desp'=> $desp]);
    }
    return file_get_contents($url);
}
function SendRequest($url, $headers=[], $data='', $method='POST', $type=0){//请求方法
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, $type);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if($method == 'POST'){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    $result = curl_exec($curl);
    if($type == 1){
        $headers = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $_POST['headers'] = substr($result, 0, $headers);
    }
    curl_close($curl);
    return $result;//JSON转化数组
}
//PKCS5填充
function PKCS5Padding($str, $blocksize=8){//pkcs5填充
    $pad = $blocksize - (strlen($str) % $blocksize);
    return $str . str_repeat(chr($pad), $pad);
}
//DES加密
function DESEncrypt($text, $key = 'b3L26XNL'){
    $iv = "\x01\x02\x03\x04\x05\x06\x07\x08";
    $text =PKCS5Padding($text);
    $res = openssl_encrypt($text, 'DES-CBC', $key, OPENSSL_NO_PADDING, $iv);
    return base64_encode($res);
}
//随机延时模块，简单反防作弊函数，能自定义延时。
function SleepTime($min, $max){
    if($min < 0 || $max <= $min){
        echo '参数错误<br>';
        return;
    }
    $time = rand($min, $max);//随机延时至多$sleepscond秒
    echo '延时'.$time.'秒<br>';
    sleep($time);
}
//百度OCR识别验证码
function BaiDuOCRCaptcha($image){
    $url = 'https://aip.baidubce.com/oauth/2.0/token';
    $token_result = json_decode(SendRequest($url, [], $_POST['Tools']['BaiDuOCRKey']));
    $token = $token_result->access_token;
    $ocrurl = 'https://aip.baidubce.com/rest/2.0/ocr/v1/general_basic?access_token=' . $token;
    $ocrdata = ['image' => base64_encode($image)];
    $ocrresult = json_decode(SendRequest($ocrurl, [], $ocrdata), true);
    return $ocrresult['words_result'][0]['words'];
}
?>