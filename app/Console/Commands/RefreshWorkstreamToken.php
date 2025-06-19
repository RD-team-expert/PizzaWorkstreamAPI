<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorkstreamApiService;

class RefreshWorkstreamToken extends Command
{
    // The name and signature of the console command.
    protected $signature = 'workstream:refresh-token';

    // The console command description.
    protected $description = 'Refresh the Workstream API token';

    // Inject the WorkstreamTokenRefreshService
    protected WorkstreamApiService $workstreamTokenRefreshService;

    // Constructor to inject the service
    public function __construct(WorkstreamApiService $workstreamTokenRefreshService)
    {
        parent::__construct();
        $this->workstreamTokenRefreshService = $workstreamTokenRefreshService;
    }

    // The command logic
    public function handle()
    {
        try {
            // Call the refreshAccessToken method from the service
            $newToken = $this->workstreamTokenRefreshService->refreshAccessToken();

            // Output the new token to the console
            $this->info('Token refreshed successfully!');
            $this->info('New Access Token: ' . $newToken);

        } catch (\Exception $e) {
            // Handle any errors and output them to the console
            $this->error('Failed to refresh token: ' . $e->getMessage());
        }
    }
}
