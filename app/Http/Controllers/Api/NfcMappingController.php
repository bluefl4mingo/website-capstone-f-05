<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NfcTag;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NfcMappingController extends Controller
{
    /**
     * Get NFC to audio mapping in simple JSON format
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportJson(Request $request)
    {
        try {
            // Get all NFC tags with their related items and audio files
            $tags = NfcTag::with(['item.audioFiles'])
                ->whereNotNull('item_id')
                ->orderBy('kode_tag')
                ->get();

            // Create simple key-value mapping
            $mappings = [];

            foreach ($tags as $tag) {
                if ($tag->item && $tag->item->audioFiles->isNotEmpty()) {
                    $audioFile = $tag->item->audioFiles->first();
                    
                    // Get file name with extension
                    $fileName = $audioFile->nama_file;
                    if (!pathinfo($fileName, PATHINFO_EXTENSION) && $audioFile->format_file) {
                        $fileName = $fileName . '.' . $audioFile->format_file;
                    }
                    
                    // Simple key-value: NFC tag -> audio file path with /audio/ prefix
                    $mappings[$tag->kode_tag] = '/audio/' . $fileName;
                }
            }

            // Log the export activity (only if user is authenticated)
            if (auth()->check()) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'aktivitas' => 'export_nfc_mapping',
                    'waktu_aktivitas' => now(),
                    'context' => json_encode([
                        'total_mappings' => count($mappings),
                        'format' => 'json',
                        'timestamp' => now()->toDateTimeString(),
                    ]),
                ]);
            }

            // Return simple JSON mapping
            return response()->json($mappings, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        } catch (\Exception $e) {
            Log::error('JSON Export Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to generate JSON mapping',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed JSON format of NFC to audio mapping
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMappings(Request $request)
    {
        try {
            $tags = NfcTag::with(['item.audioFiles'])
                ->whereNotNull('item_id')
                ->orderBy('kode_tag')
                ->get();

            $mappings = [];

            foreach ($tags as $tag) {
                if ($tag->item && $tag->item->audioFiles->isNotEmpty()) {
                    $audioFile = $tag->item->audioFiles->first();
                    
                    // Get file name with extension
                    $fileName = $audioFile->nama_file;
                    if (!pathinfo($fileName, PATHINFO_EXTENSION) && $audioFile->format_file) {
                        $fileName = $fileName . '.' . $audioFile->format_file;
                    }
                    
                    $mappings[] = [
                        'nfc_tag' => $tag->kode_tag,
                        'audio_file' => $fileName,
                        'item_id' => $tag->item_id,
                        'item_name' => $tag->item->nama_item,
                        'audio_id' => $audioFile->id,
                        'audio_path' => $audioFile->lokasi_penyimpanan,
                        'format' => $audioFile->format_file,
                        'duration' => $audioFile->durasi,
                        'file_size' => $audioFile->file_size,
                    ];
                }
            }

            // Log the API call (only if user is authenticated)
            if (auth()->check()) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'aktivitas' => 'fetch_nfc_mapping',
                    'waktu_aktivitas' => now(),
                    'context' => json_encode([
                        'total_mappings' => count($mappings),
                        'format' => 'json_detailed',
                    ]),
                ]);
            }

            return response()->json([
                'success' => true,
                'count' => count($mappings),
                'mappings' => $mappings,
                'generated_at' => now()->toIso8601String(),
            ]);

        } catch (\Exception $e) {
            Log::error('JSON Mappings Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Failed to fetch mappings',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}