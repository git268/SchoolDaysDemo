# SchoolDaysDemo


## 部署方法：

### 1，执行环境：PHP7，建议使用本地或服务器执行，并不建议将代码包部署于云函数上，尤其是腾讯云。
请下载release中最新版的SchoolDaysDemo.zip。


### 2,选择你的版本：
云函数版：`[SchoolDay_v1.74A]`适用于云函数，但近期腾讯云并不稳定，建议使用阿里云云函数。  
不推荐在云函数上使用多用户及内置模拟登陆。  

默认为阿里云云函数，若部署环境为腾讯云云函数，请将index.php中的主函数

	function handler(){
		...
	}
函数名更改为
	
	function main_handler(){
		...
	}
  
服务器版：`[SchoolDay_v1.74B]`可部署于本地或服务器上，更加适用于多用户。自带多用户乱序排序与随机延时，更好模拟真实情况。  
支持图片上传，自带今日校园精简学校列表，加快读取速度。  
`注意：`某些服务器可能因为权限或不明原因导致php文件无法使用相对路径导入其他不同路径的php文件，若你不会修改成绝对路径，请使用云函数版。


### 3,填写`Config.php`中User()的信息：  
使用多用户配置前建议先使用读者自己的账户进行测试，尤其是需要精确定位的签到&查寝经纬度。  
网上的查询到的经纬度精确度往往不够导致任务填写失败，后续步骤会介绍如何填写任务规定的经纬度。
#### 用户信息填写：

	$user = [
		[  'username'=> '账号A', 'password'=>'密码A', 'lon'=> '经度', 'lat'=> '纬度',
                'school'=> '学校全称',  'abnormalReason'=> '在学校', 'address'=>'地址',
                'mode'=> '任务A', 'notice'=> ['type'=>'推送类型A', 'key'=> '推送方式的key']],
             	[  'username'=> '账号B', 'password'=>'密码B', 'lon'=> '经度', 'lat'=> '纬度',
                'school'=> '学校全称',  'abnormalReason'=> '在学校', 'address'=>'地址',
                 'mode'=> '任务B', 'notice'=> ['type'=>'推送类型B', 'key'=> '推送方式的key']],
             	[ 'username'=> '账号C', 'password'=>'密码C', 'lon'=> '经度', 'lat'=> '纬度',
                'school'=> '学校全称',  'abnormalReason'=> '在学校', 'address'=>'地址',
                 'mode'=> '任务C', 'notice'=> ['type'=>'推送类型C', 'key'=> '推送方式的key']]
    ];
可根据次模板适当增减，注意除最后一个用户外末尾的逗号！

#### 选择任务模式：
在`Config.php`中User()方法内，找到每个用户的个人信息：

	[
	...
	'mode'=> '任务A', 
	...],


若为签到任务，替换为：

	'mode'=> 1, 
若为信息收集：

	'mode'=> 2, 
若为辅导员通知：

	'mode'=> 3, 
若为查寝任务：

	'mode'=> 4, 
	
#### 查找任务经纬度：
在签到/查寝任务中往往会对经纬度要求非常苛刻，我们可以根据请求详细任务时的返回查看任务规定好的经纬度。  
在`SignTask.php`签到/`CheckChamber.php`查寝中找到：

	/*
        $address = $res['signPlaceSelected'][0];
        $address = ['地址'=> $address['address'], '经度'=> $address['longitude'], '纬度'=> $address['latitude']];
        echo"<br>当前需要的任务经纬度:<br>";
        print_r($address);
        */
去掉注释，更改成:

	$address = $res['signPlaceSelected'][0];
	$address = ['地址'=> $address['address'], '经度'=> $address['longitude'], '纬度'=> $address['latitude']];
	echo"<br>当前需要的任务经纬度:<br>";
	print_r($address);
	
执行脚本，就能看到控制台输出经纬度，将其填写在`Config.php`的User对应的用户里即可。


#### 脚本支持的推送方式：  
'ServerChanKey' ： Server酱油key  
'QmsgKey'：     Qmsg酱key  
'TGKey'：	telegram bot两个参数[token与聊天id]  
'pushplus'	pushplus的token  
皆用于消息推送，使用哪个填哪个多用户可同时使用不同的推送模式，key在`Config.php`的User中的notice。


	'notice'=> ['type'=>1, 'key'=> 'qmsg的key']//qmsg酱
