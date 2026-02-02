<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait CategoryTree
{
    /**
     * Returns: root + all descendants ids using MySQL 8 Recursive CTE.
     * No cache.
     *
     * Requirements:
     * - Model using this trait must have:
     *   - table name via $this->getTable()
     *   - columns: id, parent_id
     */
    public static function treeIdsCte(int $rootId, bool $includeSelf = true): array
    {
        if ($rootId <= 0) return [];

        $model = new static();
        $table = $model->getTable();

        $sql = "
            WITH RECURSIVE cats AS (
                SELECT id, parent_id
                FROM {$table}
                WHERE id = ?

                UNION ALL

                SELECT c.id, c.parent_id
                FROM {$table} c
                JOIN cats ON c.parent_id = cats.id
            )
            SELECT id FROM cats
        ";

        $rows = DB::select($sql, [$rootId]);
        $ids  = array_map(fn($r) => (int) $r->id, $rows);

        if (!$includeSelf) {
            $ids = array_values(array_filter($ids, fn($x) => $x !== $rootId));
        }

        return $ids;
    }
}
