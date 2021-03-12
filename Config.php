<?php
require_once 'ToolsHelper.php';
//用户信息
function User(){
    $user = [   'username'=> '账号', 'password'=>'密码', 'lon'=> '经度', 'lat'=> '纬度',
        'school'=> '学校全称',  'abnormalReason'=> '在学校', 'address'=>'地址',
        'notice'=> ['type'=>'推送类型', 'key'=> '推送方式的key']
            ];
    return $user;
}
//签到API
function SignAPIS(){
    $apis = [   'datas-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-sign-apps/stu/sign/getStuSignInfosInOneDay',
                'task-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-sign-apps/stu/sign/detailSignInstance',
                'photo-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-sign-apps/stu/sign/previewAttachment',
                'submit-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-sign-apps/stu/sign/submitSign' ];
    return $apis;
}
//信息收集API
function CollectAPIS(){
    $apis = [   'datas-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-collector-apps/stu/collector/queryCollectorProcessingList',
                'task-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-collector-apps/stu/collector/detailCollector',
                'form-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-collector-apps/stu/collector/getFormFields',
                'submit-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-collector-apps/stu/collector/submitForm'   ];
    return $apis;
}
//查寝API
function AttendanceAPIS(){
    $apis = [   'datas-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-attendance-apps/student/attendance/getStuAttendacesInOneDay',
                'task-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-attendance-apps/student/attendance/detailSignInstance',
                'photo-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-attendance-apps/student/attendance/previewAttachment',
                'submit-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-attendance-apps/student/attendance/submitSign' ];
    return $apis;
}
//辅导员信息确认
function ConfirmAPIS(){
    $url = $_POST['school'];
    $apis = [   'datas-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-stu-apps/stu/notice/queryProcessingNoticeList',
                'task-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-stu-apps/stu/notice/detailNotice',
                'submit-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-stu-apps/stu/notice/confirmNotice'   ];
    return $apis;
}
//获取校园信息URL
function SchoolMessageURL(){
    $url = [    'list'=> 'https://mobile.campushoy.com/v6/config/guest/tenant/list',
                'info'=> 'https://mobile.campushoy.com/v6/config/guest/tenant/info' ];
    return $url;
}
//获取任务请求头
function Headers(){
    $header = [ 'Accept:application/json, text/plain, */*',
        'User-Agent:Mozilla/5.0 (Linux; Android 6.0.1; vivo Y66L Build/MMB29M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/51.0.2704.81 Mobile Safari/537.36  cpdaily/8.2.20 wisedu/8.2.20',
        'content-type:application/json', 'Cookie:'.$_COOKIE,
        'Accept-Language:zh-CN,en-US;q=0.8', 'Content-Type:application/json;charset=UTF-8'  ];
    return $header;
}
//提交签到表单请求头
function Headers2($extension){
    $header = ['User-Agent:Mozilla/5.0 (Linux; Android 6.0.1; vivo Y66L Build/MMB29M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/51.0.2704.81 Mobile Safari/537.36  cpdaily/8.2.20 wisedu/8.2.20',
                'CpdailyStandAlone:0', 'extension:1', 'Cpdaily-Extension:'.$extension,'Cookie:'.$_COOKIE,
                'Content-Type:application/json; charset=utf-8', 'Connection:Keep-Alive' ];
    return $header;
}
//模拟登录请求头
function DoLoginHeader($referer){
    $header = [ 'Accept: application/json, text/plain, */*', 'Accept-Language: zh-CN,zh;q=0.8', 'Connection: keep-alive',
                'Referer: '.$referer, 'Cookie: '.$_COOKIE, 'Content-Type:application/x-www-form-urlencoded'];
    return $header;
}
?>
