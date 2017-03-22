<?php

 //本机ip 192.168.222.128
 //等下 要监听  9091端口

require  'workerman/Autoloader.php';
use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;

// 创建一个Worker监听2345端口，使用http协议通讯
$tcp_worker = new Worker("websocket://10.105.113.175:9091");

// 启动1个进程对外提供服务
$tcp_worker->count = 1;
$clients=[];//保存客户端信息

function syncUsers() //同步登陆用户列表
{
    global $clients;
    $users="users:".json_encode(array_column($clients,"name","ipp"));
    foreach($clients as $ip=>$set)
    {
        $set["conn"]->send($users);
    }
}

$tcp_worker->onMessage = function($connection, $data)
{
    global $clients;
    if(preg_match("/^login:(\w{3,20})/i",$data,$result))//代表是客户端认证
    {
        if(!array_key_exists($connection->getRemoteIp().":".$connection->getRemotePort(),$clients)) //必须是之前没有记录过
        {
           //  //保存所有客户端
            $clients[$connection->getRemoteIp().":".$connection->getRemotePort()]=["ipp"=>$connection->getRemoteIp().":".$connection->getRemotePort()
                ,"name"=>$result[1],"conn"=>$connection];

            $connection->send("notice:success");//代表给客户端一个连接提示
            $connection->send("msg:welcome back ".$result[1]);//代表是发送普通文字消息
          //  echo $connection->getRemoteIp().":".$result[1]." login".PHP_EOL;
            syncUsers();
        }

    }
    else if(preg_match("/^msg:(.*?)/isU",$data,$msgset)) //代表 是客户端发送普通消息
    {
        if(array_key_exists($connection->getRemoteIp(),$clients)) //必须已经认证过得客户端
        {
            echo "get msg :".$msgset[1];
        }
    }
    else if(preg_match("/^chat:\<(.*?)\>:(.*?)/isU",$data,$msgset))
    {
        $ipp=$msgset[1];
        $msg=$msgset[2];
        //$name=$msgset[3];
        if(array_key_exists($ipp,$clients))
        {
            $clients[$ipp]["conn"]->send("msg:".$msg);
            //$clients[$ipp]["conn"]->send("name:".$name);
        }
    }

    $connection->onClose=function ($connection)//客户端主动关闭
    {
        global $clients;
        unset($clients[$connection->getRemoteIp().":".$connection->getRemotePort()]);

        syncUsers();

    };
};
$tcp_worker->onClose=function ($connection) use($clients)
{

};


// 运行worker
Worker::runAll();



