<?php
declare(strict_types=1);

namespace app\Models;

use PDO;

class InformationSales
{
    private PDO $conn;

    public function __construct(PDO $conn) 
    {
        $this->conn = $conn;
    }
}