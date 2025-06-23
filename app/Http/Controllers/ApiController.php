<?php

namespace App\Http\Controllers;

use App\Services\WorkstreamApiService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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
