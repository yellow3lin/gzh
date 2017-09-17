<?php
/**
  * wechat php test
  */

//define your token
define("TOKEN", "hellwor654ds");
$wechatObj = new wechatCallbackapiTest();
//$wechatObj->valid();
$wechatObj->responseMsg();
class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            header('content-type:text');
            echo $echoStr;
            exit;
        }
    }

    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
                
                $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
                $fromUsername = $postObj->FromUserName;
                $toUsername = $postObj->ToUserName;
                $keyword = trim($postObj->Content);
                $time = time();
                $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
            if ($postObj->MsgType == 'event' && $postObj->Event == 'subscribe') {
                    $msgType = 'text';
                    $contentStr = '欢迎订阅本公众号\n查询天气请回复天气\n人脸识别请上传图片';
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }else if($postObj->MsgType == 'image'){
                    $msgType = 'text';
                    $pic= $postObj->PicUrl;
                    $api="http://apicn.faceplusplus.com/v2/detection/detect?api_key=596d66398a9724f7216cd08523b29d20&api_secret=sTKcDiAXBJYR0JEqXWPUe5yTHpwTg91w&url={$pic}&attribute=gender,age,race";
                    $rs=json_decode(file_get_contents($api),true);
                    $str = '共检测到'.count($rs['face'])."个人,分别是\n";
                    foreach ($rs['face'] as $f) {
                        $str = $str . $f['attribute']['race']['value'] ." ". $f['attribute']['gender']['value']." ".$f['attribute']['age']['value']."\n";
                    }
                    $contentStr = $str;
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;       
                }else if ($postObj->MsgType == 'location') {
                    $msgType = 'text';
                    $api="http://api.map.baidu.com/telematics/v3/local?location={$postObj->Location_Y},{$postObj->Location_X}&keyWord=%E5%AD%A6%E6%A0%A1&output=json&ak=07c8d27bbe614cfeec7383b722c6ccb4";
                    $rs=json_decode(file_get_contents($api),true);
                    for ($contentStr='',$i=0; $i <3&&$i<$rs['count']; $i++) { 
                        $contentStr = $contentStr.$rs['pointList'][$i]['name']." ".$rs['pointList'][$i]['address']." 距离".$rs['pointList'][$i]['distance']."米\n"; 
                    }
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr); 
                    echo $resultStr;     
                }else if(!empty( $keyword ))
                {
                    $msgType = "text";
                    if ($keyword =='天气') {
                        $api="http://api.map.baidu.com/telematics/v3/weather?location=%E6%B1%95%E5%A4%B4&output=json&ak=07c8d27bbe614cfeec7383b722c6ccb4";
                        $rs=json_decode(file_get_contents($api),true);
                        $date=$rs['results'][0]['weather_data'];
                        for ($i=0,$contentStr=''; $i <count($date); $i++) { 
                            $contentStr = $contentStr.$date[$i]['date']." ".$date[$i]['weather']." ".$date[$i]['wind']." ".$date[$i]['temperature']."\n";
                        }
                        $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                        echo $resultStr;
                    }
                
                
                    $contentStr = "Welcome to wechat world!";
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                
                }else{
                    echo "Input something...";
                }

        }else {
            echo "";
            exit;
        }
    }
        
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];    
                
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}

?>