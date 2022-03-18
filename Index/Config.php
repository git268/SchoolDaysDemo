<?php
require_once 'ToolsHelper.php';
//用户信息
function User(){
    $user = [
        [  'username'=> '账号A', 'password'=>'密码A', 'lon'=> '经度', 'lat'=> '纬度',
            'school'=> '学校全称',  'abnormalReason'=> '在学校', 'address'=>'地址',
            'photo'=>'../SaveFile/black.jpg','mode'=> '任务A', 'notice'=> ['type'=>'推送类型A', 'key'=> '推送方式的key']],
        [  'username'=> '账号B', 'password'=>'密码B', 'lon'=> '经度', 'lat'=> '纬度',
            'school'=> '学校全称',  'abnormalReason'=> '在学校', 'address'=>'地址',
            'photo'=>'../SaveFile/black.jpg','mode'=> '任务B', 'notice'=> ['type'=>'推送类型B', 'key'=> '推送方式的key']],
        [ 'username'=> '账号C', 'password'=>'密码C', 'lon'=> '经度', 'lat'=> '纬度',
            'school'=> '学校全称',  'abnormalReason'=> '在学校', 'address'=>'地址',
            'photo'=>'../SaveFile/black.jpg','mode'=> '任务C', 'notice'=> ['type'=>'推送类型C', 'key'=> '推送方式的key']]
    ];
    return $user;
}
//签到API
function SignAPIS(){
    $apis = [   'datas-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-sign-apps/stu/sign/getStuSignInfosInOneDay',
        'task-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-sign-apps/stu/sign/detailSignInstance',
        'put-photo'=>'https://'.$_POST['school']['host'].'/wec-counselor-sign-apps/stu/oss/getUploadPolicy',
        'get-photo'=> 'https://'.$_POST['school']['host'].'/wec-counselor-attendance-apps/student/attendance/previewAttachment',
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
        'put-photo'=>'https://'.$_POST['school']['host'].'/wec-counselor-sign-apps/stu/obs/getUploadPolicy',
        'get-photo'=> 'https://'.$_POST['school']['host'].'/wec-counselor-sign-apps/stu/sign/previewAttachment',
        'submit-url'=> 'https://'.$_POST['school']['host'].'/wec-counselor-attendance-apps/student/attendance/submitSign' ];
    return $apis;
}
//辅导员信息确认
function ConfirmAPIS(){
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
function TaskHeader(){
    $header = [ 'Accept:application/json, text/plain, */*',
        'User-Agent:Mozilla/5.0 (Linux; Android 11.0.2; '.MOBILETYPE.' Build/MMB29M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/51.0.2704.81 Mobile Safari/537.36  cpdaily/'.APPVERSION.' wisedu/'.APPVERSION.'',
        'content-type:application/json', 'Cookie:'.$_COOKIE,
        'Accept-Language:zh-CN,en-US;q=0.8', 'Content-Type:application/json;charset=UTF-8'  ];
    return $header;
}
//提交签到表单请求头
function SubmitHeader($extension){
    $header = ['User-Agent:Mozilla/5.0 (Linux; Android 11.0.2; '.MOBILETYPE.' Build/MMB29M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/51.0.2704.81 Mobile Safari/537.36  cpdaily/'.APPVERSION.' wisedu/'.APPVERSION.'',
                'CpdailyStandAlone:0', 'extension:1', 'Cpdaily-Extension:'.$extension,'Cookie:'.$_COOKIE,
                'Content-Type:application/json; charset=utf-8', 'Connection:Keep-Alive' ];
    return $header;
}
//模拟登录请求头
function DoLoginHeader($referer){
    $header = [ 'Accept: application/json, text/plain, */*', 'Accept-Language: zh-CN,zh;q=0.8', 'Connection: keep-alive',
        'User-Agent:Mozilla/5.0 (Linux; Android 11.0.2; '.MOBILETYPE.' Build/MMB29M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/51.0.2704.81 Mobile Safari/537.36  cpdaily/'.APPVERSION.' wisedu/'.APPVERSION.'',
                'Referer: '.$referer, 'Cookie: '.$_COOKIE, 'Content-Type:application/x-www-form-urlencoded'];
    return $header;
}
?>