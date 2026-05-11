<?php

declare(strict_types=1);

require_once __DIR__ . '/../../core/Database.php';

final class Usuario
{
    public static function autenticar(string $usuario, string $password): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM usuarios WHERE usuario = :usuario LIMIT 1'
        );
        $stmt->execute(['usuario' => $usuario]);
        $row = $stmt->fetch();

        if ($row && password_verify($password, $row['password'])) {
            return $row;
        }

        return null;
    }
}
