<?php

namespace App\Console\Commands;

use App\Services\CasafariService;
use Illuminate\Console\Command;

class SyncCasafariProperties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'casafari:sync
                            {--country= : Filter by country code (e.g., PT, ES)}
                            {--city= : Filter by city name}
                            {--type= : Filter by property type}
                            {--test : Test API connection only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync properties from Casafari API to local database';

    protected CasafariService $casafariService;

    public function __construct(CasafariService $casafariService)
    {
        parent::__construct();
        $this->casafariService = $casafariService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Test connection if --test option is provided
        if ($this->option('test')) {
            return $this->testConnection();
        }

        $this->info('Starting Casafari properties sync...');

        // Build filters from options
        $filters = array_filter([
            'country' => $this->option('country'),
            'city' => $this->option('city'),
            'type' => $this->option('type'),
        ]);

        if (!empty($filters)) {
            $this->info('Applying filters: ' . json_encode($filters));
        }

        // Perform the sync
        $stats = $this->casafariService->syncProperties($filters);

        // Display results
        $this->newLine();
        $this->info('Sync completed!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total processed', $stats['total']],
                ['Created', $stats['created']],
                ['Updated', $stats['updated']],
                ['Errors', $stats['errors']],
            ]
        );

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Test the API connection.
     */
    protected function testConnection(): int
    {
        $this->info('Testing Casafari API connection...');

        if ($this->casafariService->testConnection()) {
            $this->info('✓ Connection successful!');
            return self::SUCCESS;
        }

        $this->error('✗ Connection failed. Please check your API credentials.');
        return self::FAILURE;
    }
}
