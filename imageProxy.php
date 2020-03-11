<?php

    $url = 'http://80.60.61.114:80/snap.jpg';

    if ($fp = fopen($url, 'rb')) {
        header("Content-type: image/jpeg");
        fpassthru($fp);
        fclose($fp);
    } else {
        echo 'oops!';
    }
?>