<?php
/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
*/
function tld($domain){
    if(!strpos($domain,'.')) return false;
    $parts = explode('.', $domain);
    return (count($parts) ? ('.' . (count($parts) > 2 && in_array(strlen($parts[count($parts)-2]),array(2,3)) ? $parts[count($parts)-2].'.' : '') . end($parts)) : false);
}
function mxrecordValidate($email){
        list($user, $domain) = explode('@', $email);
        $arr= dns_get_record($domain,DNS_MX);
        if($arr[0]['host']==$domain&&!empty($arr[0]['target'])){
                return $arr[0]['target'];
        }
}
function isValidEmail($email){
    $isValid = true;
    $atIndex = strrpos($email, "@");
    if (is_bool($atIndex) && !$atIndex) {
        $isValid = false;
    } else {
        $domain    = substr($email, $atIndex + 1);
        $local     = substr($email, 0, $atIndex);
        $localLen  = strlen($local);
        $domainLen = strlen($domain);
        $tld = tld($domain);
        if ($localLen < 1 || $localLen > 64) {
            // local part length exceeded
            $isValid = false;
        } else if ($domainLen < 4 || $domainLen > 255) {
            // domain part length exceeded
            $isValid = false;
        } else if (!$tld || strlen($tld)<3) {
            // tld invalid
            $isValid = false;
        } else if ($local[0] == '.' || $local[$localLen - 1] == '.') {
            // local part starts or ends with '.'
            $isValid = false;
        } else if (preg_match('/\\.\\./', $local)) {
            // local part has two consecutive dots
            $isValid = false;
        } else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
            // character not valid in domain part
            $isValid = false;
        } else if (preg_match('/\\.\\./', $domain)) {
            // domain part has two consecutive dots
            $isValid = false;
        } else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/', str_replace("\\\\", "", $local))) {
            // character not valid in local part unless 
            // local part is quoted
            if (!preg_match('/^"(\\\\"|[^"])+"$/', str_replace("\\\\", "", $local))) {
                $isValid = false;
            }
        }
        //if ($isValid && !(checkdnsrr($domain, "MX") || checkdnsrr($domain, "A"))) {
        if($isValid && !mxrecordValidate($email)) {
            // domain not found in DNS
            $isValid = false;
        }
    }
    return $isValid;
}
