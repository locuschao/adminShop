<?php
/**
 * json数据请求
 * @param $url
 * @param $jsonData
 * @return mixed
 */
if ( ! function_exists('http_post_json') ) {
    function http_post_json($url, $jsonData)
    {
        $h_curl = curl_init();
        curl_setopt($h_curl, CURLOPT_URL, $url);
        curl_setopt($h_curl, CURLOPT_HEADER, 0);
        curl_setopt($h_curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($h_curl, CURLOPT_TIMEOUT, 60);

        if (!empty($jsonData)) {
            curl_setopt($h_curl, CURLOPT_POST, 1);
            curl_setopt($h_curl, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length: ' . strlen($jsonData)]
            );
            curl_setopt($h_curl, CURLOPT_POSTFIELDS, $jsonData);
        }

        $output = curl_exec($h_curl);

        if (curl_errno($h_curl)) {
            return curl_error($h_curl);
        }

        curl_close($h_curl);

        return json_decode($output, true, JSON_UNESCAPED_UNICODE);
    }
}

function http_post($url, $data = [], $header =[], $response = 'json'){
    if(function_exists('curl_init')) {
        $urlArr = parse_url($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if(is_array($header) && !empty($header)){
            $setHeader = array();
            foreach ($header as $k=>$v){
                $setHeader[] = "$k:$v";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $setHeader);
        }

        if (strnatcasecmp($urlArr['scheme'], 'https') == 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        }

        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = curl_exec($ch);

        if(curl_errno($ch)){
            return curl_error($ch);
        }

        $info = curl_getinfo($ch);
        curl_close($ch);

        if (is_array($info) && $info['http_code'] == 200) {
            return $response == 'json' ? json_decode($output, true, JSON_UNESCAPED_UNICODE) : $output;
        } else {
            exit('请求失败（code）：' . $info['http_code']);
        }
    } else {
        throw new Exception('请开启CURL扩展');
    }
}

function http_get($url, $header = [], $response = 'json') {
    if(function_exists('curl_init')) {
        $urlArr = parse_url($url);
        $ch = curl_init();

        if(is_array($header) && !empty($header)){
            $setHeader = array();
            foreach ($header as $k=>$v){
                $setHeader[] = "$k:$v";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $setHeader);
        }

        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);

        if (strnatcasecmp($urlArr['scheme'], 'https') == 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
        }

        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if (is_array($info) && $info['http_code'] == 200) {
            return $response == 'json' ? json_decode($output, true, JSON_UNESCAPED_UNICODE) : $output;
        } else {
            exit('请求失败（code）：' . $info['http_code']);
        }
    } else {
        throw new Exception('请开启CURL扩展');
    }
}