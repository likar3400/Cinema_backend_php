<?php
declare(strict_types=1);
namespace App\Core\Contracts;

interface RepositoryInterface
{
    public function find(int $id): array|false;
    public function delete(int $id): bool;
}
