<?php
require_once 'SubmitForm.php';
require_once 'ToolsHelper.php';
function getSignTasks($user, $apis){//签到
    $headers = Headers();//获取请求头部
    //echo"<br>第二次请求获取签到任务<br>";
    $datas = json_decode(SendRequest($apis['datas-url'], $headers, json_encode($_POST['params'])), true)['datas']['unSignedTasks'];//获取任务
    //print_r($datas);
    if (isset($datas[0]) && empty($_POST['tips'])){//判断有无任务及cookie是否正常
        $params = ['signInstanceWid'=> $datas[0]['signInstanceWid'],'signWid'=> $datas[0]['signWid']];//获取任务wid
        //echo"<br>当前任务<br>";
        $res = json_decode(SendRequest($apis['task-url'], $headers, json_encode($params)), true)['datas'];//获取详细任务
        $form = SignForm($res['signInstanceWid'], $user);//获取答卷
        //print_r($res);
        if($res['isNeedExtra'] == 1)$form['extraFieldItems'] = FillSignForm($res, $form['extraFieldItems']);//手动填充答卷
        //if($res['isNeedExtra'] == 1)$form['extraFieldItems'] = FillSignForm($res, $form['extraFieldItems'], true);//自动填充答卷
        if($res['isPhoto'] ==1)$form['signPhotoUrl'] = UploadPicture($form['signPhotoUrl'], $apis['photo-url']);//上传图片
        echo"<br>填充表单<br>";
        print_r($form);
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
