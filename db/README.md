#Phinx 数据库版本管理工具
官网：https://phinx.org/ <br />
文档：https://book.cakephp.org/phinx/0/en/index.html <br />

###常用操作举例
下面所有操作都在项目根目录进行的

###1.创建一个新的迁移文件：
####命令：
vendor/bin/phinx create 类名（可以是表名或者操作名） -c 配置文件
####举例：类名大驼峰
vendor/bin/phinx create Admin -c ./db/config/phinxDev.php <br />
vendor/bin/phinx create AdminLog -c ./db/config/phinxDev.php <br />

当配置中有多个目录的时候需要选择存放的目录，根据当时情况选择即可。


###2.执行迁移：
####命令：
vendor/bin/phinx migrate -e 环境名 -c 配置文件
####举例：
vendor/bin/phinx migrate -e testing -c ./db/config/phinxDev.php <br />
vendor/bin/phinx migrate -c ./db/config/phinxDev.php
####生产环境：
vendor/bin/phinx migrate -c ./db/config/phinx.php

###3.回滚
####命令：
vendor/bin/phinx rollback -e 环境名 -c 配置文件
####生产环境：
vendor/bin/phinx rollback -c ./db/config/phinx.php
####回滚单个版本：
vendor/bin/phinx rollback -e testing -c ./db/config/phinxDev.php <br />
vendor/bin/phinx rollback -c ./db/config/phinxDev.php
####回滚所有：
vendor/bin/phinx rollback -e testing -c ./db/config/phinxDev.php -t 0 <br />
vendor/bin/phinx rollback -c ./db/config/phinxDev.php -t 0
####回滚到指定的版本号（就是文件前面的数字）
vendor/bin/phinx rollback -e testing -c ./db/config/phinxDev.php -t 20220531113703 <br />
vendor/bin/phinx rollback -c ./db/config/phinxDev.php -t 20220531113703

