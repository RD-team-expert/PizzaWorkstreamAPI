<?php

namespace App\Services;

use App\Models\Token;  // Import the Token model
use Illuminate\Support\Facades\Http;

class WorkstreamApiService
{
    // Define class properties for the environment variables
    protected $clientId;
    protected $clientSecret;
    protected $apiBaseUrl;

    // Constructor to initialize the environment values
    public function __construct()
    {
        // Load the values from .env file
        $this->clientId = env('Workstream_Client_ID');
        $this->clientSecret = env('Workstream_Client_Secret');
        $this->apiBaseUrl = env('WORKSTREAM_API_BASE_URL'); // Base URL for the API
    }

    /**
     * Get the access token from the /tokens endpoint, using database storage.
     */
    public function getAccessToken()
    {
        // Check if the token exists in the database
        $token = Token::latest()->first(); // Get the latest stored token

        // If no token is found in the database, request a new one
        if (!$token) {

            // Hardcode the /tokens endpoint URL
            $apiUrl = $this->apiBaseUrl . '/tokens';
            // Make a POST request to the /tokens endpoint to get a new token
            $response = Http::post($apiUrl, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'name'          => 'Adler Test',
                'scopes'        => [
                    "positions",
                    "company_users",
                    "company_roles",
                    "position_applications",
                    "employees",
                    "locations",
                    "departments",
                    "team_members",
                    "imported_employee_infos"
                ]
            ]);
            // Check if the request was successful
            if ($response->successful()) {
                $data = $response->json();
            
                if (!isset($data['token'])) {
                    throw new \Exception('Expected token not found: ' . json_encode($data));
                }
            
                $token = $data['token'];
            
                Token::create([
                    'token' => $token
                ]);
            
                return $token;
            }
            

            // Handle failed request
            throw new \Exception('Failed to get access token: ' . $response->body());
        }

        // If the token exists in the database, return it
        return $token->token;
    }

/**
     * Refresh the access token using the /tokens/refresh_token endpoint.
     */
    public function refreshAccessToken()
{
    // Get the existing token from the database (assume the latest one is valid)
    $token = Token::latest()->first();

    if (!$token) {
        throw new \Exception('No token found in the database.');
    }

    // Build the refresh token endpoint
    $apiUrl = $this->apiBaseUrl . '/tokens/refresh_token';

    // Make the request
    $response = Http::post($apiUrl, [
        'grant_type'    => 'client_credentials',
        'client_id'     => $this->clientId,
        'client_secret' => $this->clientSecret,
        'token'         => $token->token
    ]);

    if ($response->successful()) {
        $data = $response->json();

        if (!isset($data['token'])) {
            throw new \Exception('Expected token not found in response: ' . json_encode($data));
        }

        // Update the token in DB
        $token->update([
            'token'      => $data['token'],
            'expires_in' => $data['expires_in'] ?? null,
            'scopes'     => isset($data['scopes']) ? json_encode($data['scopes']) : null,
        ]);

        return $data['token'];
    }

    // Handle error
    throw new \Exception('Failed to refresh access token: ' . $response->body());
}

public function getPositionApplications($embed = null, $status = null, $firstName = null, $lastName = null, $name = null, $currentStage = null, $positionUuid = null, $locationName = null, $tagName = null, $noteContent = null, $createdAtGte = null, $createdAtLte = null, $hiredAtGte = null, $hiredAtLte = null)
{
    $token = Token::first();

    if (!$token) {
        throw new \Exception('No token found in the database.');
    }

    // If no positionUuid is provided, fetch published positions
    if (empty($positionUuid)) {
        $positions = $this->getPublishedPositions();

        $uuids = array_column($positions, 'uuid');

        $chunks = array_chunk($uuids, 2);
        $allApplications = [];

        foreach ($chunks as $chunk) {
            foreach ($chunk as $uuid) {
                $applications = $this->getPositionApplications(
                    $embed,
                    $status,
                    $firstName,
                    $lastName,
                    $name,
                    $currentStage,
                    $uuid,
                    $locationName,
                    $tagName,
                    $noteContent,
                    $createdAtGte,
                    $createdAtLte,
                    $hiredAtGte,
                    $hiredAtLte
                );
        
                if (isset($applications['position_applications'])) {
                    $allApplications = array_merge($allApplications, $applications['position_applications']);
                }
            }
        }
        
        return $allApplications; // Flat array of all applicants
    }

    // --- Original Logic Continues Here ---

    $query = [];

    if ($embed) {
        $query['embed'] = $embed;
    }
    if ($status) {
        $query['status'] = $status;
    }
    if ($firstName) {
        $query['first_name'] = $firstName;
    }
    if ($lastName) {
        $query['last_name'] = $lastName;
    }
    if ($name) {
        $query['name'] = $name;
    }
    if ($currentStage) {
        $query['current_stage'] = $currentStage;
    }
    if ($positionUuid) {
        $query['position[uuid]'] = $positionUuid;
    }
    if ($locationName) {
        $query['location[name]'] = $locationName;
    }
    if ($tagName) {
        $query['tag[name]'] = $tagName;
    }
    if ($noteContent) {
        $query['note[content]'] = $noteContent;
    }
    if ($createdAtGte) {
        $query['created_at.gte'] = $createdAtGte;
    }
    if ($createdAtLte) {
        $query['created_at.lte'] = $createdAtLte;
    }
    if ($hiredAtGte) {
        $query['hired_at.gte'] = $hiredAtGte;
    }
    if ($hiredAtLte) {
        $query['hired_at.lte'] = $hiredAtLte;
    }

    $url = $this->apiBaseUrl . '/position_applications?' . http_build_query($query);

    $response = Http::withToken($token->token)->get($url);

    if ($response->successful()) {
        return $response->json();
    }

    throw new \Exception('Failed to fetch position applications: ' . $response->body());
}


    public function getPublishedPositions()
{
    $token = Token::first();

    if (!$token) {
        throw new \Exception('No token found in the database.');
    }

    $url = $this->apiBaseUrl . '/positions?status=published';

    $response = Http::withToken($token->token)->get($url);

    if ($response->successful()) {
        $data = $response->json();

        if (!isset($data['positions'])) {
            throw new \Exception('Expected positions not found in response: ' . json_encode($data));
        }

        return $data['positions'];
    }

    throw new \Exception('Failed to fetch published positions: ' . $response->body());
}

}
