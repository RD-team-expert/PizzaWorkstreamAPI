<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorkstreamApiService;
use App\Models\Applicant;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Log;


class SyncWorkstreamApplications extends Command
{
    protected $signature = 'workstream:sync-applications';

    protected $description = 'Fetch applications from Workstream and store/update in database';

    protected ApiController $api;
    public function __construct(ApiController $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    public function handle(): int
    {
        $this->info("Fetching applications from Workstream...");

        try {
            $applications = $this->api->getPositionApplications();

            //  $this->info("✅ Successfully synced " . count($applications) . " application(s).");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error("Workstream sync failed: " . $e->getMessage());
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
