<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CopyMysqlToPostgres extends Command
{
    protected $signature = 'nikahstudio:copy-mysql-to-postgres
        {--execute : Perform the data copy. Without this option, only run a preflight report.}
        {--chunk=250 : Number of rows copied per batch.}';

    protected $description = 'Safely copy NikahStudio data from the existing MySQL database into the migrated PostgreSQL database.';

    /**
     * Tables are ordered so parent records are copied before their dependants.
     * The migrations table is intentionally excluded because Laravel creates
     * the target migration history when php artisan migrate is run on PostgreSQL.
     */
    private array $tables = [
        'users',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'sessions',
        'passkeys',
        'plans',
        'plan_features',
        'subscriptions',
        'templates',
        'invitations',
        'invitation_events',
        'media_assets',
        'invitation_media',
        'invitation_guests',
        'rsvps',
        'guestbook_messages',
        'admin_activity_logs',
    ];

    public function handle(): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));

        try {
            $source = DB::connection('mysql_source');
            $target = DB::connection('pgsql');

            $source->getPdo();
            $target->getPdo();

            $this->info('MySQL source: '.$source->getDatabaseName());
            $this->info('PostgreSQL target: '.$target->getDatabaseName());

            if (! $source->getSchemaBuilder()->hasTable('migrations') || ! $target->getSchemaBuilder()->hasTable('migrations')) {
                $this->error('Migration history table is missing. Run the PostgreSQL migrations first.');

                return self::FAILURE;
            }

            $sourceMigrations = $source->table('migrations')->orderBy('migration')->get(['migration', 'batch']);
            $targetMigrations = $target->table('migrations')->orderBy('migration')->pluck('migration')->all();
            $sourceMigrationNames = $sourceMigrations->pluck('migration')->all();

            if ($sourceMigrationNames !== $targetMigrations) {
                $this->error('The MySQL and PostgreSQL migration histories do not match.');
                $this->line('MySQL migrations: '.count($sourceMigrationNames));
                $this->line('PostgreSQL migrations: '.count($targetMigrations));
                $this->warn('Do not copy data yet. Resolve the migration mismatch first.');

                return self::FAILURE;
            }

            $this->newLine();
            $this->info('Preflight table counts:');
            $hasTargetData = false;

            foreach ($this->tables as $table) {
                if (! $source->getSchemaBuilder()->hasTable($table) || ! $target->getSchemaBuilder()->hasTable($table)) {
                    $this->error("Required table is missing in source or target: {$table}");

                    return self::FAILURE;
                }

                $sourceCount = $source->table($table)->count();
                $targetCount = $target->table($table)->count();
                $this->line(sprintf('%-26s MySQL: %6d | PostgreSQL: %6d', $table, $sourceCount, $targetCount));

                if ($targetCount > 0) {
                    $hasTargetData = true;
                }

                $sourceColumns = $source->getSchemaBuilder()->getColumnListing($table);
                $targetColumns = $target->getSchemaBuilder()->getColumnListing($table);
                sort($sourceColumns);
                sort($targetColumns);

                if ($sourceColumns !== $targetColumns) {
                    $this->error("Column definitions differ for table {$table}.");
                    $this->line('MySQL: '.implode(', ', $sourceColumns));
                    $this->line('PostgreSQL: '.implode(', ', $targetColumns));

                    return self::FAILURE;
                }
            }

            if ($hasTargetData) {
                $this->error('At least one PostgreSQL application table already contains data. Copy cancelled to prevent overwriting records.');

                return self::FAILURE;
            }

            if (! $this->option('execute')) {
                $this->newLine();
                $this->info('Preflight passed. No data has been changed.');
                $this->comment('After making a MySQL backup, run: php artisan nikahstudio:copy-mysql-to-postgres --execute');

                return self::SUCCESS;
            }

            if (! $this->confirm(
    'No backup will be created. The MySQL source will remain unchanged. Continue copying data to PostgreSQL?',
    false
)) {
    $this->warn('Copy cancelled. No data was changed.');

    return self::SUCCESS;
}

            $target->transaction(function () use ($source, $target, $chunkSize, $sourceMigrations): void {
                foreach ($this->tables as $table) {
                    $targetColumns = $target->getSchemaBuilder()->getColumnListing($table);
                    $copied = 0;

                    $source->table($table)
                        ->orderBy($this->orderColumn($table))
                        ->chunk($chunkSize, function ($rows) use ($target, $table, $targetColumns, &$copied): void {
                            $batch = [];
                            $allowed = array_flip($targetColumns);

                            foreach ($rows as $row) {
                                $batch[] = array_intersect_key((array) $row, $allowed);
                            }

                            if ($batch !== []) {
                                $target->table($table)->insert($batch);
                                $copied += count($batch);
                            }
                        });

                    if (in_array('id', $targetColumns, true)) {
                        $this->syncSequence($target, $table);
                    }

                    $this->info("Copied {$table}: {$copied} rows");
                }

                // Keep the original batch numbers while retaining the migration
                // records generated by Laravel on the PostgreSQL database.
                foreach ($sourceMigrations as $migration) {
                    $target->table('migrations')
                        ->where('migration', $migration->migration)
                        ->update(['batch' => $migration->batch]);
                }
            });

            $this->newLine();
            $this->info('Data copy completed successfully.');
            $this->warn('Keep the original MySQL database and its backup until the application has been fully tested.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('Migration copy failed. PostgreSQL transaction was rolled back where possible.');
            $this->line($exception->getMessage());

            return self::FAILURE;
        }
    }

    private function orderColumn(string $table): string
    {
        return in_array($table, ['cache', 'cache_locks'], true)
            ? 'key'
            : (in_array($table, ['password_reset_tokens'], true) ? 'email' : 'id');
    }

    private function syncSequence($connection, string $table): void
    {
        $maxId = $connection->table($table)->max('id');

        if ($maxId === null) {
            return;
        }

        $sequence = $connection->selectOne(
            "SELECT pg_get_serial_sequence(?, 'id') AS sequence",
            ['public.'.$table],
        )->sequence ?? null;

        if ($sequence) {
            $connection->selectOne('SELECT setval(?::regclass, ?, true)', [$sequence, (int) $maxId]);
        }
    }
}
