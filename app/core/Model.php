<?php
/**
 * Kubica Hub — Core: Model base
 * Todos os Models herdam desta classe. Abstrai operações CRUD básicas.
 */

declare(strict_types=1);

namespace App\Core;

abstract class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── CRUD básico ────────────────────────────────────────────────────────

    /**
     * Encontrar por ID.
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        return $this->db->queryOne(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1",
            [$id]
        );
    }

    /**
     * Listar todos os registos.
     * @return array<int, array<string, mixed>>
     */
    public function all(string $orderBy = '', int $limit = 100): array
    {
        $order = $orderBy ? "ORDER BY {$orderBy}" : '';
        $lim   = $limit   ? "LIMIT {$limit}"      : '';
        return $this->db->query("SELECT * FROM `{$this->table}` {$order} {$lim}");
    }

    /**
     * Encontrar com condições WHERE simples.
     * @param array<string, mixed> $conditions
     * @return array<int, array<string, mixed>>
     */
    public function where(array $conditions, string $orderBy = '', int $limit = 100): array
    {
        $clauses = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($conditions)));
        $params  = array_values($conditions);
        $order   = $orderBy ? "ORDER BY {$orderBy}" : '';
        $lim     = $limit   ? "LIMIT {$limit}"      : '';
        return $this->db->query("SELECT * FROM `{$this->table}` WHERE {$clauses} {$order} {$lim}", $params);
    }

    /**
     * Encontrar uma linha com condições WHERE.
     * @param array<string, mixed> $conditions
     * @return array<string, mixed>|null
     */
    public function whereOne(array $conditions): ?array
    {
        $clauses = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($conditions)));
        $params  = array_values($conditions);
        return $this->db->queryOne(
            "SELECT * FROM `{$this->table}` WHERE {$clauses} LIMIT 1",
            $params
        );
    }

    /**
     * Inserir nova linha.
     * @param array<string, mixed> $data
     * @return int ID inserido
     */
    public function create(array $data): int
    {
        $cols     = implode('`, `', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $this->db->execute(
            "INSERT INTO `{$this->table}` (`{$cols}`) VALUES ({$placeholders})",
            array_values($data)
        );
        return $this->db->lastInsertId();
    }

    /**
     * Actualizar linha por ID.
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): int
    {
        $setClause = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
        $params    = array_merge(array_values($data), [$id]);
        return $this->db->execute(
            "UPDATE `{$this->table}` SET {$setClause}, updated_at = NOW() WHERE `{$this->primaryKey}` = ?",
            $params
        );
    }

    /**
     * Apagar linha por ID (soft delete se tiver coluna deleted_at).
     */
    public function delete(int $id): int
    {
        return $this->db->execute(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?",
            [$id]
        );
    }

    /**
     * Contar registos com condições opcionais.
     * @param array<string, mixed> $conditions
     */
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            $result = $this->db->queryOne("SELECT COUNT(*) as total FROM `{$this->table}`");
        } else {
            $clauses = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($conditions)));
            $result  = $this->db->queryOne(
                "SELECT COUNT(*) as total FROM `{$this->table}` WHERE {$clauses}",
                array_values($conditions)
            );
        }
        return (int) ($result['total'] ?? 0);
    }

    /**
     * Verificar se existe registo com condições.
     * @param array<string, mixed> $conditions
     */
    public function exists(array $conditions): bool
    {
        return $this->count($conditions) > 0;
    }
}
