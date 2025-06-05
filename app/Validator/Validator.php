<?php
namespace App\Validator;

require "vendor/autoload.php";

use GUMP;

class Validator
{
    private static $gump;

    public function __construct() {
        $this->gump = new GUMP();
    }

    public static function validateGump(): void
    {
        self::$gump->filter_rules([
            'username'  =>  'required|alpha_numeric|max_len,100',
            'lastname'  =>  'required|alpha_numeric|max_len,100',
            'email'     =>  'required|valid_email',
            'password'  =>  'required|mex_len,8',
        ]);

        self::$gump->set_fields_error_messages([
            'username'  =>  ['required' => 'Fill the username field please'],
            'email'     =>  ['required' => 'Fill the email field please'],
        ]);

        self::$gump->filter_rules([
            'username'  =>  'trim|sanitize_string',
            'lastname'  =>  'trim|sanitize_string',
            'email'     =>  'trim|sanitize_email',
            'password'  =>  'trim',
        ]);

        $valid_data = self::$gump->run($_POST);

        if(self::$gump->errors()) {
            var_dump(self::$gump->get_readable_errors());
            var_dump(self::$gump->get_errors_array());
        }

        var_dump($valid_data);
    }
}