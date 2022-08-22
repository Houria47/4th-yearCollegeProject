<?php

    function lang($phrase){
        static $lang=array(
            'MESSAGE'=>'أهلا',
            'ADMIN'=>'مدير'
        );
        return $lang[$phrase];
    }