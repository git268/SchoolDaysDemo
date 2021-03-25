<?php
require_once '../Index/SubmitForm.php';
require_once '../Index/ToolsHelper.php';
function getCheckChamber($user, $apis){//查寝
    $headers = TaskHeader();//获取请求头部
    echo"<br>第二次请求获取签到任务<br>";
    $datas = json_decode(SendRequest($apis['datas-url'], $headers, json_encode($_POST['params'])), true)['datas']['unSignedTasks'];//获取任务
    print_r($datas);
    if (isset($datas[0]) && empty($_POST['tips'])){//判断有无任务及cookie是否正常
        $params = ['signInstanceWid'=> $datas[0]['signInstanceWid'],'signWid'=> $datas[0]['signWid']];//获取任务wid
        //echo"<br>当前任务<br>";
        $res = json_decode(SendRequest($apis['task-url'], $headers, json_encode($params)), true)['datas'];//获取详细任务
        $form = SignForm($res['signInstanceWid'], $user);//获取答卷
        unset($form['extraFieldItems']);//删除额外项
        //print_r($res);
        if($res['isPhoto'] ==1)//判断图片
            $form['signPhotoUrl'] = UploadPicture($form['signPhotoUrl'], $apis['put-photo'], $apis['get-photo']);//上传图片
        else
            $form['signPhotoUrl'] = '';//清空图片项
        echo"<br>填充表单<br>";
        print_r($form);
        SubmitTask($apis['submit-url'], $form, $user, 3);//提交表单信息
    } else {
        if (empty($_POST['tips'])) $_POST['tips']= '当前没有任务。';
    }
}
?>