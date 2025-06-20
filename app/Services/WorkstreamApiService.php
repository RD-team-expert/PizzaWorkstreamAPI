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
            return $response;
            // Check if the request was successful
            if ($response->successful()) {
                // Get the token from the response
                $token = $response->json()['access_token'];

                // Store the token in the database
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
        $token = Token::latest()->first(); // Get the latest stored token

        // If no token is found, return an error
        if (!$token) {
            throw new \Exception('No token found in the database.');
        }

        // Hardcode the /tokens/refresh_token endpoint URL
        $apiUrl = $this->apiBaseUrl . '/tokens/refresh_token';

        // Prepare the query parameters
        $query = [
            'grant_type'    => 'client_credentials',
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'token'         => $token->token // Pass the current token to refresh it
        ];

        // Make a POST request to the /tokens/refresh_token endpoint
        $response = Http::asForm()->post($apiUrl, $query);

        // Check if the request was successful
        if ($response->successful()) {
            // Get the new token from the response
            $newToken = $response->json()['access_token'];

            // Update the token in the database (replace the old one)
            $token->update([
                'token' => $newToken
            ]);

            return $newToken;
        }

        // Handle failed request
        throw new \Exception('Failed to refresh access token: ' . $response->body());
    }

    public function getPositionApplications($embed = null, $status = null, $firstName = null, $lastName = null, $name = null, $currentStage = null, $positionUuid = null, $locationName = null, $tagName = null, $noteContent = null, $createdAtGte = null, $createdAtLte = null, $hiredAtGte = null, $hiredAtLte = null)
    {
        // Get the first stored token (do not generate a new one)
        $token = Token::first();

        if (!$token) {
            throw new \Exception('No token found in the database.');
        }

        // Build the query parameters based on the optional parameters
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

        // Build the full URL with query parameters
        $url = $this->apiBaseUrl . '/position_applications?' . http_build_query($query);

        // Make the GET request with the Authorization header
        $response = Http::withToken($token->token)->get($url);

        // Check if the request was successful
        if ($response->successful()) {
            return $response->json(); // Return the response as JSON
        }

        // Handle failed request
        throw new \Exception('Failed to fetch position applications: ' . $response->body());
    }
}
