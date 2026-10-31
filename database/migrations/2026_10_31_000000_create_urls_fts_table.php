<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Full text index of the url names, for the search (a LIKE '%...%' reads every url).
     * Kept up to date by triggers, accents and case are ignored
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement("CREATE VIRTUAL TABLE urls_fts USING fts5(name, content='urls', content_rowid='id', tokenize='unicode61 remove_diacritics 2')");
        DB::statement("CREATE TRIGGER urls_fts_insert AFTER INSERT ON urls BEGIN
            INSERT INTO urls_fts(rowid, name) VALUES (new.id, new.name);
        END");
        DB::statement("CREATE TRIGGER urls_fts_delete AFTER DELETE ON urls BEGIN
            INSERT INTO urls_fts(urls_fts, rowid, name) VALUES ('delete', old.id, old.name);
        END");
        DB::statement("CREATE TRIGGER urls_fts_update AFTER UPDATE OF name ON urls BEGIN
            INSERT INTO urls_fts(urls_fts, rowid, name) VALUES ('delete', old.id, old.name);
            INSERT INTO urls_fts(rowid, name) VALUES (new.id, new.name);
        END");
        DB::statement("INSERT INTO urls_fts(urls_fts) VALUES ('rebuild')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement("DROP TRIGGER IF EXISTS urls_fts_insert");
        DB::statement("DROP TRIGGER IF EXISTS urls_fts_delete");
        DB::statement("DROP TRIGGER IF EXISTS urls_fts_update");
        DB::statement("DROP TABLE IF EXISTS urls_fts");
    }
};
