<?php
/**
 * @file sendmail.php
 * This file contains all the wrapper functions for sending an email to a user.
 *
 * @note This file uses the PHPMailer library.
 *
 * @see <a href="https://github.com/PHPMailer/PHPMailer" target="_blank">PHPMailer, the classic email sending library for PHP, on Github</a>
 *
 * @defgroup mail Mail Functions
 * @brief All functions for sending emails from the server.
 * @{
 */

require "/var/www/includes/PHPMailer/PHPMailerAutoload.php";

/**
 * @global const The directory holding all the HTML email templates.
 */
define('EMAIL_TEMPLATE_DIR', '/var/www/includes/email-templates/');

/**
 * Function for generating a sufficiently unique Message-ID.
 *
 * @return string A sufficiently unique Message-ID uniform resource locator to specify when sending an email.
 *
 * @see Information on <a href="https://en.wikipedia.org/wiki/Message-ID" target="_blank">Message-IDs on Wikipedia</a>
 */
function generateMessageID(){
  return sprintf(
    "<%s-%s@%s>",
    base_convert(microtime(), 10, 36),
    base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
    $_SERVER['SERVER_NAME']
  );
}

/**
 * Sends a password reset email to the given address.
 *
 * @param string $email     User's email address
 * @param string $name      User's name
 * @param string $selector  Selector key to form part of the reset link
 * @param string $validator Validator key to form part of the reset link
 *
 * @return boolean Whether the email sent successfully: `true` if yes, `false` if it failed.
 *
 * @see \ref io-recovery :: validate_user_recovery();
 *
 * @note This does not store the selector and relevent hash in the database, it simply sends the email. The IO must be done separately with \ref io-recovery :: insert_user_recovery().
 *
 * @note Uses a template called `reset-email.html` located in the ::EMAIL_TEMPLATE_DIR.
 */
function sendResetEmail($email,$name,$selector,$validator) {
  $stamp = time();
  $body = file_get_contents(EMAIL_TEMPLATE_DIR.'reset-email.html');
  $body = str_replace('%%NAME%%',htmlspecialchars($name),$body);
  $body = str_replace('%%SELECTOR%%',htmlspecialchars(urlencode($selector)),$body);
  $body = str_replace('%%VALIDATOR%%',htmlspecialchars(urlencode($validator)),$body);

  $headers = 'MIME-Version: 1.0' . PHP_EOL .
    'Content-Type: text/html; charset=UTF-8' . PHP_EOL .
    'From: tktpass Team <no-reply@tktpass.com>' . PHP_EOL .
    'Reply-To: tktpass Team <support@tktpass.com>' . PHP_EOL .
    'To: '. $email . PHP_EOL .
    'Subject: =?utf-8?B?'. base64_encode('Reset password | tktpass') ."?=" . PHP_EOL .
    'Date: ' . date(DATE_RFC2822, $stamp) . PHP_EOL .
    'Envelope-To: '. $email .PHP_EOL .
    'Message-ID: ' . generateMessageID() . PHP_EOL .
    'Content-Transfer-Encoding: 8bit' . PHP_EOL .
    'X-Priority: 3' . PHP_EOL .
    'X-Mailer: PHP/' . phpversion() . PHP_EOL;

  $mail = new PHPMailer;
 
  $mail->isSMTP();                                      // Set mailer to use SMTP
  $mail->Host = 'smtp.gmail.com';                       // Specify main and backup server
  $mail->SMTPAuth = true;                               // Enable SMTP authentication
  $mail->Username = 'contact@tktpass.com';              // SMTP username
  $mail->Password = 'TKTPASS15!';                       // SMTP password
  $mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
  $mail->Port = 587;                                    //Set the SMTP port number - 587 for authenticated TLS
  $mail->setFrom('no-reply@tktpass.com', 'tktpass Team');  //Set who the message is to be sent from
  $mail->addReplyTo('contact@tktpass.com', 'tktpass Team');  //Set an alternative reply-to address
  $mail->addAddress($email);                            // Add a recipient
  $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
  $mail->isHTML(true);                                  // Set email format to HTML
   
  $mail->Subject = 'Reset password | tktpass';
  $mail->Body    = $body;
  $mail->AltBody = "Hi ".$name.",\n\nit seems you requested to reset your password at tktpass.com. If you did, you can reset your password by visiting\n\nhttps://tktpass.com/reset.php?selector=".htmlspecialchars(urlencode($selector))."&validator=".htmlspecialchars(urlencode($validator))."\n\nin your browser.\n\ntktpass Team";
   
  //Read an HTML message body from an external file, convert referenced images to embedded,
  //convert HTML into a basic plain-text alternative body
  $mail->msgHTML($body);
  
  return $mail->send();
}

