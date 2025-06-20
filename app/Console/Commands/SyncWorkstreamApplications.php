<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorkstreamApiService;
use App\Models\Applicant;
use Illuminate\Support\Facades\Log;

class SyncWorkstreamApplications extends Command
{
    protected $signature = 'workstream:sync-applications';

    protected $description = 'Fetch applications from Workstream and store/update in database';

    protected WorkstreamApiService $api;

    public function __construct(WorkstreamApiService $api)
    {
        parent::__construct();
        $this->api = $api;
    }

    public function handle(): int
    {
        $this->info("Fetching applications from Workstream...");

        try {
            $applications = $this->api->getPositionApplications();

            foreach ($applications as $app) {
                Applicant::create([
                    'uuid'               => $app['uuid'] ?? null,
                    'first_name'         => $app['first_name'] ?? null,
                    'last_name'          => $app['last_name'] ?? null,
                    'email'              => $app['email'] ?? null,
                    'phone'              => $app['phone'] ?? null,
                    'name'               => $app['name'] ?? null,
                    'status'             => $app['status'] ?? null,
                    'current_stage'      => $app['current_stage'] ?? null,
                    'application_date'   => $app['application_date'] ?? null,
                    'hired_at'           => $app['hired_at'] ?? null,
                    'sms_phone_number'   => $app['sms_phone_number'] ?? null,
                    'global_phone_number'=> $app['global_phone_number'] ?? null,
                    'language'           => $app['language'] ?? null,
                    'referer_source'     => $app['referer_source'] ?? null,
                    'position_title'     => $app['position']['title'] ?? null,
                    'location_name'      => $app['location']['name'] ?? null,
                ]);
            }

            $this->info("✅ Successfully synced " . count($applications) . " application(s).");
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            Log::error("Workstream sync failed: " . $e->getMessage());
            $this->error("❌ Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
