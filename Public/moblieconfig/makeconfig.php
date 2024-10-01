<?php

// 连接数据库的函数
function connectDatabase($host, $user, $pass, $db) {
    // 创建 MySQLi 实例
    $mysqli = new mysqli($host, $user, $pass, $db);

    // 检查连接
    if ($mysqli->connect_error) {
        die("连接失败: " . $mysqli->connect_error);
    }

    return $mysqli;
}

// 查询数据的函数
function fetchAllFromDatabase($mysqli, $table) {
    // 查询数据
    $sql = "SELECT * FROM $table";
    $result = $mysqli->query($sql);

    // 将结果存入数组
    $results = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    }

    return $results;
}

// 使用示例
$host = 'localhost';
$db = 'wysp001';
$user = 'wysp001';
$pass = 'dragr2iceTIEYd';
$table = 'ch_channel';

// 连接数据库
$mysqli = connectDatabase($host, $user, $pass, $db);

// 查询数据
$results = fetchAllFromDatabase($mysqli, $table);


// 关闭连接
$mysqli->close();

$data = [];
foreach ($results as $value){
    $data[] = $value['channelKey'];
}

    $path = 'itms-services.mobileconfig';
    $res2 = file_get_contents($path);
    
    
    foreach ($data as $value){
        if(!is_dir($value)){
            mkdir($value);
        }
        
        $yj = 'https://zz.zhongzi100.com';
        $url = 'https://casing_zz.tttwer.com?url='.$yj.'/#/nav?pageName='.$value;
        
        $res = str_replace(['@@url@@','@@yj@@'], [$url,$yj], $res2);
        file_put_contents($value.'/'.$path,$res);
    }



?>