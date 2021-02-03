# SchoolDaysDemo
本代码根据https://github.com/ZimoLoveShuang/auto-sign
移植成PHP，只支持信息收集与签到，该脚本并未完全适配图片上传功能，若上传失败请绕路

*部署方法：*
推荐使用腾讯云选择云函数，环境选PHP 7，本脚本无任何依赖，填写好个人和对应的任务表单就能使用。
使用阿里云或本地执行亦可，保证使用PHP7的环境即可。

1,填写Config.php中User()的信息：
账号	密码	经/纬度[精确到小数点后5位]	学校全称		定位状态    

2,填写Config.php中ToolsKey()其他工具信息：

3,脚本运行结果推送：
'ServerChanKey' ： Server酱油key
'QmsgKey'：     Qmsg酱key
两者皆用于消息推送，使用哪个填哪个，默认使用Qmsg酱

4,若要更改推送方式，本脚本有2处推送运行结果，都需更改
第一处	[用于返回任务异常状态]
若为签到任务，在SignTask.php中第26行，
若为信息收集，在CollectMessage.php中第25行，
	
	print_r(SendNotice($title, date('Y-m-d H:i:s'), 'Qmsg'));   //Qmsg酱推送
	更改为
	print_r(SendNotice($title, date('Y-m-d H:i:s'), 'ServerChan'));   //Server酱推送

第二处	[用于返回答卷提交状态]
在SubmitForm.php中第16行
	
	print_r(SendNotice($title, date('Y-m-d H:i:s'), 'Qmsg'));   //Qmsg酱推送
	更改为
	print_r(SendNotice($title, date('Y-m-d H:i:s'), 'ServerChan'));   //Server酱推送
这样设计可以满足你同时使用不同推送方式A_A

BaiDuOCRKey是为不使用子墨API服务器准备的，若使用子墨的API可直接无视。
使用脚本获取cookie有局限性，详情见API服务器篇

5,学校URL填写：
因为在每次登录时适配不同学校的中查找list获得学校的host需要遍历全国各个
学校直到找到你的学校为止。如果只设置了用户信息，默认只查找并显示你所填写学校的链接。
如果你的学校排名较后，这个过程会消耗大量内存，CPU资源。
先执行一次本脚本，以今日校园https://mobile.campushoy.com/v6/config/guest/tenant/list
中第一个加入的学校  甘肃工业职业技术学院  为例

控制台会输出

	Array ( [idsUrl] => https://gipc.campusphere.net/iap [scheme] => https [host] => gipc.campusphere.net )

找到index.php主函数function main_handler()
若你的今日校园任务是签到，
可替换为[必须确保个人信息没有填写错误]

	function main_handler(){
	    $_POST['school'] = [   
		'idsUrl' => 'https://gipc.campusphere.net/iap',
		'scheme' => 'https',
		'host' => 'gipc.campusphere.net'    ];
	    ToolsKey();
	    //执行签到
	    getSignTasks(User());
	    $_POST = [];//清空超全局变量
	    echo '<br>执行完毕!';
	}
若你的今日校园任务是信息收集，
可替换为[必须确保个人信息没有填写错误]

	function main_handler(){
	   $_POST['school'] = [   
		'idsUrl' => 'https://gipc.campusphere.net/iap',
		'scheme' => 'https',
		'host' => 'gipc.campusphere.net'    ];
	    ToolsKey();
	    //执行信息收集
	    getCollectTasks(User());
	    $_POST = [];//清空超全局变量
	    echo '<br>执行完毕!';
	}
注意URL必须使用英语单引号''填写，不能使用英语双引号"" 
中文双引号“”中文单引号‘’，不能有多余空格，注意末尾逗号！！！

至此，以后每次执行不再从庞大的list列表中搜索你所在学校的名字，节约大量资源。


*答案填写[签到]*

请先完成配置填写中的步骤

适用于签到，下列为默认问题，用于展示样本，暂不支持图片上传。
签到答卷在SubmitForm.php中SignForm方法中

1：今天你的体重是多？
答：10kg以下

2：今天周几？
答：周八

3：近14天你有无吃早餐？
答：否


样卷格式：

	 'extraFieldItems'=> [ '10kg以下',
			       '周八',
			       '否'],

