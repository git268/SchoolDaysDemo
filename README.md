# SchoolDaysDemo

## 前言
  众所周知的原因，几乎各大高校都得使用不同方法进行签到打卡，其中最常见的为今日校园打卡~~我猜的~~。网络上Python脚本层出不穷但都需
要各种麻烦的依赖。本代码根据[子墨大佬](https://github.com/ZimoLoveShuang/auto-sign)的方案移植成~~世界上最好的编程语言~~，经过多次版本更替，
移植了其绝大部分功能，免除依赖，支持信息收集与签到及部分学校的模拟登录。  
  `注意`：请勿利用信息差盈利。本脚本将会持续更新修复Bug、扩展功能并且`永久免费`。

## 部署方法：

### 1，执行环境：PHP7。
请下载release中最新版的SchoolDaysDemo.zip
。若在非云函数下执行，请将index.php中最后一行

	//main_handler();
替换为

	main_handler();
若部署环境为阿里云云函数，请将index.php中的主函数

	function main_handler(){
		...
	}
函数名更改为
	
	function handler(){
		...
	}
若部署环境为腾讯云云函数，不需要修改主函数。

### 2,填写`Config.php`中User()的信息：
账号	密码	经/纬度[精确到小数点后5位]	学校全称	定位状态   

### 3,填写`Config.php`中ToolsKey()其他工具信息：

##### 脚本运行结果推送：  
'ServerChanKey' ： Server酱油key  
'QmsgKey'：     Qmsg酱key  
'TGKey'：	telegram bot两个参数[token与聊天id]  
两者皆用于消息推送，使用哪个填哪个，默认使用Qmsg酱

#### 百度OCR识别key
BaiDuOCRKey是为不使用子墨API服务器准备的，若使用子墨的API可直接无视。
使用脚本获取cookie有局限性，详情见API服务器篇



### 4，学校URL填写：
因为在每次登录时适配不同学校的中查找list获得学校的host需要遍历全国各个
学校直到找到你的学校为止。如果只设置了用户信息，默认只查找并显示你所填写学校的链接。
如果你的学校排名较后，这个过程会消耗大量内存，CPU资源。
先执行一次本脚本，以今日校园学校列表中第一个加入的学校  甘肃工业职业技术学院  `为例`

	https://mobile.campushoy.com/v6/config/guest/tenant/list


控制台会输出

	Array ( [idsUrl] => https://gipc.campusphere.net/iap [scheme] => https [host] => gipc.campusphere.net )

找到`index.php`主函数function main_handler()
若你的今日校园任务是签到，
可替换为[必须确保个人信息没有填写错误]

	function main_handler(){
	    $_POST['school'] = [   
		'idsUrl' => 'https://gipc.campusphere.net/iap',
		'scheme' => 'https',
		'host' => 'gipc.campusphere.net'    ];
	    ToolsKey();
	    getSignTasks(User(), SignAPIS());   //签到
	    if(empty($_POST['Result']))$_POST['Result'] = '答卷提交成功！';
	    print_r(SendNotice([$_POST['Result'], date('Y-m-d H:i:s')], 1));   //Qmsg酱推送
	    echo '<br>执行完毕!';
	}
若你的今日校园任务是信息收集，
可将上方的

	getSignTasks(User(), SignAPIS());   //签到
更改为

	getCollectTasks(User(), CollectAPIS()); //信息收集
	
注意URL`必须`使用英语单引号''填写，`不能`使用`英语双引号""`，
中文双引号“”，中文单引号‘’，`不能`有多余`空格`，注意末尾逗号！！！

6,若要更改推送方式，默认使用Qmsg，其他推送方式如下。  
将`index.php`中的
	
	print_r(SendNotice([$title, date('Y-m-d H:i:s')], 1));   //Qmsg酱推送
替换为Server酱推送

	print_r(SendNotice([$title, date('Y-m-d H:i:s')], 2));   //Server酱推送
或telegram bot 推送
	
	print_r(SendNotice([$title, date('Y-m-d H:i:s')], 3));   //TG bot推送
若使用telegram bot推送，请使用海外服务器或自备梯子，海外IP仍能正常所有功能。  
其中使用代理需在`ToolsHelper.php`中的SendRequest(...)方法找到：

	//curl_setopt($curl, CURLOPT_PROXY, 'http://127.0.0.1:你的梯子端口号');//TG bot需要使用代理，请自备梯子
替换为：

	curl_setopt($curl, CURLOPT_PROXY, 'http://127.0.0.1:xxxx');//TG bot需要使用代理，请自备梯子  
	
## 答案填写&自动装填机
### 自动装填机
由于今日校园请求任务时会返回历史答卷或在答卷选择题中标注正确答案，无非手动填写答卷的自动装填算法应运而生，默认为手动填充，有以下2种模式供选择：  
`全自动装填模式`：根据历史答卷重新填充，无需填写答卷。因此请确认历史答卷有无填写错误。若问卷有变，使用该模式不会提交答卷并返回第x题填充失败。  
`手动装填模式`：需要在本脚本手动填写答卷，可灵活根据问卷填充答卷。需注意的是在签到中手动装填也会自动根据正确答案自动填写选择题，[并非根据历史答卷]。
且签到中绝大多数为纯选择题，因此签到的手动装填即使频繁更改问卷只要不发布文本问题也可以自适应填充答卷。  

#### 选择答卷装填模式
签到：在`SignTask.php`中找到：

	if($res['isNeedExtra'] == 1)$form['extraFieldItems'] = FillSignForm($res, $form['extraFieldItems']);//手动填充答卷
若你若需自动装填，则更改为：

	if($res['isNeedExtra'] == 1)$form['extraFieldItems'] = FillSignForm($res, $form['extraFieldItems'], true);//自动填充答卷
  
信息收集：在`CollectMessage.php`中找到：
	
	$form['form'] = FillCollectForm($res['datas']['rows'], $form['form']);//手动填充答卷
若你若需自动装填，则更改为：

	$form['form'] = FillCollectForm($res['datas']['rows'], $form['form'], true);//自动填充答卷
  
### 签到答卷填写

请先完成配置填写中的步骤

适用于签到，图片请在`Config.php`的User()中填写图片路径。由于签到大部分为纯选择题[包括判断题]，默认使用自动填写正常答案功能。
若你的签到问卷全为选择题，可以跳过此步骤，下列展示为非选择题情况。

	$form = [
		...
		'extraFieldItems'=> [答案],
		]
问卷：

	1：今天你的体重是多？  [纯文本]
	答：599.88KG

	2：今天周几？  	[选择题]
	A:周一	B：周二	C：周四	D：周六
	答：周一

	3：请上传你小时候的照片？ [图片]
	答：'savefile/sample.png'


样卷格式：

	 'extraFieldItems'=> [	'599.88KG', 
	 			'savefile/sample.png'	],
即只用填写非选择题答案，请按照先后顺序。
			 
### 信息收集答卷填写
适用于信息收集，下列问题用于展示不同问题的答案样本，可适当增删改。
信息收集答卷在`SubmitForm.php`文件的CollectForm()中

	$data = [
		...
		'form'=> [答案]
		]
问卷：

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

	6：请上传打卡照片		[图片]  
	答：'savefile/sample.png',  

	7：有无咳嗽，发烧等身体不适？	[判断题]  
	是	否  
	答：['否']

答卷格式：

	 'form'=> [
		    'xx省/xx市/xx区',
		    'xx省xx市xx区xx路xx号',
		    '2077-01-01/12:00',
		    ['公交'],
		    ['早餐', '午餐', '晚餐'],
		    'savefile/sample.png',
		    ['否']   ],
		    
填写格式注意：
签到、信息收集的图片上传理论上可行，但需要另行创建与本项目处于同一目录下
的文件夹存放图片，文件夹默认名称savefile。`请注意：`云函数一般不支持读取/写入，因此要使用上传图片功能请使用服务器或本地执行！
答卷所有符号都必须使用英文符号，答案数组除文本外不能有多余空格除最后一
项外每一项末尾都要添加英文逗号，且顺序与收集的问题必须完全一致。


## API服务器篇

由于使用人数多及服务器维护费用高昂，
`Config.php`中SignAPIS、CollectAPIS的login-api即子墨API会经常连接超时导致无法返回所需cookie
解决办法有如下2种：

### 使用自行架设的服务器，
即将`Config.php`中SignAPIS、CollectAPIS的

	'login-api'=>'http://你的服务器IP地址:端口号/wisedu-unified-login-api-v1.0/api/login'  
[架设方法](https://github.com/ZimoLoveShuang/wisedu-unified-login-api)

### 使用本脚本自带的`SimulationLogin.php`
本PHP文件子墨API的部分功能整合在一起，但并未适配全部学校。
先说局限性，在配置填写中，我们在填写学校URL步骤时控制台会输出如下

	Array ( [idsUrl] => https://gipc.campusphere.net/iap [scheme] => https [host] => gipc.campusphere.net )
其中因工程量浩大，只适配了[idsUrl]的后缀为/iap的学校，因此如果你的学校不是这种结尾，请使用第一种办法
或者你可以参考本代码以及上述[架设方法](https://github.com/ZimoLoveShuang/wisedu-unified-login-api)中的逻辑将其他学校也
整合在一起。


### 使用方法
这一步骤嫌麻烦可以先跳过申请key尝试直接做第三步替换代码，大部分学校需要验证码的原因都是短时间登录过于频繁或密码频繁错误。  
1，注册百度账号，进入百度智能云控制台

	https://login.bce.baidu.com/?redirect=https%3A%2F%2Fconsole.bce.baidu.com%2F
创建普通版文字识别服务，每天免费5000次那个。

2，填写`Config.php`中ToolsKey()关于'BaiDuOCRKey'具体信息：

	client_id ：百度OCR API KEY	以及	client_secret ：百度OCR Secret KEY

3，替换代码
找到`SignTask.php`、`CollectMessage.php`中的

    	$cookie = GetCookie($user, [$apis['login-api'], $params]);//从子墨服务器获取cookie
    	//$cookie = GetCookie($user, []);//从本地获取cookie
将其更改为

    	//$cookie = GetCookie($user, [$apis['login-api'], $params]);//从子墨服务器获取cookie
    	$cookie = GetCookie($user, []);//从本地获取cookie

## 附加特殊功能及错误排查  

### 反防作弊方法简介
根据观察连续几天的签到排行榜单发现，每天前十几乎都是老面孔，而且是每天准时`[精确到秒]`的签到时间。这种定时签到方式再辅导员眼里实际非常明显。
为此我在`ToolsHelper.php`中加入Timer方法提供随机延时以降低呗辅导员发现的可能~~只能在时间显示上没这么明显，后台是否能检测脚本未知~~。
其中Timer([$min, $max])有两个通俗易懂的参数：最小延迟时间，最大延迟时间。
#### 使用方法
在`SignTask.php`[签到]/`CollectMessage.php`[信息收集]的执行路径上，最好是第一个方法的第一行添加，如想延时5-30秒可以这样填写：

	function getSignTasks / getCollectTasks (...){
		Timer([5, 30]);//随机延时5-30秒
		...
	}
#### 注意事项
由于启用随机延时，请注意你的云函数执行时间上限，并且如腾讯云，阿里云的云函数会对执行时长作为计费标准之一，请留意最坏执行时长及使用频率。
若为本地执行或服务器执行，则要留意`index.php`上方

	set_time_limit(150);//设置执行时间上限(150秒)
若需紧急执行，请先在所有调用的`Timer(..);`前方更改为

	//Timer(...);

### 定时器使用方法
Timer亦提供精确定时功能，使用得当可以准时签到，指~~0秒签到进入封号斗罗排行榜~~。
要想成为封号斗罗，首先要提前20秒左右触发启动脚本定时任务，不能少于5秒防止cookie获取失败，
也不能超过2分钟，因为定时上限只有2分钟，且不能使用随机延时。  
如任务在每天早上07:00:00发布。可在`SubmitForm.php`中的

	echo"<br>答卷结果<br>";
的下一行添加如下代码：

	Timer('07:00:00');//准时7点提交任务，精确到50毫秒
且在`SignTask.php`签到任务/`CollectMessage.php`信息收集中找到

	echo"<br>第二次请求获取xx任务<br>";
	$datas = ...;//获取任务
	//print_r($datas);
	if(...){
		...
	}
将其中的

	if(...)
改成

	if(true)
### 优化运行速度
在自动签到/信息收集过程中，耗时最长的是获取cookie的过程，大概2-4秒不等。
因此想要加速脚本运行，减少资源占用，可在`SignTask.php`签到任务/`CollectMessage.php`信息收集中找到

    	$cookie = GetCookie($user, [$apis['login-api'], $params]);//从子墨服务器获取cookie
或
    	
	$cookie = GetCookie($user, []);//从本地获取cookie
具体看你使用何种方式获取cookie，前面没有`//`的就是你使用的方式。  
将其在末尾添加一个参数如：

    	$cookie = GetCookie($user, [$apis['login-api'], $params], 1);//从子墨服务器获取cookie
或
    	
	$cookie = GetCookie($user, [], 1);//从本地获取cookie
且将`SignTask.php`签到任务/`CollectMessage.php`信息收集中找到

	//if(empty($data))$cookie = GetCookie($user, [$apis['login-api'], $params], 1);//强制更新cookie
去掉前面的`//`，更改为：

	if(empty($data))$cookie = GetCookie($user, [$apis['login-api'], $params], 1);//强制更新cookie
这样会在第一次运行后在savefile里生成一个当前账号命名的txt文件用于保存cookie，此后登陆时就从已保存的
txt文件中提取cookie，能将运行脚本速度缩短到0.8秒内。
#### 注意：
使用本地保存cookie功能与上传图片类似，需要支持读取/写入的环境，一般云函数不支持该功能，请在本地或服务器使用。

### 随机定位
上述定时器功能能助你进入封号斗罗排行榜，下面的随机定位功能能让你环游世界，指定位层面。
该功能会随机生成一个北半球经纬度，有一定风险，你的辅导员可能很快就到你家门口，请勿滥用。
#### 使用方法
在`Config.php`的User()中：

	$user = [   'username'=> '账号', 'password'=>'密码', 'address'=>'地址','email'=> 'None',
        'school'=> '', 'lon'=> '经度', 'lat'=> '纬度', 'abnormalReason'=> '在学校' ];
	/*
	$coordinate = RandomCoordinate();//获取随机坐标
	$user['lon'] = $coordinate['lon'];
	$user['lat'] = $coordinate['lat'];
	*/
	return $user;
更改为:

	$user = [   'username'=> '账号', 'password'=>'密码', 'address'=>'地址','email'=> 'None',
        'school'=> '', 'lon'=> '经度', 'lat'=> '纬度', 'abnormalReason'=> '在学校' ];
	$coordinate = RandomCoordinate();//获取随机坐标
	$user['lon'] = $coordinate['lon'];
	$user['lat'] = $coordinate['lat'];
	return $user;
#### 注意
使用后请注意人生安全。

### 今日校园反脚本案例介绍
由于今日校园为了防止脚本自带提交任务，大概每隔2-4星期会更改一次链接  
这些链接都在`Config.php`中的签到/信息收集API/获取校园信息URL中
如在大约20年11月更新时，获取校园信息URL中

	'list'=> 'https://mobile.campushoy.com/v6/config/guest/tenant/list'
更改为

	'list'=> 'https://xxx/v6/config/guest/tenant/list'
而签到API中的

	'datas-url'=>'https://'.$url['host'].'/wec-counselor-sign-apps/stu/sign/getStuSignInfosInOneDay'
一般会更改为

	'datas-url'=>'https://'.$url['host'].'/wec-counselor-sign-apps/stu/sign/xxx'
且DES加密的密钥也会跟随版本更新，密钥在`ToolsHelper.php`中

	DESEncrypt($text, $key = 'b3L26XNL')
此处`$key = 'b3L26XNL'`就是密钥  

模拟登录API问题请阅读API服务器篇

### 简单自行排查

遇到报错难以解决，可在签到任务请打开`SignTask.php`，信息收集任务打开`CollectMessage.php`
找出全部的	

	print_r(xxx);
	
去掉他们前面的`//`，再次执行
观察日志到哪一步时没有结果或异常，如看到`['msg']`中出现SUCCESS以外的文本
打印的任务表单与你想要填写的内容是否正确。

## 自己给自己star

