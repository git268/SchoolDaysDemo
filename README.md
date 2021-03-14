# SchoolDaysDemo

## 前言
  众所周知的原因，几乎各大高校都得使用不同方法进行签到打卡，其中最常见的为今日校园打卡~~我猜的~~。网络上Python脚本层出不穷但都需
要各种麻烦的依赖。本代码根据[子墨大佬](https://github.com/ZimoLoveShuang/auto-sign)的方案移植成~~世界上最好的编程语言~~，经过多次版本更替，
支持今日校园大部分功能：签到，信息收集，辅导员通知，查寝及大部分学校的模拟登录。  
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

##### 脚本支持的推送方式：  
'ServerChanKey' ： Server酱油key  
'QmsgKey'：     Qmsg酱key  
'TGKey'：	telegram bot两个参数[token与聊天id]  
皆用于消息推送，使用哪个填哪个，key在`Config.php`的User中的notice。


	'notice'=> ['type'=>1, 'key'=> '推送方式的key']//qmsg酱
或

	'notice'=> ['type'=>2, 'key'=> '推送方式的key']//server酱
或

	'notice'=> ['type'=>3, 'key'=> ['token', 'chant_id']]//telegram bot
	

### 4，学校URL填写：
因为在每次登录时适配不同学校的中查找list获得学校的host需要遍历全国各个
学校直到找到你的学校为止。如果只设置了用户信息，默认只查找并显示你所填写学校的链接。
如果你的学校排名较后，这个过程会消耗大量内存，CPU资源。
先执行一次本脚本，以今日校园学校列表中第一个加入的学校  甘肃工业职业技术学院  `为例`

	https://mobile.campushoy.com/v6/config/guest/tenant/list


控制台会输出

	Array ( [idsUrl] => https://gipc.campusphere.net/iap  [host] => gipc.campusphere.net )

找到`index.php`主函数function main_handler()
若你的今日校园任务是签到，
可替换为[必须确保个人信息没有填写错误]

	function main_handler(){
	    $serviceapi = 'http://www.zimo.wiki:8080/wisedu-unified-login-api-v1.0/api/login';//外置API
	    $_POST['school'] = [   
		'idsUrl' => 'https://gipc.campusphere.net/iap',
		'host' => 'gipc.campusphere.net'    ];
	    $user = User();
	    Getcookie($user['username'], $user['password'], $serviceapi);//外置API模拟登陆获取cookie 
	    getSignTasks($user, SignAPIS());   //签到
	    if(empty($_POST['tips']))$_POST['tips'] = '答卷提交成功！';
	    print_r(SendNotice([$_POST['tips'], date('Y-m-d H:i:s')], $user['notice']));//推送方式
	    echo '填写状态：'.$_POST['tips'];
	}
若你的今日校园任务是信息收集，
可将上方的

	getSignTasks($user, SignAPIS());   //签到
更改为

	getCollectTasks($user, CollectAPIS()); //信息收集
若是辅导员通知，更改为
	
	getQueryTasks($user, ConfirmAPIS());//辅导员通知
若是查寝，更改为

	getCheckChamber($user, AttendanceAPIS());//查寝  
	
注意URL`必须`使用英语单引号''填写，`不能`使用`英语双引号""`，
中文双引号“”，中文单引号‘’，`不能`有多余`空格`，注意末尾逗号！！！

若使用telegram bot推送，请使用海外服务器或自备梯子，海外IP能正常完成所有功能。  
其中使用代理需在`ToolsHelper.php`中的SendRequest(...)方法找到：

	//curl_setopt($curl, CURLOPT_PROXY, 'http://127.0.0.1:你的梯子端口号');//TG bot需要使用代理，请自备梯子
替换为：

	curl_setopt($curl, CURLOPT_PROXY, 'http://127.0.0.1:xxxx');//TG bot需要使用代理，请自备梯子  
	
## 答案填写&自动装填机
### 自动装填机
由于今日校园请求任务时会返回历史答卷或在答卷选择题中标注正确答案，为自动填写答卷提供了可能。默认为手动填充，有以下2种模式供选择：  
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
  
### 签到&查寝答卷填写

请先完成配置填写中的步骤

适用于签到和查寝，由于签到大部分为纯选择题[包括判断题]，即使手动装填也会自动填写正确答案。
若你的签到问卷全为选择题，可以跳过此步骤，下列展示为非选择题情况。  
`查寝`只需填写下方图片路径即可

	$form = [
		'signPhotoUrl'=> '图片路径',
		...
		'extraFieldItems'=> [答案],
		...
		]
问卷：

	1：今天你的体重是多？  [纯文本]
	答：599.88KG

	2：今天周几？  	[选择题]
	A:周一	B：周二	C：周四	D：周六
	答：周一

	3：你今天是否喝水？ [判断题]
	 是	 否
	答：是
若只是单纯签到，没有额外问题，则：

	'extraFieldItems'=> [],  
若要上传图片，请将
	
	'signPhotoUrl'=> '',
改成

	'signPhotoUrl'=> '图片路径',//图片路径如savefile/sample.png
该功能需要执行环境有读取权限，云函数一般不支持该功能。若你没有图床也没有适合的执行环境，且辅导员不看提交内容，
可填写今日校园的官网logo图片路径：
	
	'signPhotoUrl'=> 'https://www.campushoy.com/wp-content/uploads/2019/06/cropped-hoy.png',//图片路径如savefile/sample.png


样卷格式：

	 'extraFieldItems'=> ['599.88KG'],  
即只用填写非选择题答案，请按照先后顺序。
			 
### 信息收集答卷填写
适用于信息收集，下列问题用于展示不同问题的答案样本，可适当增删改。
信息收集答卷在`SubmitForm.php`文件的CollectForm()中

	$data = [
		...
		'form'=> [答案],
		...
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

	6：有无咳嗽，发烧等身体不适？	[判断题]  
	是	否  
	答：['否']


答卷格式：

	 'form'=> [
		    'xx省/xx市/xx区',
		    'xx省xx市xx区xx路xx号',
		    '2077-01-01/12:00',
		    ['公交'],
		    ['早餐', '午餐', '晚餐'],
		    ['否']   ],
		    
填写格式注意：
签到、查寝图片上传需要另行创建与本项目处于同一目录下
的文件夹存放图片，文件夹默认名称savefile。`请注意：`云函数一般不支持读取/写入，因此要使用上传图片功能请使用服务器或本地执行！
答卷所有符号都必须使用英文符号，答案数组除文本外不能有多余空格除最后一
项外每一项末尾都要添加英文逗号，且顺序与收集的问题必须完全一致。


## API服务器篇

由于使用人数多及服务器维护费用高昂，子墨API会经常连接超时导致无法返回所需cookie
解决办法有如下2种：

### 使用自行架设的服务器，
即将`index.php`中

	$serviceapi ='http://你的服务器IP地址:端口号/wisedu-unified-login-api-v1.0/api/login';
[架设方法](https://github.com/ZimoLoveShuang/wisedu-unified-login-api)

### 使用本脚本自带的`SimulationLogin.php`
本PHP文件对各种学校的云端登录进行抓包以获取cookie，但并未适配全部学校。
先说局限性，在配置填写中，我们在填写学校URL步骤时控制台会输出如下

	Array ( [idsUrl] => https://gipc.campusphere.net/iap  [host] => gipc.campusphere.net )
适配了[idsUrl]的后缀为iap的全部学校和部分authserver的学校。
或者你可以参考本代码以及上述[架设方法](https://github.com/ZimoLoveShuang/wisedu-unified-login-api)中的逻辑将其他学校也
整合在一起。


### 使用方法
替换代码：
找到`index.php`中的

    	Getcookie($user['username'], $user['password'], $serviceapi);//外置API模拟登陆获取cookie
将其更改为

    	Getcookie($user['username'], $user['password']);//本地模拟登录获取cookie
执行，若账号密码填写正确，且出现

	账号密码错误或不支持该类学校模拟登录。
说明不支持该学校的模拟登录，请改用外置API获取cookie。

## 附加特殊功能及错误排查  

### 反防作弊方法简介
根据观察连续几天的签到排行榜单发现，每天前十几乎都是老面孔，而且是每天准时`[精确到秒]`的签到时间。这种定时签到方式再辅导员眼里实际非常明显。
为此我在`ToolsHelper.php`中加入Timer方法提供随机延时以降低呗=被辅导员发现的可能~~只能在时间显示上没这么明显，后台是否能检测脚本未知~~。
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
要想成为封号斗罗，首先需要在任务发布前20秒左右触发启动脚本定时任务，不能少于5秒防止cookie获取失败，
也不能超过2分钟，因为定时上限只有2分钟，且不能使用随机延时。  
如任务在每天早上07:00:00发布。可在  
`SignTask.php`签到任务/`CollectMessage.php`/信息收集/`QueryNotice.php`辅导员通知/`CheckChamber.php`查寝任务中找到

	echo"<br>第二次请求获取xx任务<br>";
	$datas = ...;//获取任务
	if(...){
		...
	}
改成

	echo"<br>第二次请求获取xx任务<br>";
	$datas = ...;//获取任务
	Timer('07:00:00');//准时7点提交任务，精确到50毫秒
	if(...){
		...
	}
	


### 简单自行排查

遇到报错难以解决，可在签到任务请打开`SignTask.php`，信息收集任务打开`CollectMessage.php`
找出全部的	

	print_r(xxx);
	
去掉他们前面的`//`，再次执行
观察日志到哪一步时没有结果或异常，如看到`['msg']`中出现SUCCESS以外的文本
打印的任务表单与你想要填写的内容是否正确。

## 自己给自己star

