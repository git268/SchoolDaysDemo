<?php
require_once 'SubmitForm.php';
require_once 'ToolsHelper.php';
function getCollectTasks($user, $apis) {//信息收集
    $headers = TaskHeader();//获取请求头部
    //echo "<br>第二次请求获取信息收集任务<br>";
    $datas = json_decode(SendRequest($apis['datas-url'], $headers, json_encode(['pageSize' => 6, 'pageNumber' => 1])), true)['datas']['rows'];//获取任务
    if (isset($datas[0]) && empty($_POST['tips'])){//判断有无任务及cookie是否正常
        $collectWid = $datas[0]['wid'];//获取任务wid
        $formWid = $datas[0]['formWid'];
        $schoolTaskWid = json_decode(SendRequest($apis['task-url'], $headers, json_encode(['collectorWid' => $collectWid])), true)['datas'];
        //echo "<br>第三次请求具体信息收集任务<br>";
        $params = json_encode(['pageSize' => 100, 'pageNumber' => 1, 'formWid' => $formWid, 'collectorWid' => $collectWid]);
        $res = json_decode(SendRequest($apis['form-url'], $headers, $params), true);//获取信息收集详情
        //print_r($res);
        //echo "<br>填充表单<br>";
        $form = CollectForm($formWid, $collectWid, $schoolTaskWid['collector']['schoolTaskWid'], $user);//获取答卷
        //$form['form'] = FillCollectForm($res['datas']['rows'], $form['form']);//手动填充答卷
        $form['form'] = FillCollectForm($res['datas']['rows'], $form['form'], true);//自动填充答卷
        //print_r($form);
        SubmitTask($apis['submit-url'], $form, $user, 1);//提交表单
    }else{
        if (empty($_POST['tips'])) $_POST['tips'] = '当前没有任务。';
    }
}
//填充信息收集答卷
function FillCollectForm($fillarr, $keyarr, $mode=false, $flag=0){
    foreach ($fillarr as $keyname => $keyvalue){//遍历问题
        if ($keyvalue['isRequired'] == 1) {//必填项
            if ($keyvalue['fieldType']==2 || $keyvalue['fieldType']==3) {//填充单选/多选
                if($mode)//根据装填模式选择问卷
                    $correctkey = DelSurplusOption($fillarr[$keyname]['fieldItems'], [], $mode);//全自动构造答卷
                else
                    $correctkey = DelSurplusOption($fillarr[$keyname]['fieldItems'], $keyarr[$flag], $mode);//手动构造答卷
                $fillarr[$keyname]['fieldItems'] = $correctkey[0];//保留正确选项
                $fillarr[$keyname]['value'] = $correctkey[1];   //填充答案
            }else{//填充文本、图片。图片功能需要填写路径
                if(!$mode)$fillarr[$keyname]['value'] = $keyarr[$flag];
            }
            if(empty($fillarr[$keyname]['value'])){//填充自检
                $_POST['tips'] = $keyvalue['sort'].': '.$keyvalue['title'].'填写失败。';//返回报错
                return [];
            }
            $flag++;//记录必填项
        }else{
            unset($fillarr[$keyname]);//删除非必填项
        }
    }
    sort($fillarr);//序列化数组
    return $fillarr;
}
//构造选择题答卷
function DelSurplusOption($option, $keyarr, $mode, $flag = 0, $correctkey=[[],'']){
    foreach ($option as $key => $value) {//遍历选项
        if (($mode && $value['isSelected']==1) || (!$mode && $value['content']==$keyarr[$flag])) {//自动装填机
            $correctkey[0][] = $value;//填充fieldItems数组
            $correctkey[1] .= $value['content'].' ';//填充答案
            if (!$mode && count($keyarr) > 1) $flag++;//多选下标
        }
    }
    return $correctkey;
}
?>