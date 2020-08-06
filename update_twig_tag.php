<?php

define('VIEW_TWIG_PATH', realpath(dirname(__FILE__)) . '/app/view/twig/');
define('DS', '/');

getEventSubscriberFile(VIEW_TWIG_PATH);
function getEventSubscriberFile($dir){
    $currentDir = dir($dir);
    $viewFile = [];
    $num = 0;
    while ($file = $currentDir->read()) {
        if ((is_dir($dir . $file)) and ($file != ".") and ($file != "..")) {
            getEventSubscriberFile($dir . $file . DS);
        } else {
           // var_dump($dir . $file );
            $pathinfo = pathinfo($dir . $file);
            if ( isset($pathinfo['extension'])
                && $pathinfo['extension'] == 'twig'
                && $pathinfo['basename']!='.'
                && $pathinfo['basename']!='..'
                && $pathinfo['basename']!='.gitignore'
            ) {
                $viewFile[] = $filePath = $dir . $file;
                echo $filePath."\n";
                $source = file_get_contents($filePath);
                /*
                preg_match_all('/\{\%\s*verbatim\s*\%\}(.+?)\{\%\s*endverbatim\s*\%\}/sU', $source, $result, PREG_PATTERN_ORDER);
                $forntSource = $result[1];
                echo $forntSource[0] ?? '';
                */
                $exp = '/\{\%\s*verbatim\s*\%\}(.+?)\{\%\s*endverbatim\s*\%\}/sU';
                $result = preg_replace_callback($exp, function ($matches) {
                    return   '{% verbatim %}'.base64_encode($matches[1]).'{% endverbatim %}';
                }, $source);




                $num++;
                if($num==10){
                   //reak;
                }

            }
        }
    }
    // print_r($viewFile);
    $currentDir->close();
}





