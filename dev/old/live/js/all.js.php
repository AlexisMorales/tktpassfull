<?php
$dh = opendir('dev');
$files = array();
while($file = readdir($dh)){
    if(in_array($file,array(".",".."))) continue;
    $files[] = $file;
}
sort($files);
$contents = "";
foreach($files as $file)
    $contents .= "//".$file."\n".file_get_contents('dev/' . $file).";\n\n";
header('Content-Type: application/javascript');
echo $contents;