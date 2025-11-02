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
     * Generate and download CSV mapping of NFC tags to audio files
     * 
     * @return \Illuminate\Http\Response
     */
    public function exportCsv(Request $request)
    {
        try {
            // Get all NFC tags with their related items and audio files
            $tags = NfcTag::with(['item.audioFiles'])
                ->whereNotNull('item_id')
                ->orderBy('kode_tag')
                ->get();

            // Create CSV lines
            $csvLines = [];
            
            // Add header row
            $csvLines[] = 'nfc_tag,audio_file';

            // Add data rows
            foreach ($tags as $tag) {
                if ($tag->item && $tag->item->audioFiles->isNotEmpty()) {
                    $audioFile = $tag->item->audioFiles->first();
                    
                    // Escape commas in filenames
                    $nfcTag = $this->escapeCsvField($tag->kode_tag);
                    $audioFileName = $this->escapeCsvField($audioFile->nama_file);
                    
                    $csvLines[] = $nfcTag . ',' . $audioFileName;
                }
            }

            // Join lines with proper line breaks
            $csvContent = implode("\r\n", $csvLines);
            
            // Add BOM for Excel compatibility
            $csvContent = "\xEF\xBB\xBF" . $csvContent;

            // Log the export activity (only if user is authenticated)
            if (auth()->check()) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'aktivitas' => 'export_nfc_mapping',
                    'waktu_aktivitas' => now(),
                    'context' => json_encode([
                        'total_mappings' => count($csvLines) - 1, // Exclude header
                        'format' => 'csv',
                        'timestamp' => now()->toDateTimeString(),
                    ]),
                ]);
            }

            // Generate filename with timestamp
            $filename = 'nfc_audio_mapping_' . date('Ymd_His') . '.csv';

            // Return CSV response with proper headers
            return response($csvContent, 200)
                ->header('Content-Type', 'text/csv; charset=UTF-8')
                ->header('Content-Description', 'File Transfer')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->header('Content-Transfer-Encoding', 'binary')
                ->header('Cache-Control', 'must-revalidate, post-check=0, pre-check=0')
                ->header('Pragma', 'public')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            Log::error('CSV Export Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // If called from web route, redirect back with error
            if (!$request->expectsJson()) {
                return back()->with('error', 'Gagal mengekspor CSV: ' . $e->getMessage());
            }
            
            // If called from API, return JSON error
            return response()->json([
                'error' => 'Failed to generate CSV',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get JSON format of NFC to audio mapping
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
                    
                    $mappings[] = [
                        'nfc_tag' => $tag->kode_tag,
                        'audio_file' => $audioFile->nama_file,
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
                        'format' => 'json',
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

    /**
     * Escape CSV field if it contains comma, quotes, or newlines
     * 
     * @param string $field
     * @return string
     */
    private function escapeCsvField(?string $field): string
    {
        if ($field === null) {
            return '';
        }

        // If field contains comma, quotes, or newlines, wrap in quotes and escape quotes
        if (preg_match('/[,"\r\n]/', $field)) {
            return '"' . str_replace('"', '""', $field) . '"';
        }

        return $field;
    }
}