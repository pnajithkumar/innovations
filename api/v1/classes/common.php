<?php
class Common{
    function __construct(){
        
    }
    
    function getPostData(){
        return json_decode(file_get_contents("php://input"),1);
    }
}