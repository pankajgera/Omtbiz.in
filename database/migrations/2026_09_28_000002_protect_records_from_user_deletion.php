<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // SQLite rebuilds tables to change foreign keys; its foreign-key pragma
    // must run outside a transaction to preserve dependent rows during rebuild.
    public $withinTransaction = false;

    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        foreach (Schema::getTableListing(schemaQualified: false) as $table) {
            foreach (Schema::getForeignKeys($table) as $foreignKey) {
                if ($foreignKey['foreign_table'] !== 'users'
                    || in_array($foreignKey['on_delete'], ['restrict', 'no action'], true)) {
                    continue;
                }

                if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                    // Replace both clauses in one ALTER: no interval without
                    // the constraint, and no dropped constraint if adding fails.
                    $grammar = DB::connection()->getQueryGrammar();
                    $oldName = $grammar->wrap($foreignKey['name']);
                    // MySQL cannot reuse a constraint name within the same ALTER.
                    $newName = $grammar->wrap('user_delete_restrict_'.substr(sha1($table.implode(',', $foreignKey['columns'])), 0, 32));
                    $columns = $grammar->columnize($foreignKey['columns']);
                    $references = $grammar->columnize($foreignKey['foreign_columns']);
                    DB::statement('ALTER TABLE '.$grammar->wrapTable($table)
                        .' DROP FOREIGN KEY '.$oldName.', ADD CONSTRAINT '.$newName
                        .' FOREIGN KEY ('.$columns.') REFERENCES '.$grammar->wrapTable('users')
                        .' ('.$references.') ON DELETE RESTRICT ON UPDATE '.strtoupper($foreignKey['on_update']));
                } else {
                    Schema::table($table, function (Blueprint $blueprint) use ($foreignKey): void {
                        $blueprint->dropForeign($foreignKey['name'] ?? $foreignKey['columns']);
                        $blueprint->foreign($foreignKey['columns'], $foreignKey['name'])
                            ->references($foreignKey['foreign_columns'])->on('users')
                            ->restrictOnDelete()->onUpdate($foreignKey['on_update']);
                    });
                }
            }
        }
    }

    public function down(): void
    {
        // Deliberately retain data-preserving constraints on rollback.
    }
};
