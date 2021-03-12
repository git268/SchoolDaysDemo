<?php
require_once 'SubmitForm.php';
require_once 'ToolsHelper.php';
function getQueryTasks($user, $apis){//信息确认任务
    $headers = Headers();//获取请求头部
    //echo "<br>第二次请求获取信息收集任务<br>";
    $datas = json_decode(SendRequest($apis['datas-url'], $headers, json_encode(['pageSize' => 6, 'pageNumber' => 1])), true)['datas']['rows'];//获取任务
    //print_r($datas);
    if (isset($datas[0]) && empty($_POST['tips'])){//判断有无任务及cookie是否正常
        $noticewid = ['wid'=> $datas[0]['noticeWid']];//获取任务表单
        echo"<br>当前任务<br>";
        $res = json_decode(SendRequest($apis['task-url'], $headers, json_encode($noticewid), true));//获取详细任务
        print_r($res);
        SubmitTask($apis['submit-url'],  $noticewid, $user, 3);//提交表单信息
    } else {
        if (empty($_POST['tips'])) $_POST['tips'] = '当前没有辅导员通知。';
    }
}
?>