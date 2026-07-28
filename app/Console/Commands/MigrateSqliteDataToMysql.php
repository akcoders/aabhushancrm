<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class MigrateSqliteDataToMysql extends Command
{
    protected $signature = 'crm:migrate-sqlite-data
        {--source= : Absolute path to the SQLite source database}
        {--force : Confirm that all target MySQL tables may be truncated}';

    protected $description = 'Copy all CRM data from the legacy SQLite database into the configured MySQL database';

    public function handle(): int
    {
        if (! $this->option('force')) {
            $this->error('Use --force after confirming that the target MySQL database may be replaced.');

            return self::FAILURE;
        }

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $this->error('The default database connection must be MySQL or MariaDB.');

            return self::FAILURE;
        }

        $sourcePath = $this->option('source') ?: database_path('database.sqlite');
        $sourcePath = realpath($sourcePath) ?: $sourcePath;

        if (! is_file($sourcePath)) {
            $this->error("SQLite source not found: {$sourcePath}");

            return self::FAILURE;
        }

        config([
            'database.connections.sqlite_source' => [
                'driver' => 'sqlite',
                'database' => $sourcePath,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);
        DB::purge('sqlite_source');

        $source = DB::connection('sqlite_source');
        $target = DB::connection();
        $sourceTables = collect($source->select(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
        ))->pluck('name');
        $targetTables = collect($target->select(
            'SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME'
        ))->pluck('name');
        $tables = $sourceTables->intersect($targetTables)->values();

        if ($missing = $sourceTables->diff($targetTables)->implode(', ')) {
            throw new RuntimeException("Target schema is missing tables: {$missing}");
        }

        $this->info("Migrating {$tables->count()} tables from {$sourcePath}");
        $target->statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $table) {
                $target->table($table)->truncate();
            }

            foreach ($tables as $table) {
                $columns = Schema::getColumnListing($table);
                $count = 0;

                $source->table($table)->orderBy($columns[0])->chunk(250, function ($rows) use ($target, $table, $columns, &$count) {
                    $payload = $rows->map(function ($row) use ($columns) {
                        $values = (array) $row;

                        return collect($columns)->mapWithKeys(
                            fn ($column) => [$column => $values[$column] ?? null]
                        )->all();
                    })->all();

                    if ($payload) {
                        $target->table($table)->insert($payload);
                        $count += count($payload);
                    }
                });

                $this->line(sprintf('  %-34s %d', $table, $count));
            }
        } catch (Throwable $exception) {
            $this->error("Migration failed on table {$table}: {$exception->getMessage()}");

            return self::FAILURE;
        } finally {
            $target->statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->newLine();
        $this->info('SQLite data migration completed successfully.');

        return self::SUCCESS;
    }
}
