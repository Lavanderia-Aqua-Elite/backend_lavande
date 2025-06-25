<?php
declare(strict_types=1);

namespace app\Hooks;

class ImageHook
{
    public function __construct()
    {
        
    }

    function image_component($img) {
    
        foreach($_FILES["userfile"]["error"] as $key => $error) {
            $tmp_name = $_FILES["userfile"]["tmp_name"][$key];
            $name = $_FILES["userfile"]["name"][$key];

            $exception = basename($tmp_name, $name);
            
            $move = move_uploaded_file($tmp_name, $name);
        }
    }
}