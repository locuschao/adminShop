<?php 
    header('Content-Type:application/json; charset=utf-8');

    echo '<pre>';
    if (!function_exists('parse_file')) {
        function parse_file($file, $line){
           return basename($file). " line {$line}";
        }
    }

    $file_line =  sprintf('in %s', parse_file($file, $line));
    $main = $message;

    $arr = [
        'file_line' => $file_line,
        'main' => $main,
        'error' => '求神拜佛没BUG'
    ];

     print_r($arr);
 ?>
