<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DisasterUpdateController extends Controller
{
    
    public function index()
    { 
        try {
            $adminBase = rtrim(env('ADMIN_API_URL', 'http://127.0.0.1:8000'), '/');
            $token = env('ADMIN_API_TOKEN');

            $resp = Http::withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->get($adminBase . '/api/updates');

            if (! $resp->successful()) {
                Log::warning('System-E proxy warning: admin API responded with status ' . $resp->status());
                return response()->json(['data' => [], 'message' => 'Failed to fetch updates from admin API'], 502);
            }

            $data = $resp->json();

            $items = $data['data'] ?? $data;
            $mapped = collect($items)->map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'disaster_type' => $item['disaster_type'] ?? null,
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                    'severity' => $item['severity'] ?? null,
                    'source' => $item['source'] ?? null,
                    'latitude' => isset($item['latitude']) ? (float) $item['latitude'] : null,
                    'longitude' => isset($item['longitude']) ? (float) $item['longitude'] : null,
                    'issued_at' => $item['issued_at'] ?? null,
                    'created_at' => $item['created_at'] ?? null,
                    'updated_at' => $item['updated_at'] ?? null,
                ];
            })->values()->all();

            return response()->json([
                'data' => $mapped,
                'current_page' => $data['current_page'] ?? $data['page'] ?? 1,
                'last_page' => $data['last_page'] ?? $data['lastPage'] ?? null,
                'total' => $data['total'] ?? count($mapped),
            ]);
        } catch (\Exception $e) {
            Log::error('System-E proxy error fetching updates: ' . $e->getMessage());
            return response()->json(['data' => [], 'message' => 'Error fetching updates: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $adminBase = rtrim(env('ADMIN_API_URL', 'http://127.0.0.1:8000'), '/');
            $token = env('ADMIN_API_TOKEN');
            
            $resp = Http::withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->get($adminBase . '/api/updates/' . $id);
            
            if (! $resp->successful()) {
                Log::warning('System-E proxy warning (show): admin API responded with status ' . $resp->status());
                return response()->json(['message' => 'Failed to fetch update from admin API'], 502);
            }

            $raw = $resp->json();
            $item = $raw['data'] ?? $raw;
            $mapped = [
                'id' => $item['id'] ?? null,
                'disaster_type' => $item['disaster_type'] ?? null,
                'title' => $item['title'] ?? null,
                'description' => $item['description'] ?? null,
                'severity' => $item['severity'] ?? null,
                'source' => $item['source'] ?? null,
                'latitude' => isset($item['latitude']) ? (float) $item['latitude'] : null,
                'longitude' => isset($item['longitude']) ? (float) $item['longitude'] : null,
                'issued_at' => $item['issued_at'] ?? null,
                'created_at' => $item['created_at'] ?? null,
                'updated_at' => $item['updated_at'] ?? null,
            ];

            return response()->json(['data' => $mapped]);
        } catch (\Exception $e) {
            Log::error('System-E proxy error fetching update: ' . $e->getMessage());
            return response()->json(['message' => 'Error fetching update: ' . $e->getMessage()], 500);
        }
    }

   
    public function latest()
    {
        try {
            $adminBase = rtrim(env('ADMIN_API_URL', 'http://127.0.0.1:8000'), '/');
            $token = env('ADMIN_API_TOKEN');
            
            $resp = Http::withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->get($adminBase . '/api/updates?per_page=10');
            
            if (! $resp->successful()) {
                Log::warning('System-E proxy warning (latest): admin API responded with status ' . $resp->status());
                return response()->json(['data' => [], 'message' => 'Failed to fetch latest updates'], 502);
            }

            $data = $resp->json();
            $items = $data['data'] ?? $data;
            $mapped = collect($items)->map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'disaster_type' => $item['disaster_type'] ?? null,
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                    'severity' => $item['severity'] ?? null,
                    'source' => $item['source'] ?? null,
                    'latitude' => isset($item['latitude']) ? (float) $item['latitude'] : null,
                    'longitude' => isset($item['longitude']) ? (float) $item['longitude'] : null,
                    'issued_at' => $item['issued_at'] ?? null,
                ];
            })->values()->all();

            return response()->json(['data' => $mapped]);
        } catch (\Exception $e) {
            Log::error('System-E proxy error fetching latest updates: ' . $e->getMessage());
            return response()->json(['data' => []], 500);
        }
    }
}