/**
 * Sends a booking confirmation email (the tickets) to the given user.
 *
 * @param array $user    A user. Complete row data of the relevent user from the `users` table.
 * @param array $event   An event. Complete row data of the relevent event from the `events` table.
 * @param array $order   An array containing all the information regarding this specific order.
 * @param array $tickets An array of arrays, each item in the array contains complete row data from the `tickets` table for each of the newly generated tickets forming part of this order.
 *
 * @return boolean Whether the email sent successfully: `true` if yes, `false` if it failed.
 *
 * @note Uses a template called `booking-email.html` located in the ::EMAIL_TEMPLATE_DIR.
 */
function sendBookingEmail($user,$event,$order,$tickets) {
  $stamp = time();
  $body = file_get_contents(EMAIL_TEMPLATE_DIR.'booking-email.html');
  $body = str_replace('%%TITLE%%',htmlspecialchars($event['name']),$body);
  $start = new DateTime($event['start']);
  $time = $start->format('g');
  if($start->format('i') !== '00')
    $time .= ':'.$start->format('i');
  $time .= $start->format('a');
  $body = str_replace('%%TIME%%',htmlspecialchars($time),$body);
  $body = str_replace('%%DATE%%',htmlspecialchars($start->format('jS F')),$body);
  $body = str_replace('%%NAME%%',htmlspecialchars($user['first_name']),$body);
  $totalQuantity = 0;
  $total = 0;
  foreach($order as $ticket){
    $totalQuantity += $ticket['quantity'];
    $total += $ticket['price']*$ticket['quantity'];
  }
  $body = str_replace('%%PLURAL%%',$totalQuantity>1?'s':'',$body);
  /*function getQrCodeHTML($data){
    include "/var/www/includes/phpqrcode2/qrcode.php";
    $qr = new QRCode();
    $qr->setErrorCorrectLevel(QR_ERROR_CORRECT_LEVEL_L);
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

    return $qr->getHTML($matrixPointSize);
  }*/
  $ids = '';
  foreach ($tickets as $ticket) {
    $ids .= $ticket["id"].',';
  }
  $ids = substr($ids, 0, -1);
  //$body = str_replace('%%QRCODE%%',getQrCodeHTML($ids),$body);
  //$body = str_replace('%%QRCODE%%','<img src="https://api.tktpass.com/qr.php?data='.urlencode($ids).'&img=1" />',$body);
  $body = str_replace('%%TOTAL%%',number_format($total/100,2),$body);
  $rowStart = strpos($body,"<!-- %%BEGIN ITEM ROW%% -->");
  $rowEnd = strpos($body,"<!-- %%END ITEM ROW%% -->",$rowStart)+25;
  $itemRow = substr($body, $rowStart, $rowEnd-$rowStart);
  foreach (array_reverse($order) as $ticket) {
    $insert = $itemRow;
    $insert = str_replace('%%TICKET%%',$ticket['name'],$insert);
    $insert = str_replace('%%QUANTITY%%',$ticket['quantity'],$insert);
    $insert = str_replace('%%SUBTOTAL%%',number_format($ticket['quantity']*$ticket['price']/100,2),$insert);
    $body = substr($body, 0, $rowEnd).$insert.substr($body, $rowEnd);
  }
  $body = substr($body, 0, $rowStart).substr($body, $rowEnd);

  $mail = new PHPMailer;
 
  $mail->isSMTP();                                      // Set mailer to use SMTP
  $mail->Host = 'smtp.gmail.com';                       // Specify main and backup server
  $mail->SMTPAuth = true;                               // Enable SMTP authentication
  $mail->Username = 'contact@tktpass.com';              // SMTP username
  $mail->Password = 'TKTPASS15!';                       // SMTP password
  $mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
  $mail->Port = 587;                                    //Set the SMTP port number - 587 for authenticated TLS
  $mail->setFrom('no-reply@tktpass.com', 'tktpass Team');  //Set who the message is to be sent from
  $mail->addReplyTo('contact@tktpass.com', 'tktpass Team');  //Set an alternative reply-to address
  $mail->addAddress($user['email']);                     // Add a recipient
  $mail->WordWrap = 50;                                 // Set word wrap to 50 characters
  $mail->isHTML(true);                                  // Set email format to HTML

  function getQrCodePNG($data){
    include "/var/www/includes/phpqrcode/qrlib.php";
    
    $errorCorrectionLevel = 'H';
    $matrixPointSize = 4;
    ob_start();
    QRcode::png($data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    $img = ob_get_contents();
    ob_end_clean();

    $QR = imagecreatefromstring($img);
    $logo = imagecreatefrompng('/var/www/img/qr_logo.png');

    $QR_width = imagesx($QR);
    $QR_height = imagesy($QR);
    
    $logo_width = imagesx($logo);
    $logo_height = imagesy($logo);

    $scale = 3.4;

    imagecopyresampled($QR, $logo, $QR_width/2-($QR_width/$scale)/2, $QR_height/2-($QR_height/$scale)/2, 0, 0, $QR_width/$scale, $QR_height/$scale, $logo_width, $logo_height);

    ob_start();
    imagepng($QR);
    $img = ob_get_contents();
    ob_end_clean();
    imagedestroy($QR);
    
    return $img;
  }
  $mail->addStringEmbeddedImage(getQrCodePNG($ids), 'qr', (explode(',',$ids)[0]).'.png', 'base64', 'image/png');
  $body = str_replace('%%QRCODE%%','<img src="cid:qr" />',$body);
   
  $mail->Subject = $event['name'].' | tktpass @ '.(new DateTime())->format('H:i d/m/y');
  $mail->Body    = $body;
  $mail->AltBody = 'You purchased tickets at tktpass.com. View your purchased tickets at http://tktpass.com/mytickets.php';
   
  //Read an HTML message body from an external file, convert referenced images to embedded,
  //convert HTML into a basic plain-text alternative body
  $mail->msgHTML(mb_convert_encoding(
                $body,
                "ISO-8859-1",
                mb_detect_encoding($body, "UTF-8, ISO-8859-1, ISO-8859-15", true)
            ));
   
  return $mail->send();
}

/**
 * Sends an email to us to alert us of an error.
 *
 * @param string $msg A readable message describing the error that has occurred.
 * @param array $data An array containing any useful variables related to the error.
 *
 * @return boolean Whether the email sent successfully: `true` if yes, `false` if it failed.
 */
function sendAdminEmail($msg, $data) {
  $stamp = time();
  $email = "to.alextaylor@outlook.com";
  ob_start();
  var_dump($data);
  $data = ob_get_clean();
  $body = "<p>An error occurred on tktpass.com that you should be aware of!</p><p>".$msg." All the data I have pertaining to this error is below.</p><pre>".$data."</pre>";

  $headers = 'MIME-Version: 1.0' . PHP_EOL .
    'Content-Type: text/html; charset=UTF-8' . PHP_EOL .
    'From: tktpass Team <no-reply@tktpass.com>' . PHP_EOL .
    'Reply-To: tktpass Team <support@tktpass.com>' . PHP_EOL .
    'To: '. $email . PHP_EOL .
    'Subject: =?utf-8?B?'. base64_encode('An Error Occurred!!!1 | tktpass') ."?=" . PHP_EOL .
    'Date: ' . date(DATE_RFC2822, $stamp) . PHP_EOL .
    'Envelope-To: '. $email . PHP_EOL .
    'Message-ID: ' . generateMessageID() . PHP_EOL .
    'Content-Transfer-Encoding: 8bit' . PHP_EOL .
    'X-Priority: 3' . PHP_EOL .
    'X-Mailer: PHP/' . phpversion() . PHP_EOL;

  $mail = new PHPMailer;
 
  $mail->isSMTP();                                      // Set mailer to use SMTP
  $mail->Host = 'smtp.gmail.com';                       // Specify main and backup server
  $mail->SMTPAuth = true;                               // Enable SMTP authentication
  $mail->Username = 'contact@tktpass.com';              // SMTP username
  $mail->Password = 'TKTPASS15!';                       // SMTP password
  $mail->SMTPSecure = 'tls';                            // Enable encryption, 'ssl' also accepted
  $mail->Port = 587;                                    //Set the SMTP port number - 587 for authenticated TLS
  $mail->setFrom('no-reply@tktpass.com', 'tktpass Team');  //Set who the message is to be sent from
  $mail->addReplyTo('contact@tktpass.com', 'tktpass Team');  //Set an alternative reply-to address
  $mail->addAddress($email);                            // Add a recipient
  $mail->WordWrap = 64;                                 // Set word wrap to 64 characters
  $mail->isHTML(true);                                  // Set email format to HTML
   
  $mail->Subject = 'An Error Occurred!!!1 | tktpass';
  $mail->Body    = $body;
  $mail->AltBody = "An error occurred on tktpass.com that you should be aware of! ".$msg." All the data I have pertaining to this error is the following: ".$data;
   
  //Read an HTML message body from an external file, convert referenced images to embedded,
  //convert HTML into a basic plain-text alternative body
  $mail->msgHTML($body);
  
  return $mail->send();
}

/** @}*/