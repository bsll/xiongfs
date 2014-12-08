<?php
//error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
ini_set('date.timezone','Asia/Shanghai');
$queueName = 'log_mysql';
$processMsg = 'processMessage';
mq_rec($queueName,$processMsg);

/*
*rabbitMQ - consumer(消费者)
*
* $queueName 队列名称
* $callback 消息处理回调程序名(其返回值为true时，循环执行回调)
*
*/
function mq_rec($queueName,$callback){
    //配置信息
    $params = array(
        'host' =>'127.0.0.1',
        'port' => 5672,
        'login' => 'root',
        'password' => 'cikuutest!',
        'vhost' => '/');
    try{
        //连接rabbitmq服务器
        $cnn = new AMQPConnection($params);
        $cnn->connect();
    }catch(AMQPConnectionException $e){
        return false;
    }

    //创建信息通道
    $ch = new AMQPChannel($cnn);

    //创建队列对象
    $queue = new AMQPQueue($ch);
    $queue->setName($queueName);#队列名称

    //阻塞模式接收消息
    $res = $queue->consume($callback);//服务器主动发送，客户端只负责接收

    $cnn->disconnect();
    return $res;
}
/*
* 消费回调函数
* @处理消息
*
* $envelope 信差对象:获取消息
* $queue 队列对象
*
* return true/false 持续监听/监听一次就中断
* @notice:为true时，循环回调此函数;false时，只执行一次回调
*/
function processMessage($envelope, $queue){
    //global $db;
    //global $write;

    $msg = $envelope->getBody();//获取消息
    $queue->ack($envelope->getDeliveryTag());//手动发送ACK应答

    //处理消息
    $arr=json_decode( $msg, true);
    print_r($arr);

    /*(要实现的具体逻辑写在这里)
    $str= "\n[".date('Y-m-d H:i:s')."] user_id=".$arr['user_id'];
    echo $str;
    */

    return false;
}
//生成日志文件
function createLog($content)
{
    $file = "./logs/mq-".date('Y-m-d').".log";
    $time = date('Y-m-d H:i:s');
    $content = '-- '.$content.'   ['.$time.']'."\r\n";
    $fp = fopen($file,"a");
    fwrite($fp,$content);
    fclose($fp);
}

?>
