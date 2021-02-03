# SchoolDaysDemo

## 前言
众所周知的原因，几乎各大高校都得使用不同方法进行签到打卡，其中最常见的为今日校园打卡。网络上Python脚本层出不穷但都需
要各种麻烦的依赖。本代码根据[子墨大佬](https://github.com/ZimoLoveShuang/auto-sign)的方案移植成~~世界上最好的语言~~，经过多次版本更替，
移植了其绝大部分功能，免除依赖，支持信息收集与签到及部分学校的模拟登录。

## 部署方法：

1,填写Config.php中User()的信息：
账号	密码	经/纬度[精确到小数点后5位]	学校全称	定位状态    

2,填写Config.php中ToolsKey()其他工具信息：

脚本运行结果推送：
'ServerChanKey' ： Server酱油key
'QmsgKey'：     Qmsg酱key
两者皆用于消息推送，使用哪个填哪个，默认使用Qmsg酱

3,若要更改推送方式，本脚本有2处推送运行结果，都需更改。  
第一处	[用于返回任务异常状态]  
若为签到任务，在SignTask.php中第26行，
若为信息收集，在CollectMessage.php中第25行，
	
	print_r(SendNotice($title, date('Y-m-d H:i:s'), 'Qmsg'));   //Qmsg酱推送
替换为

	print_r(SendNotice($title, date('Y-m-d H:i:s'), 'ServerChan'));   //Server酱推送

第二处	[用于返回答卷提交状态]  
在SubmitForm.php中第16行
	
	print_r(SendNotice($title, date('Y-m-d H:i:s'), 'Qmsg'));   //Qmsg酱推送
同理，替换为

	print_r(SendNotice($title, date('Y-m-d H:i:s'), 'ServerChan'));   //Server酱推送
这样设计可以满足你同时使用不同推送方式A_A

4，BaiDuOCRKey是为不使用子墨API服务器准备的，若使用子墨的API可直接无视。
使用脚本获取cookie有局限性，详情见API服务器篇

5，执行环境：PHP7。若在非腾讯云环境下执行，请将index.php中最后一行

	//main_handler();
替换为

	main_handler();

6，校URL填写：
因为在每次登录时适配不同学校的中查找list获得学校的host需要遍历全国各个
学校直到找到你的学校为止。如果只设置了用户信息，默认只查找并显示你所填写学校的链接。
如果你的学校排名较后，这个过程会消耗大量内存，CPU资源。
先执行一次本脚本，以今日校园学校列表中第一个加入的学校  甘肃工业职业技术学院  `为例`

	https://mobile.campushoy.com/v6/config/guest/tenant/list


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
注意URL`必须`使用英语单引号''填写，`不能`使用`英语双引号""`，
中文双引号“”，中文单引号‘’，`不能`有多余`空格`，注意末尾逗号！！！

至此，以后每次执行不再从庞大的list列表中搜索你所在学校的名字，节约大量资源。


### 签到答卷填写

请先完成配置填写中的步骤

适用于签到，下列为默认问题，用于展示样本，暂不支持图片上传，可适当增删改。
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
			 
### 信息收集答卷填写
适用于信息收集，下列问题用于展示不同问题的答案样本，可适当增删改。
信息收集答卷在SubmitForm.php中CollectForm方法中

	1：你的籍贯是（以户口本为准）	[三级联动，省市区三项选择]  
	答：xx省/xx市/xx区

	2：假期常住地（请具体到门牌号）	[普通文本]  
	答：xx省xx市xx区xx路xx号

	3：最后一科考试的时间		[时间表选择]  
	答：2077-01-01/12:00

	4：你去往目的地的交通方式		[单项选择]  
	A: 公交	B: 地铁	C: 步行	D: 飞机  
	答：['公交']

	5：你昨天吃了哪几餐？		[多项选择]  
	A: 早餐	B: 午餐	C: 晚餐  
	答：['早餐', '午餐', '晚餐']

	6：请上传打卡照片			[图片]  
	答："../images/CSGO.png"  

	7：有无咳嗽，发烧等身体不适？	[判断题]  
	是	否  
	答：['否']

样卷格式：

	 'form'=> [
		    'xx省/xx市/xx区',
		    'xx省xx市xx区xx路xx号',
		    '2077-01-01/12:00',
		    ['公交'],
		    ['早餐', '午餐', '晚餐'],
		    "../images/CSGO.png",
		    ['否']   ],
		    
填写格式注意：
签到暂不支持上传图片，~~因为我懒~~，信息收集的图片上传理论上可行，但需要另行创建与本项目处于同一目录下
的文件夹存放图片，文件夹默认名称images。
答卷所有符号都必须使用英文符号，答案数组除文本外不能有多余空格除最后一
项外每一项末尾都要添加英文逗号，且顺序与收集的问题必须完全一致。


## API服务器篇

由于使用人数多及服务器维护费用高昂，
Config.php中SignAPIS、CollectAPIS的login-api即子墨API会经常连接超时导致无法返回所需cookie
解决办法有如下2种：

### 使用自行架设的服务器，
即将Config.php中SignAPIS、CollectAPIS的

	'login-api'=>'http://你的服务器IP地址:端口号/wisedu-unified-login-api-v1.0/api/login'  
[架设方法](https://github.com/ZimoLoveShuang/wisedu-unified-login-api)

### 使用本脚本自带的SimulationLogin.php
本PHP文件子墨API的部分功能整合在一起，但并未适配全部学校。
先说局限性，在配置填写中，我们在填写学校URL步骤时控制台会输出如下

	Array ( [idsUrl] => https://gipc.campusphere.net/iap [scheme] => https [host] => gipc.campusphere.net )
其中因工程量浩大，只适配了[idsUrl]的后缀为/iap的学校，因此如果你的学校不是这种结尾，请使用第一种办法
或者你可以参考本代码以及上述[架设方法](https://github.com/ZimoLoveShuang/wisedu-unified-login-api)中的逻辑将其他学校也
整合在一起。


### 使用方法
这一步骤嫌麻烦可以先跳过申请key尝试直接执行，大部分学校需要验证码的原因都是短时间登录过于频繁或密码频繁错误。
1，注册百度账号，进入百度智能云控制台

	https://login.bce.baidu.com/?redirect=https%3A%2F%2Fconsole.bce.baidu.com%2F
创建普通版文字识别服务，每天免费5000次那个。

2，填写Config.php中ToolsKey()关于'BaiDuOCRKey'具体信息：

	client_id ：百度OCR API KEY	以及	client_secret ：百度OCR Secret KEY

3，替换代码
若为签到任务，找到SignTask.php中第11，12行
若为信息收集，找到CollectMessage.php同样位置：

	$cookie = SendRequest($apis['login-api'], [], $params);//从子墨服务器获取cookie
	//$cookie = StartLogin();//从本地获取cookie
将其更改为

	//$cookie = SendRequest($apis['login-api'], [], $params);//从子墨服务器获取cookie
	$cookie = StartLogin();//从本地获取cookie

## 简单错误排查
由于今日校园为了防止脚本自带提交任务，大概每隔2-4星期会更改一次链接
这些链接都在Config.php中的签到/信息收集API/获取校园信息URL中
如在大约20年11月更新时，获取校园信息URL中

	'list'=> 'https://mobile.campushoy.com/v6/config/guest/tenant/list'
更改为

	'list'=> 'https://xxx/v6/config/guest/tenant/list'
而签到API中的

	'datas-url'=>'https://'.$url['host'].'/wec-counselor-sign-apps/stu/sign/getStuSignInfosInOneDay'
一般会更改为

	'datas-url'=>'https://'.$url['host'].'/wec-counselor-sign-apps/stu/sign/xxx'
且DES加密的密钥也会跟随版本更新密钥在ToolsHelper.php中

	DESEncrypt($text, $key = 'b3L26XNL')
此处$key = 'b3L26XNL'就是密钥  

模拟登录API问题请阅读API服务器篇


### 简单的错误排查
若你已提交任务且任务时间未结束，会产生以下的错误
若此时执行签到任务，日志/微信推送会显示报错：	自动签到失败，原因是：请求参数SignInstanceWid为空
若此时执行信息收集，日志/微信推送会显示报错：	自动填写信息收失败，原因是：该收集已填写无需再次填写
上述报错是正常情况，请勿在一个任务时间段内多次执行

若有其他报错，签到任务请打开SignTask.php，信息收集任务打开CollectMessage.php
找出全部的	
	print_r(xxx)   	
去掉他们前面的注释，再次执行
观察日志到哪一步时没有结果或异常，如看到['msg']中出现SUCCESS以外的文本
打印的任务表单与你想要填写的内容是否正确。