或

	'notice'=> ['type'=>2, 'key'=> 'serverchan的key']//server酱
或

	'notice'=> ['type'=>3, 'token'=> 'TGtoken', 'chant_id'=> 'TGchatid']//telegram bot
或

	'notice'=> ['type'=>4, 'key'=> 'pushplus的token']//pushplus 
	
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

	暂不明确全自动装填模式是否会影响答卷填充！不过无需担心，
	即使没有自动装填成功也不会提交并返回第x题填充失败，切换至手动填充模式即可。
#### 选择答卷装填模式
签到：在`SignTask.php`中找到：

	if($res['isNeedExtra'] == 1)$form['extraFieldItems'] = FillSignForm($res, $form['extraFieldItems']);//手动填充答卷
若你若需自动装填，则更改为：

	if($res['isNeedExtra'] == 1)$form['extraFieldItems'] = FillSignForm($res, $form['extraFieldItems'], true);//自动填充答卷
  
信息收集：在`MessageCollect.php`中找到：
	
	$form['form'] = FillCollectForm($res['datas']['rows'], $form['form'], [$apis['put-photo'], $apis['get-photo']]);//手动填充答卷
若你若需自动装填，则更改为：

	$form['form'] = FillCollectForm($res['datas']['rows'], $form['form'], [$apis['put-photo'], $apis['get-photo']], true);//自动填充答卷
  
### 签到&查寝答卷填写

请先完成配置填写中的步骤

适用于签到和查寝，由于签到大部分为纯选择题[包括判断题]，即使手动装填也会自动填写正确答案。
若你的签到问卷全为选择题，可以跳过此步骤，下列展示为非选择题情况。  
`查寝`只需填写下方图片路径即可。答卷在`SubmitForm.php`中。

	$form = [
		'signPhotoUrl'=> '图片路径',
		...
		'extraFieldItems'=> [答案],
		...
		],
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

	'signPhotoUrl'=> '图片路径',//图片路径如SaveFile/sample.png
`注意`：图片上传需要读取权限，某些服务器甚至需要填写图片绝对路径。  
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
		],
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
	
	7：请拍摄一张图片？	[图片提交]  
	答：'SaveFile/sample.png'


答卷格式：

	 'form'=> [
		    'xx省/xx市/xx区',
		    'xx省xx市xx区xx路xx号',
		    '2077-01-01/12:00',
		    ['公交'],
		    ['早餐', '午餐', '晚餐'],
		    ['否'],
		    'SaveFile/sample.png' ],
		    
填写格式注意：
签到、查寝图片上传需要另行创建与本项目处于同一目录下
的文件夹存放图片，文件夹默认名称savefile。`请注意：`云函数一般不支持读取/写入，因此要使用上传图片功能请使用服务器或本地执行！
答卷所有符号都必须使用英文符号，答案数组除文本外不能有多余空格除最后一
项外每一项末尾都要添加英文逗号，且顺序与收集的问题必须完全一致。


## API服务器篇

### 使用自带的`SimulationLogin.php`
本PHP文件对各种学校的云端登录进行抓包以获取cookie，但并未适配全部学校。默认使用内置的模拟登陆。

### 使用zimo的模拟登陆服务器[外置API模拟登陆]，
由于使用人数多及服务器维护费用高昂，子墨API会经常连接超时导致无法返回所需cookie
即将`index.php`中

	$serviceapi ='http://你的服务器IP地址:端口号/wisedu-unified-login-api-v1.0/api/login';
[架设方法](https://github.com/ZimoLoveShuang/wisedu-unified-login-api)




### 使用方法
替换代码：
找到`index.php`中的

	Getcookie($user['username'], $user['password']);//本地模拟登录获取cookie
将其更改为

	Getcookie($user['username'], $user['password'], $serviceapi);//外置API模拟登陆获取cookie
    	
执行，若账号密码填写正确，且出现


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
本脚本默认在`index.php`中的main_handler方法找到

	//Timer([5, 25]);//随机延时5-25秒
去掉前面的注释变成

	Timer([5, 25]);//随机延时5-25秒
就能提供随机延时。

### 定时器使用方法
Timer亦提供精确定时功能，使用得当可以准时签到，指~~0秒签到进入封号斗罗排行榜~~。[只能单人使用]。  
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

