<?php
const DESKEY = 'b3L26XNL';
const AESKEY = 'ytUQ7l2ZZu8mLvJZ';

//结果推送
function SendNotice($content, $notice){
    switch($notice['type']) {
        case 1: //Qmsg酱
            $url = 'https://qmsg.zendee.cn/send/' .$notice['key'] . '?qq='.$notice['qq'].'&' .
                http_build_query(['msg' => $content[0] . '     ' . $content[1]]);
            break;
        case 2: //Server酱
            $url = 'https://sc.ftqq.com/' . $notice['key'] . '.send?' .
                http_build_query(['text' => $content[0], 'desp' => $content[1]]);
            break;
        case 3: //Telegram机器人
            $url = 'https://api.telegram.org/bot' . $notice['token'] . '/sendMessage?' .
                http_build_query(['chat_id' => $notice['chant_id'], 'text' => $content[0] . '     ' . $content[1]]);
            break;
        case 4: //pushplus
            $url = 'http://www.pushplus.plus/send?'.http_build_query(['token'=>$notice['key'],
                'title'=> $content[0], 'content'=> $content[1]]);
            break;
        default:
            return '参数错误!';
    }
    return SendRequest($url, [], '', 'GET');//输出推送结果
}
//curl请求模块
function SendRequest($url, $headers=[], $data='', $method='POST', $type=0){//请求方法
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HEADER, $type);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); //不验证证书
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    //curl_setopt($curl, CURLOPT_PROXY, 'http://127.0.0.1:代理端口号');//TG bot需要使用代理，请自备梯子
    if($method == 'POST'){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);//填写POST请求参数
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    }
    $result = curl_exec($curl);
    if($type == 1){
        $headers = curl_getinfo($curl, CURLINFO_HEADER_SIZE);//获取响应头
        $_POST['headers'] = substr($result, 0, $headers);//截取响应头
    }
    curl_close($curl);
    return $result;
}
//签到&查寝提交图片
function UploadPicture($path, $puturl, $geturl){
    $header = ['content-type:application/json', 'Cookie:'.$_COOKIE];//设置请求头
    $info = '.'.explode('/',getimagesize($path)['mime'])[1];//获取图片格式
    $res = json_decode(SendRequest($puturl, $header, json_encode(['fileType'=> 1])), true)['datas'];//获取今日校园图床url
    $fileName = $res['fileName'].$info;//获取上传的文件夹位置
    $data = ['key'=> $fileName, 'OSSAccessKeyId'=> $res['accessid'], 'success_action_status'=> '200',
        'policy'=> $res['policy'],'signature'=> $res['signature'], 'file'=>file_get_contents($path), 'image/jpg'];//填写参数
    SendRequest($res['host'], ['User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:50.0) Gecko/20100101 Firefox/50.0'], $data);//上传图片
    return json_decode(SendRequest($geturl, $header, json_encode(['ossKey'=> $fileName])), true)['datas'];//获取上传图片后的图床url
}
//毫秒级定时器&随机延时
function Timer($time){
    if(is_array($time)){//随机延时
        if($time[0]<0 || $time[0]>=$time[1])return;//参数错误
        $delay = mt_rand($time[0], $time[1]);//获取范围内随机数
        echo '延时'.$delay."秒\t\t";
        sleep($delay);//延时
    }else{//精确定时器
        for($count = 2400 ;$count > 0; $count--){//最大延时2分钟
            if($time == date('H:i:s'))break;
            usleep(50000);//精确到50毫秒
        }
        echo '定时结束。  '.date('H:i:s');
    }
}

?>