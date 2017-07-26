<?php    
/*
 * PHP QR Code encoder
 *
 * Exemplatory usage
 *
 * PHP QR Code is distributed under LGPL 3
 * Copyright (C) 2010 Dominik Dzienia <deltalab at poczta dot fm>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 */
    
    echo "<h1>PHP QR Code</h1><hr/>";
    
    //set it to writable location, a place for temp generated PNG files
    //$PNG_TEMP_DIR = dirname(__FILE__).DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
    
    //html PNG location prefix
    //$PNG_WEB_DIR = 'temp/';

    include "qrlib.php";    
    
    //ofcourse we need rights to create temp dir
    //if (!file_exists($PNG_TEMP_DIR))
    //    mkdir($PNG_TEMP_DIR);
    
    
    //$filename = $PNG_TEMP_DIR.'test.png';
    
    //processing form input
    //remember to sanitize user input in real-life solution !!!
    $errorCorrectionLevel = 'H';
    if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
        $errorCorrectionLevel = $_REQUEST['level'];    

    $matrixPointSize = 4;
    if (isset($_REQUEST['size']))
        $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);


    if (isset($_REQUEST['data'])) { 
    
        //it's very important!
        if (trim($_REQUEST['data']) == '')
            die('data cannot be empty! <a href="?">back</a>');
            
        // user data
        //$filename = $PNG_TEMP_DIR.'test'.md5($_REQUEST['data'].'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        ob_start();
        QRcode::png($_REQUEST['data'], null, $errorCorrectionLevel, $matrixPointSize, 2);
        $img = ob_get_contents();
        ob_end_clean();

        $QR = imagecreatefromstring($img);
        $logo = imagecreatefrompng('/var/www/img/qr_logo.png');

        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);
        
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);

        $scale = 3.4;
        switch($errorCorrectionLevel){
          case 'L': $scale = 14.3; //7%
                    break;
          case 'M': $scale = 6.7; //15%
                    break;
          case 'Q': $scale = 4.05; //25%
                    break;
          case 'H':
          default:  $scale = 3.4; //30%
                    break;
        }

        imagecopyresampled($QR, $logo, $QR_width/2-($QR_width/$scale)/2, $QR_height/2-($QR_height/$scale)/2, 0, 0, $QR_width/$scale, $QR_height/$scale, $logo_width, $logo_height);

        ob_start();
        imagepng($QR);
        $img = ob_get_contents();
        ob_end_clean();
        imagedestroy($QR);

    } else {
    
        //default data
        echo 'You can provide data in GET parameter: <a href="?data=http://tktpass.com/">like this</a><hr/>';
        ob_start();
        QRcode::png('http://tktpass.com/', null, $errorCorrectionLevel, $matrixPointSize, 2);
        $img = ob_get_contents();
        ob_end_clean();

        $QR = imagecreatefromstring($img);
        $logo = imagecreatefrompng('/var/www/img/qr_logo.png');

        $QR_width = imagesx($QR);
        $QR_height = imagesy($QR);
        
        $logo_width = imagesx($logo);
        $logo_height = imagesy($logo);
            
        imagecopy($QR, $logo, $QR_width/2-$logo_width/2, $QR_height/2-$logo_height/2, 0, 0, $logo_width, $logo_height);

        ob_start();
        imagepng($QR);
        $img = ob_get_contents();
        ob_end_clean();
        imagedestroy($QR);
        
    }
        
    //display generated file
    echo '<img src="data:image/png;base64,'.base64_encode($img).'" /><hr/>';
    
    //config form
    echo '<form action="qr.php" method="get">
        Data:&nbsp;<input name="data" value="'.(isset($_REQUEST['data'])?htmlspecialchars($_REQUEST['data']):'http://tktpass.com/').'" />&nbsp;
        ECC:&nbsp;<select name="level">
            <option value="L"'.(($errorCorrectionLevel=='L')?' selected':'').'>L - smallest</option>
            <option value="M"'.(($errorCorrectionLevel=='M')?' selected':'').'>M</option>
            <option value="Q"'.(($errorCorrectionLevel=='Q')?' selected':'').'>Q</option>
            <option value="H"'.(($errorCorrectionLevel=='H')?' selected':'').'>H - best</option>
        </select>&nbsp;
        Size:&nbsp;<select name="size">';
        
    for($i=1;$i<=10;$i++)
        echo '<option value="'.$i.'"'.(($matrixPointSize==$i)?' selected':'').'>'.$i.'</option>';
        
    echo '</select>&nbsp;
        Return: <input type="radio" name="format" value="img">Image&nbsp;<input type="radio" name="format" value="html">HTML&nbsp;
        <input type="submit" value="GENERATE"></form><hr/>';
        
    // benchmark
    QRtools::timeBenchmark();    

    