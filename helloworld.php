<?php
echo 'Hello, World!';
$homepage = file_get_contents('voorbeeld.json');
//echo $homepage;

$result = $homepage;

$tekst = array();
$details = array();
$array = json_decode($result, true);

// titel bijbelvertaling en disclaimer copyright
$translations = $array['translations'];
$tekst = $translations["nbg21"];


foreach ($array as $key => $jsons) {
    $counterElement = 1;
    foreach ($jsons as $key => $value) {
        if (is_array($value)) {
            $currentDate = false;
            foreach ($value as $item) {
                if (is_array($item)) {
                    $counterText = 1;
                    foreach ($item as $text) {
                        // alleen specfieke bijbelvertaling tonen
                        if ($counterText == 1 and $currentDate == true) {
                            array_push($details, $item["nbg21"]);
                        }
                        $counterText++;
                    }
                } else {
                    if ($counterElement <= 3) {
                        if ((string) date("Y-m-d") == $item) {
                            $currentDate = true;
                        }
                        array_push($details, $item);
                    }
                    $counterElement++;
                }
                
            }
        }
    }
}

$content = $details[3];
$content = $content."<br/>"."<br/>";
$content = $content."<a href=\"" .$details[2]."\" target=\"_blank\" title=\"".$tekst."\" >".$details[1] . "</a>";
$content = $content."<br/>"."<br/>";

echo $content;

?>