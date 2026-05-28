<?php
declare(strict_types=1);
namespace App\Core\Contracts;


interface CrudInterface extends RepositoryInterface
{
    public function count(): int;
}
