<?php

namespace App\Http\Controllers;
use App\Models\Applicant;
use App\Services\WorkstreamApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
class ApiController extends Controller
{
    protected $workstreamApiService;

    // Injecting the WorkstreamApiService
    public function __construct(WorkstreamApiService $workstreamApiService)
    {
        $this->workstreamApiService = $workstreamApiService;
    }

    public function getAccessToken()
    {
        try {
            $token = $this->workstreamApiService->getAccessToken(); // Calls the /tokens endpoint
            return response()->json($token);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);

        }
    }



    public function getPositionApplications(): JsonResponse
    {
        try {
            // Collect all non-status filters once
            $embed           = request('embed');
            $firstName       = request('first_name');
            $lastName        = request('last_name');
            $name            = request('name');
            $currentStage    = request('current_stage');
            $positionUuid    = request('position_uuid');
            $locationName    = request('location_name');
            $tagName         = request('tag_name');
            $noteContent     = request('note_content');
            $createdAtGte    = request('created_at_gte');
            $createdAtLte    = request('created_at_lte');
            $hiredAtGte      = request('hired_at_gte');
            $hiredAtLte      = request('hired_at_lte');

            $statusesToFetch = ['in_progress', 'hired'];

            $allApplications = [];
            $savedCounts = [
                'in_progress' => 0,
                'hired'       => 0,
                'total'       => 0,
                'skipped_no_uuid' => 0,
            ];

            foreach ($statusesToFetch as $status) {
                // Fetch per status
                $applications = $this->workstreamApiService->getPositionApplications(
                    $embed,            // embed
                    $status,           // status
                    $firstName,        // first_name
                    $lastName,         // last_name
                    $name,             // name
                    $currentStage,     // current_stage
                    $positionUuid,     // position[uuid]
                    $locationName,     // location[name]
                    $tagName,          // tag[name]
                    $noteContent,      // note[content]
                    $createdAtGte,     // created_at.gte
                    $createdAtLte,     // created_at.lte
                    $hiredAtGte,       // hired_at.gte
                    $hiredAtLte        // hired_at.lte
                );

                // Persist results for this status
                foreach ((array) $applications as $app) {
                    $uuid = $app['uuid'] ?? null;
                    if (empty($uuid)) {
                        $savedCounts['skipped_no_uuid']++;
                        continue;
                    }

                    Applicant::updateOrCreate(
                        ['uuid' => $uuid],
                        [
                            'first_name'           => $app['first_name']           ?? null,
                            'last_name'            => $app['last_name']            ?? null,
                            'email'                => $app['email']                ?? null,
                            'phone'                => $app['phone']                ?? null,
                            'name'                 => $app['name']                 ?? null,
                            'status'               => $app['status']               ?? null,
                            'current_stage'        => $app['current_stage']        ?? null,
                            'application_date'     => $app['application_date']     ?? null,
                            'hired_at'             => $app['hired_at']             ?? null,
                            'sms_phone_number'     => $app['sms_phone_number']     ?? null,
                            'global_phone_number'  => $app['global_phone_number']  ?? null,
                            'language'             => $app['language']             ?? null,
                            'referer_source'       => $app['referer_source']       ?? null,
                            'position_title'       => data_get($app, 'position.title'),
                            'location_name'        => data_get($app, 'location.name'),
                        ]
                    );

                    $savedCounts[$status]++;
                    $savedCounts['total']++;
                }

                // Keep a combined list for the response (optional)
                if (is_array($applications)) {
                    $allApplications = array_merge($allApplications, $applications);
                }
            }

            return response()->json([
                'message'          => 'Applications fetched and synced.',
                'saved_counts'     => $savedCounts,
                'applications'     => $allApplications, // remove if you don’t want to echo payloads
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    public function updateDataWarehouse(Request $request)
    {
        try {
            // Validate that 'date' is present and in a valid format
            $request->validate([
                'date' => 'required|date',
            ]);

            $date = $request->input('date');

            // Call the service method with the date
            $result = $this->workstreamApiService->updateDataWarehouse($date);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (ValidationException $e) {
            // Return validation error messages in custom format
            return response()->json([
                'success' => false,
                'errors' => $e->errors(),
            ], 422); // 422 Unprocessable Entity

        } catch (\Exception $e) {
            // Handle other errors
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
