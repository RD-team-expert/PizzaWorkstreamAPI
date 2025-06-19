<?php

namespace App\Http\Controllers;

use App\Services\WorkstreamApiService;

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

    public function getPositionApplications()
    {
        try {
            // Call the service method to fetch position applications with optional parameters
            $response = $this->workstreamApiService->getPositionApplications(
                request('embed'),          // Optional embed parameter
                request('status'),         // Optional status parameter
                request('first_name'),     // Optional first_name parameter
                request('last_name'),      // Optional last_name parameter
                request('name'),           // Optional name parameter
                request('current_stage'),  // Optional current_stage parameter
                request('position_uuid'),  // Optional position[uuid] parameter
                request('location_name'),  // Optional location[name] parameter
                request('tag_name'),       // Optional tag[name] parameter
                request('note_content'),   // Optional note[content] parameter
                request('created_at_gte'), // Optional created_at.gte parameter
                request('created_at_lte'), // Optional created_at.lte parameter
                request('hired_at_gte'),   // Optional hired_at.gte parameter
                request('hired_at_lte')    // Optional hired_at.lte parameter
            );
            
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
