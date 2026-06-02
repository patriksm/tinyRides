<?php

declare(strict_types=1);

abstract class Model
{
    protected Database $db;
    protected string $table;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? new Database();

        if (empty($this->table)) {
            throw new RuntimeException('Model table is not defined for ' . static::class, 500);
        }
    }

    protected function allowedColumns(): array
    {
        return [];
    }

    protected function assertColumnAllowed(string $column): void
    {
        $column = trim($column);
        if ($column === '') {
            throw new InvalidArgumentException('Column cannot be empty');
        }

        // minimal safety
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            throw new InvalidArgumentException("Invalid column: {$column}");
        }

        $allowed = $this->allowedColumns();
        if (!empty($allowed) && !in_array($column, $allowed, true)) {
            throw new InvalidArgumentException("Column not allowed: {$column}");
        }
    }

    protected function assertOrderBySafe(string $orderBy): string
    {
        $orderBy = trim($orderBy);
        if ($orderBy === '') return 'id DESC';

        // "col ASC|DESC"
        if (!preg_match('/^([a-zA-Z_][a-zA-Z0-9_]*)(\s+(ASC|DESC))?$/i', $orderBy, $m)) {
            throw new InvalidArgumentException('Invalid orderBy');
        }

        $col = $m[1];
        $this->assertColumnAllowed($col);

        $dir = isset($m[3]) ? strtoupper($m[3]) : 'ASC';
        if ($dir !== 'ASC' && $dir !== 'DESC') $dir = 'ASC';

        return $col . ' ' . $dir;
    }

    // =========================================================
    // READ
    // =========================================================

    public function findAll(string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $orderBySafe = $this->assertOrderBySafe($orderBy);

        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBySafe}";
        if ($limit !== null) {
            $limit = max(1, (int)$limit);
            $sql .= " LIMIT {$limit}";
        }

        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    public function findOneBy(string $column, mixed $value): array|false
    {
        $this->assertColumnAllowed($column);

        $this->db->query("SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1");
        $this->db->bind(':value', $value);
        return $this->db->fetch();
    }

    public function findBy(string $column, mixed $value, string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $this->assertColumnAllowed($column);
        $orderBySafe = $this->assertOrderBySafe($orderBy);

        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value ORDER BY {$orderBySafe}";
        if ($limit !== null) {
            $limit = max(1, (int)$limit);
            $sql .= " LIMIT {$limit}";
        }

        $this->db->query($sql);
        $this->db->bind(':value', $value);
        return $this->db->fetchAll();
    }

    /**
     * ✅ FILTER BUILDER
     *
     * $filters examples:
     *  - ['category' => ['RC Cars','Ride-on Cars']]
     *  - ['age_group' => ['0-2 years','2-5 years']]
     *  - ['condition' => ['New','Good']]
     *  - ['price_between' => [10, 100]]
     *  - ['name_like' => 'toy']
     */
    public function findWhere(array $filters = [], string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $where = [];
        $params = [];

        foreach ($filters as $key => $value) {
            $key = (string)$key;

            // LIKE: name_like => 'abc'
            if (str_ends_with($key, '_like')) {
                $col = substr($key, 0, -5);
                $this->assertColumnAllowed($col);

                $ph = ':' . $col . '_like';
                $where[] = "{$col} LIKE {$ph}";
                $params[$ph] = '%' . (string)$value . '%';
                continue;
            }

            // BETWEEN: price_between => [min,max]
            if (str_ends_with($key, '_between')) {
                $col = substr($key, 0, -8);
                $this->assertColumnAllowed($col);

                if (!is_array($value) || count($value) !== 2) continue;

                $phMin = ':' . $col . '_min';
                $phMax = ':' . $col . '_max';

                $where[] = "{$col} BETWEEN {$phMin} AND {$phMax}";
                $params[$phMin] = $value[0];
                $params[$phMax] = $value[1];
                continue;
            }

            // IN: category => ['a','b']
            if (is_array($value)) {
                $this->assertColumnAllowed($key);
                if (count($value) === 0) continue;

                $phs = [];
                foreach (array_values($value) as $i => $v) {
                    $ph = ':' . $key . '_' . $i;
                    $phs[] = $ph;
                    $params[$ph] = $v;
                }

                $where[] = "{$key} IN (" . implode(',', $phs) . ")";
                continue;
            }

            // EQ: condition => 'New'
            $this->assertColumnAllowed($key);
            $ph = ':' . $key;
            $where[] = "{$key} = {$ph}";
            $params[$ph] = $value;
        }

        $orderBySafe = $this->assertOrderBySafe($orderBy);

        $sql = "SELECT * FROM {$this->table}";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY {$orderBySafe}";

        if ($limit !== null) {
            $limit = max(1, (int)$limit);
            $sql .= " LIMIT {$limit}";
        }

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v);
        }

        return $this->db->fetchAll();
    }

    // =========================================================
    // WRITE
    // =========================================================

    public function create(array $data): int|false
    {
        if (empty($data)) {
            throw new InvalidArgumentException('Create data cannot be empty');
        }

        foreach (array_keys($data) as $col) {
            $this->assertColumnAllowed((string)$col);
        }

        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->query($sql);

        foreach ($data as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }

        if ($this->db->execute()) {
            return (int)$this->db->lastInsertedID();
        }

        return false;
    }

    public function update(int $id, array $data): bool
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid id');
        }
        if (empty($data)) {
            throw new InvalidArgumentException('Update data cannot be empty');
        }

        foreach (array_keys($data) as $col) {
            $this->assertColumnAllowed((string)$col);
        }

        $setPart = [];
        foreach ($data as $key => $_) {
            $setPart[] = "{$key} = :{$key}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setPart) . " WHERE id = :id";
        $this->db->query($sql);

        foreach ($data as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        $this->db->bind(':id', $id);

        return (bool)$this->db->execute();
    }

    public function updateByUuid(string $uuid, array $data): bool
    {
        $uuid = trim($uuid);

        if ($uuid === '') {
            throw new InvalidArgumentException('Invalid uuid');
        }

        if (empty($data)) {
            throw new InvalidArgumentException('Update data cannot be empty');
        }

        foreach (array_keys($data) as $col) {
            $this->assertColumnAllowed((string)$col);
        }

        $setPart = [];
        foreach ($data as $key => $_) {
            $setPart[] = "{$key} = :{$key}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setPart) . " WHERE uuid = :uuid";
        $this->db->query($sql);

        foreach ($data as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }

        $this->db->bind(':uuid', $uuid);

        return (bool)$this->db->execute();
    }


    public function delete(int $id): bool
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('Invalid id');
        }

        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);

        return (bool)$this->db->execute();
    }

    public function search(string $column, string $keyword, string $orderBy = 'id DESC', ?int $limit = null): array
    {
        $this->assertColumnAllowed($column);
        $orderBySafe = $this->assertOrderBySafe($orderBy);

        $sql = "SELECT * FROM {$this->table} WHERE {$column} LIKE :keyword ORDER BY {$orderBySafe}";
        if ($limit !== null) {
            $limit = max(1, (int)$limit);
            $sql .= " LIMIT {$limit}";
        }

        $this->db->query($sql);
        $this->db->bind(':keyword', '%' . $keyword . '%');
        return $this->db->fetchAll();
    }

    public function countAll(): int
    {
        $this->db->query("SELECT COUNT(*) as total FROM {$this->table}");
        $row = $this->db->fetch();
        return isset($row['total']) ? (int)$row['total'] : 0;
    }

    public function countBy(string $column, mixed $value): int
    {
        $this->assertColumnAllowed($column);

        $this->db->query("SELECT COUNT(*) as total FROM {$this->table} WHERE {$column} = :value");
        $this->db->bind(':value', $value);
        $row = $this->db->fetch();
        return isset($row['total']) ? (int)$row['total'] : 0;
    }
}
