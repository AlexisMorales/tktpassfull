<?php
    //Just some setup
    header('Content-Type: text/plain');
    $account = (object) array(
        'email' => 'foo',
        'dob'=>((object)array(
            'day'=>1,
            'month'=>1,
            'year'=>((object)array('century'=>1900,'decade'=>0))
        ))
    );
    var_dump($account);
    echo "\n\n==============\n\n";

    //The functions
    function &getObjRef(&$obj,$prop) {
        return $obj->{$prop};
    }

    function updateObjFromArray(&$obj,$array){
        foreach ($array as $key=>$value) {
            if(!is_array($value))
                $obj->{$key} = $value;
            else{
                $ref = getObjRef($obj,$key);
                updateObjFromArray($ref,$value);
            }
        }
    }

    //Test
    updateObjFromArray($account,array(
        'id' => '123',
        'email' => 'user@domain.com',
        'dob'=>array(
            'day'=>19,
            'month'=>11,
            'year'=>array('century'=>1900,'decade'=>80)
        )
    ));
    var_dump($account);