<?php
require_once '../Index/SubmitForm.php';
require_once '../Index/ToolsHelper.php';
function getSignTasks($user, $apis){//签到
    $headers = TaskHeader();//获取请求头部
    //echo"<br>第二次请求获取签到任务<br>";
    $datas = json_decode(SendRequest($apis['datas-url'], $headers, json_encode($_POST['params'])), true)['datas']['unSignedTasks'];//获取任务
    //print_r($datas);
    if (isset($datas[0]) && empty($_POST['tips'])){//判断有无任务及cookie是否正常
        $time = [$datas[0]['currentTime'], $datas[0]['rateTaskBeginTime'], $datas[0]['rateTaskEndTime']];//获取任务当前、开始和结束时间
        if(strtotime($time[0])<strtotime($time[1]) || strtotime($time[0]) > strtotime($time[2]))$_POST['tips'] = '非任务限定时间内。';//判断是否在任务时间内
        $params = ['signInstanceWid'=> $datas[0]['signInstanceWid'],'signWid'=> $datas[0]['signWid']];//获取任务wid
        $res = json_decode(SendRequest($apis['task-url'], $headers, json_encode($params)), true)['datas'];//获取详细任务
        $form = SignForm($res['signInstanceWid'], $user);//获取答卷
        /*
        $address = $res['signPlaceSelected'][0];
        $address = ['地址'=> $address['address'], '经度'=> $address['longitude'], '纬度'=> $address['latitude']];
        echo"<br>当前需要的任务经纬度:<br>";
        print_r($address);
        */
        echo"<br>当前任务<br>";
        //print_r($res);
        if($res['isNeedExtra'] == 1)//判断有无附加问题
            //$form['extraFieldItems'] = FillSignForm($res, $form['extraFieldItems']);//手动填充答卷
            $form['extraFieldItems'] = FillSignForm($res, $form['extraFieldItems'], true);//自动填充答卷
        else
            $form['extraFieldItems'] = [];//清空额外项
        if($res['isPhoto'] ==1)//判断是否需要上传图片
            $form['signPhotoUrl'] = UploadPicture($form['signPhotoUrl'], $apis['put-photo'], $apis['get-photo']);//上传图片
        else
            $form['signPhotoUrl'] = '';//清空图片项
        SubmitTask($apis['submit-url'], $form, $user, 2);//提交表单信息
    } else {
        if (empty($_POST['tips'])) $_POST['tips'] = '当前没有签到任务。';
    }
}
//填充签到任务答卷
function FillSignForm($fillarr, $keyarr, $mode=false, $flag=0, $correctkey=[]){
    if($mode)///根据装填模式选择问卷
        $fillarr = $fillarr['signedStuInfo']['extraFieldItemVos'];//根据历史答卷自动装填
    else
        $fillarr = $fillarr['extraField'];//根据脚本内嵌的答卷手动装填
    foreach ($fillarr as $fillkey => $fillvalue) {//遍历问题
        if($mode){//填充模式
            $correctkey[] = ['extraFieldItemValue' => $fillvalue['extraFieldItem'], 'extraFieldItemWid' => $fillvalue['extraFieldItemWid']];//根据历史答案自动装填
        }else{
            foreach ($fillvalue['extraFieldItems'] as $itemkey => $itemvalue) {//遍历选项
                if ($itemvalue['isAbnormal'] == false) {//根据有标识的正确选项自动装填选择题
                    if ($itemvalue['isOtherItems'] == 1) {//其他项填充非选择题
                        $itemvalue['content'] = $keyarr[$flag];//文本类型答案填充
                        $flag++;//记录非选择题
                    }
                    $correctkey[] = ['extraFieldItemValue' => $itemvalue['content'], 'extraFieldItemWid' => $itemvalue['wid']];//填充选项答案
                    break;//遍历到正确答案后进入下一题
                }
            }
        }
        if(empty($correctkey[$fillkey]['extraFieldItemValue'])){//填充自检
            $_POST['tips'] = (int)$fillkey + 1 . ': '.$fillvalue['title'] . '填写失败。';//返回报错
            return [];
        }
    }
    return $correctkey;
}
?>