<?php
    if(isset($_REQUEST['format']) && $_REQUEST['format'] === 'img'){
        include "/var/www/includes/phpqrcode/qrlib.php";
        
        $errorCorrectionLevel = 'H';
        if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
            $errorCorrectionLevel = $_REQUEST['level'];

        $matrixPointSize = 4;
        if (isset($_REQUEST['size']))
            $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);

        if (isset($_REQUEST['data'])) {
            //it's very important!
            if (trim($_REQUEST['data']) == '')
                die('data cannot be empty!');
            // user data
            $data = $_REQUEST['data'];
        } else {
            //default data
            $data = 'http://tktpass.com/';
        }

        ob_start();
        QRcode::png($data, null, $errorCorrectionLevel, $matrixPointSize, 2);
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

        header('Content-type: image/png');
        imagepng($QR);
        imagedestroy($QR);
    }
    else if(isset($_REQUEST['format']) && $_REQUEST['format'] === 'html'){
        include "/var/www/includes/phpqrcode2/qrcode.php";

        $qr = new QRCode();
        $errorCorrectionLevel = 'L';
        if (isset($_REQUEST['level']) && in_array($_REQUEST['level'], array('L','M','Q','H')))
            $errorCorrectionLevel = $_REQUEST['level'];
        switch($errorCorrectionLevel){
          case 'M': $errorCorrectionLevel = QR_ERROR_CORRECT_LEVEL_M; //15%
                    break;
          case 'Q':
          case 'H': $errorCorrectionLevel = QR_ERROR_CORRECT_LEVEL_Q; //25%
                    break; //$errorCorrectionLevel = QR_ERROR_CORRECT_LEVEL_H; //30%
          default: $errorCorrectionLevel = QR_ERROR_CORRECT_LEVEL_L; //7%
        }
        $qr->setErrorCorrectLevel($errorCorrectionLevel);

        $data = 'http://tktpass.com/';
        if(isset($_REQUEST['data'])) {
            if(trim($_REQUEST['data']) === '')
                die('data cannot be empty!');
            // user data
            $data = $_REQUEST['data'];
        }
        function estimateTypeNumber($stringLength){
          $estimate = -2.90441 + 0.0000396134*sqrt(-4.31958879*pow(10,8) + 5.0488*pow(10,8)*((int)$stringLength));
          return min(max(ceil($estimate)+1,1),40);
        }
        $qr->setTypeNumber(estimateTypeNumber(strlen($data)));
        $qr->addData($data);
        $qr->make();

        $matrixPointSize = 4;
        if (isset($_REQUEST['size']))
            $matrixPointSize = min(max((int)$_REQUEST['size'], 1), 10);

        $qr->printHTML($matrixPointSize);
    } else
        include "/var/www/includes/phpqrcode/index.php";

    