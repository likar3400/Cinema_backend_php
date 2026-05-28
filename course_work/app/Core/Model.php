<?php
declare(strict_types=1);
namespace App\Core;
abstract class Model
{
    protected Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }


    protected function h(string $v): string
    {
        return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    protected function offset(int $page): int
    {
        return max(0, ($page - 1) * PER_PAGE);
    }
}
