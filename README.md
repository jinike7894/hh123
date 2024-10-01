##开发git仓库说明
1.现在就一套接口代码一套前端代码<br />
2.因为静态文件没有用OSS，一定要注意转代码的时候复制静态文件。<br />

##php版本扩展
基础运行环境(参考：https://www.easyswoole.com/QuickStart/environment.html) <br />
保证 PHP 版本大于等于 7.4 (php7.4.27) <br />
最新版本easyswoole请使用PHP8.0以上 <br />
保证 Swoole 拓展版本大于等于 4.4.23 (4.8.6) <br />
需要 pcntl 拓展的任意版本 <br />
使用 Linux / FreeBSD / MacOS 这三类操作系统 <br />
使用 Composer 作为依赖管理工具 <br />

##需要额外安装的PHP扩展 <br />
1.swoole<br />
2.xlswriter(https://xlswriter-docs.viest.me/zh-cn/an-zhuang)

pecl install xlswriter 即可安装

docker-php-ext-enable xlswriter 即可启用（非docker环境就修改ini）

##初期需要composer安装的
composer require easyswoole/easyswoole <br />
composer require easyswoole/utility <br />
composer require easyswoole/file-watcher <br />
composer require easyswoole/redis <br />
composer require easyswoole/redis-pool <br />
composer require easyswoole/jwt <br />
composer require easyswoole/orm <br />
composer require easyswoole/swoole-ide-helper <br />
composer require easyswoole/policy <br />
composer require easyswoole/verifycode <br />
composer require easyswoole/task <br />
composer require easyswoole/http-client <br />
composer require easyswoole/queue <br />
composer require endroid/qrcode <br />
composer require robmorgan/phinx <br />
composer require ritaswc/zx-ip-address <br />

当然在有 composer.json 的情况下可以直接 composer install<br />
如果装不了可以删除 composer.lock 或者用上面的命令一个一个来。

上面的安装命令没有规定版本，最新版本需要PHP8.0以上，所以可能安装失败，这个注意一下。

###如果是空项目是需要初始化操作的，后期开发不用，已经安装过了。
php vendor/easyswoole/easyswoole/bin/easyswoole install <br />
vendor/bin/phinx init <br />

最后别忘了执行 <br />
composer dump-autoload <br />

##生成文档
php easyswoole doc --dir=App/HttpController <br />
// 或者执行如下命令 <br />
php vendor/bin/annotation-doc --dir=App/HttpController

##单元测试
https://www.easyswoole.com/Components/phpunit.html <br />
eg: php easyswoole phpunit tests/DbTest.php

##测试环境启动
php easyswoole server start -d <br />
读取的配置为 dev.php

##生产环境启动
php easyswoole server start -mode=produce -d <br />
读取的配置为 produce.php

##nginx
nginx转发swoole端口的时候要注意带上源ip

```
# 跨域配置
add_header Access-Control-Allow-Origin *;
add_header Access-Control-Allow-Headers $http_access_control_request_headers;
add_header Access-Control-Allow-Methods 'GET, POST, OPTIONS';
add_header Access-Control-Expose-Headers 'Date';
add_header Access-Control-Allow-Credentials true;
if ($request_method = OPTIONS) {
    return 200;
}

# 转发配置
location / {
    proxy_http_version 1.1;
    proxy_pass_header  Server;
    proxy_set_header   Host             $http_host;
    #proxy_set_header   Connection      "keep-alive";
    #发起请求的ip地址转发到header
    #注意下面这3个键名不要换
    proxy_set_header   X-Real-IP        $remote_addr;
    proxy_set_header   X-Forwarded-For  $proxy_add_x_forwarded_for;
    proxy_set_header   X-Forwarded-Proto $scheme;
    # 转发Cookie，设置 SameSite
    proxy_cookie_path / "/;";
    if (!-f $request_filename) {
         proxy_pass http://你的es服务ip地址:9501;
    }
}
```

##网宿oss配置
vendor/wangsucs/wcs-sdk-php/src/Wcs/Config.php 配置对应的 网宿key 密钥

##数据库
请至少使用Mysql5.7及以上版本 <br />
数据库版本管理工具使用了phinx <br />
官网：https://phinx.org/ <br />
文档：https://book.cakephp.org/phinx/0/en/index.html <br />
具体查看db目录

##☆☆☆线上初始化或更新数据库☆☆☆
编辑好 ./db/config/phinx.php 文件 <br />
项目根目录执行 vendor/bin/phinx migrate -c ./db/config/phinx.php

##☆☆☆开发初始化或更新数据库☆☆☆
编辑好 ./db/config/phinxDev.php 文件 <br />
项目根目录执行 vendor/bin/phinx migrate -c ./db/config/phinxDev.php

##如何验证已经安装成功
开启服务后访问 ip:port/Test<br />
返回ok就是成功了


##配置文件
dev.php是开发环境配置文件，自己本地改这个文件玩就好了，不要提交git

produce.php是生产环境配置文件，本地开发不用管。



##下面是当前项目的新增部署流程
需要PHP7.4 和mysql5.7（最低）+ redis

建数据库语句
CREATE DATABASE IF NOT EXISTS esdh CHARACTER SET utf8mb4 DEFAULT COLLATE utf8mb4_general_ci;

1.http://gitlab.aqensc.com/daying/esnavigation <br />
这个是后端的接口代码 <br />
拉了代码之后 <br />
修改配置文件 produce.php <br />
需要修改的部分有 <br />
mysql配置  host port user password database <br />
redis配置 <br />

然后再修改 /db/config/phinx.php 配置文件连接数据库 <br />
配置完成后需要执行数据库初始化命令 <br />
vendor/bin/phinx migrate -c ./db/config/phinx.php

线上开启服务的命令 <br />
启动 <br />
php easyswoole server start --mode=produce -d <br />
停止 <br />
php easyswoole server stop <br />
重启 <br />
php easyswoole server restart --mode=produce -d <br />

2.http://gitlab.aqensc.com/daying/esnavigationvue <br />
这个是后台的前端代码 <br />
拉下来后，把 admin.xxx.xx 的域名绑定到 /dist 目录即可。 可以参考nginx的 admin.sioiyeu.conf 文件

3.http://gitlab.aqensc.com/daying/esnavigationvueh5 <br />
这个是前台的前端代码 <br />
拉下来后，把该项目目录下的 dist 文件夹，复制到 后端项目文件夹（esnavigation）的目录下。 <br />
注意：更新前台的前端代码都需要将dist文件夹复制过去。

4.配置 前台域名 到 /esnavigation/dist  ，可以参考 nginx的 www.sioiyeu.conf 文件

5.配置资源目录到 /esnavigation/Public  ，可以参考 nginx的 static.sioiyeu.conf 文件


######################################################
该版本现在分了3个git
esnavigation  PHP
http://gitlab.aqensc.com/daying/esnavigation

esnavigationvue  admin前端
http://gitlab.aqensc.com/daying/esnavigationvue

esnavigationvueh5  h5前端
http://gitlab.aqensc.com/daying/esnavigationvueh5

esnavigationnativeh5  h5原生前端
http://gitlab.aqensc.com/daying/esnavigationnativeh5

#########################################################

##特别注意： <br />
1.后端PHP代码更新后需要重启服务 <br />
2.后台前端代码可以直接git更新 <br />
3.前台前端代码更新后需要将后端代码的esnavigation/dist  目录删除，然后将前台前端代码的 dist 目录复制过去。 <br />