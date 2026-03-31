<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWilayah extends Command
{
    protected $signature = 'wilayah:import
                            {--fresh : Drop all existing data before importing}
                            {--source=local : Source: local (database/sql/wilayah.sql) or github}';

    protected $description = 'Import Indonesian administrative region data from cahyadsn/wilayah';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            DB::table('wilayah')->truncate();
            $this->info('Existing data cleared.');
        }

        $source = $this->option('source');

        if ($source === 'local') {
            $result = $this->importFromLocalFile();
        } else {
            $result = $this->importFromGitHub();
        }

        return $result;
    }

    private function importFromLocalFile(): int
    {
        $path = database_path('sql/wilayah.sql');

        if (!file_exists($path)) {
            $this->error("Local file not found: {$path}");
            $this->info('Run with --source=github to download from GitHub, or place wilayah.sql manually.');
            return self::FAILURE;
        }

        $this->info("Reading from local file: {$path}");
        return $this->parseAndImport(file_get_contents($path));
    }

    private function importFromGitHub(): int
    {
        $this->info('Fetching wilayah data from GitHub...');

        try {
            $content = file_get_contents('https://raw.githubusercontent.com/cahyadsn/wilayah/master/db/wilayah.sql');
            if ($content === false) {
                throw new \Exception('Failed to read from GitHub');
            }
        } catch (\Exception $e) {
            $this->error("Network error: {$e->getMessage()}");
            return self::FAILURE;
        }

        $this->info('Downloaded. Parsing...');
        return $this->parseAndImport($content);
    }

    private function parseAndImport(string $sql): int
    {
        // Normalize: remove MySQL comment lines that look like row separators
        $lines = explode("\n", $sql);
        $totalLines = count($lines);
        $this->info("Parsing {$totalLines} lines...");

        $batch = [];
        $batchSize = 500;
        $totalInserted = 0;
        $inInsertBlock = false;

        $bar = $this->output->createProgressBar($totalLines);
        $bar->start();

        DB::beginTransaction();

        try {
            foreach ($lines as $line) {
                $bar->advance();
                $line = trim($line);

                // Skip empty lines
                if ($line === '') continue;

                // Skip standalone INSERT INTO header — we'll collect VALUES below
                if (preg_match('/^INSERT\s+INTO\s+[`"]?wilayah[`"]?\s*\([`"]?kode[`"]?,\s*[`"]?nama[`"]?\)/i', $line)) {
                    $inInsertBlock = true;
                    continue;
                }

                // Detect start of new block
                if ($inInsertBlock && preg_match('/^INSERT\s+INTO/i', $line)) {
                    // Flush previous block
                    if (count($batch) > 0) {
                        DB::table('wilayah')->insertOrIgnore($batch);
                        $totalInserted += count($batch);
                        $batch = [];
                    }
                    $inInsertBlock = true;
                    continue;
                }

                // If not in an insert block, skip
                if (!$inInsertBlock) continue;

                // Extract ('kode','nama') pairs
                if (preg_match_all("/\\('([^']+)','([^']+)'\\)/", $line, $matches, PREG_SET_ORDER)) {
                    foreach ($matches as $m) {
                        $batch[] = [
                            'kode' => trim($m[1]),
                            'nama' => trim($m[2]),
                        ];

                        if (count($batch) >= $batchSize) {
                            DB::table('wilayah')->insertOrIgnore($batch);
                            $totalInserted += count($batch);
                            $batch = [];
                        }
                    }
                }
            }

            // Flush remaining
            if (count($batch) > 0) {
                DB::table('wilayah')->insertOrIgnore($batch);
                $totalInserted += count($batch);
            }

            DB::commit();
            $bar->finish();
            $this->newLine();

            $this->info("Import complete. {$totalInserted} records inserted.");

            $stats = DB::table('wilayah')
                ->selectRaw("LENGTH(kode) as level, COUNT(*) as total")
                ->groupByRaw("LENGTH(kode)")
                ->orderBy('level')
                ->get();

            foreach ($stats as $s) {
                $level = match ((int) $s->level) {
                    2   => 'Provinsi',
                    5   => 'Kabupaten/Kota',
                    8   => 'Kecamatan',
                    13  => 'Desa/Kelurahan',
                    default => "Level {$s->level}",
                };
                $this->line("  {$level}: {$s->total}");
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $bar->finish();
            $this->newLine();
            $this->error("Import failed: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}
