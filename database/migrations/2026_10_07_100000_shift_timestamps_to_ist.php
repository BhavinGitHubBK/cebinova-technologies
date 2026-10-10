<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->shift(330);
    }

    public function down(): void
    {
        $this->shift(-330);
    }

    private function shift(int $minutes): void
    {
        $skip = ['migrations', 'cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];
        $driver = Schema::getConnection()->getDriverName();
        $grammar = Schema::getConnection()->getQueryGrammar();

        foreach (Schema::getTables() as $table) {
            $name = $table['name'];
            if (in_array($name, $skip, true)) {
                continue;
            }

            foreach (Schema::getColumns($name) as $column) {
                $type = strtolower((string) ($column['type_name'] ?? $column['type'] ?? ''));
                if (! in_array($type, ['datetime', 'timestamp'], true)) {
                    continue;
                }

                $wrappedTable = $grammar->wrapTable($name);
                $wrappedColumn = $grammar->wrap($column['name']);

                if ($driver === 'sqlite') {
                    $sign = $minutes >= 0 ? '+' : '';
                    DB::statement("UPDATE {$wrappedTable} SET {$wrappedColumn} = datetime({$wrappedColumn}, '{$sign}{$minutes} minutes') WHERE {$wrappedColumn} IS NOT NULL");
                } else {
                    $interval = abs($minutes);
                    $expression = $minutes >= 0
                        ? "DATE_ADD({$wrappedColumn}, INTERVAL {$interval} MINUTE)"
                        : "DATE_SUB({$wrappedColumn}, INTERVAL {$interval} MINUTE)";
                    DB::statement("UPDATE {$wrappedTable} SET {$wrappedColumn} = {$expression} WHERE {$wrappedColumn} IS NOT NULL");
                }
            }
        }
    }
};
