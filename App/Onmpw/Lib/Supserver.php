<?php
namespace Lib;

use Inter\Webservice;
class Supserver implements Webservice{
    public function sum($a,$b){
        return $a+$b;
    }
    public function getstr($str){
        return $str;
    }
}