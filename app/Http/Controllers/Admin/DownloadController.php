<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AudioFile;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Google\Cloud\Storage\StorageClient;
use ZipArchive;

class DownloadController extends Controller
{
    /**
     * Download all audio files as a ZIP archive
     * 
     * @return \Illuminate\Http\Response
     */
    public function downloadAllAudio(Request $request)
    {
        try {
            // Get all audio files with their items
            $audioFiles = AudioFile::with('item')->get();

            if ($audioFiles->isEmpty()) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No audio files found'
                    ], 404);
                }
                return back()->with('error', 'Tidak ada file audio yang tersedia untuk diunduh.');
            }

            // Create temporary directory for zip
            $tempPath = storage_path('app/temp');
            if (!file_exists($tempPath)) {
                mkdir($tempPath, 0755, true);
            }

            // Generate unique filename
            $zipFileName = 'audio_files_' . date('Ymd_His') . '.zip';
            $zipFilePath = $tempPath . '/' . $zipFileName;

            // Create ZIP archive
            $zip = new ZipArchive();
            if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Could not create ZIP file');
            }

            // Get GCS client with proper authentication
            $client = $this->gcsClient();
            $bucketName = config('filesystems.disks.gcs.bucket');
            $bucket = $client->bucket($bucketName);

            $downloadedCount = 0;
            $failedFiles = [];
            $totalSize = 0;

            // Add each audio file to the ZIP
            foreach ($audioFiles as $audio) {
                try {
                    // Normalize the object path
                    $objectPath = $this->normalizeObjectPath($audio->lokasi_penyimpanan);
                    
                    Log::info("Attempting to download: {$objectPath}");
                    
                    $object = $bucket->object($objectPath);

                    if (!$object->exists()) {
                        $failedFiles[] = [
                            'name' => $audio->nama_file,
                            'reason' => 'File not found in storage: ' . $objectPath
                        ];
                        Log::warning("Audio file not found in GCS: {$objectPath}");
                        continue;
                    }

                    // Download file content
                    $fileContent = $object->downloadAsString();
                    $fileSize = strlen($fileContent);
                    $totalSize += $fileSize;

                    // Get file extension from the stored file name or format_file
                    $fileName = $audio->nama_file;
                    
                    // Check if the file name already has an extension
                    if (!pathinfo($fileName, PATHINFO_EXTENSION) && $audio->format_file) {
                        // Add extension if missing
                        $fileName = $fileName . '.' . $audio->format_file;
                    }
                    
                    // Create organized folder structure in ZIP
                    $zipPath = $fileName;

                    // Add file to ZIP
                    $zip->addFromString($zipPath, $fileContent);
                    $downloadedCount++;

                    Log::info("Successfully downloaded: {$fileName}");

                } catch (\Exception $e) {
                    $failedFiles[] = [
                        'name' => $audio->nama_file,
                        'reason' => $e->getMessage()
                    ];
                    Log::error("Failed to add audio to ZIP: {$audio->nama_file}", [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            // Add a README.txt file with information
            $readmeContent = $this->generateReadme($downloadedCount, $failedFiles, $totalSize);
            $zip->addFromString('README.txt', $readmeContent);

            // Add CSV mapping file
            $csvContent = $this->generateCsvMapping($audioFiles);
            $zip->addFromString('nfc_audio_mapping.csv', $csvContent);

            // Close the ZIP file
            $zip->close();

            // Log the activity
            if (auth()->check()) {
                ActivityLog::create([
                    'user_id' => auth()->id(),
                    'aktivitas' => 'download_all_audio',
                    'waktu_aktivitas' => now(),
                    'context' => json_encode([
                        'total_files' => $downloadedCount,
                        'failed_files' => count($failedFiles),
                        'zip_size' => filesize($zipFilePath),
                        'zip_size_mb' => round(filesize($zipFilePath) / 1024 / 1024, 2),
                        'timestamp' => now()->toDateTimeString(),
                    ]),
                ]);
            }

            // Return the ZIP file for download and delete after sending
            return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Download All Audio Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to create ZIP file',
                    'message' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal membuat file ZIP: ' . $e->getMessage());
        }
    }

    /**
     * Get download statistics and file list
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDownloadStats(Request $request)
    {
        try {
            $audioFiles = AudioFile::with('item')->get();
            
            $totalFiles = $audioFiles->count();
            $totalSize = $audioFiles->sum('file_size') ?: 0;
            
            // Group by format
            $formatStats = $audioFiles->groupBy('format_file')->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'total_size' => $group->sum('file_size'),
                ];
            });

            // Items with audio
            $itemsWithAudio = $audioFiles->pluck('item_id')->unique()->count();

            // File list with details
            $fileList = $audioFiles->map(function ($audio) {
                return [
                    'id' => $audio->id,
                    'name' => $audio->nama_file,
                    'item_name' => $audio->item ? $audio->item->nama_item : 'Unknown',
                    'format' => $audio->format_file,
                    'size' => $audio->file_size,
                    'size_formatted' => $audio->formatted_file_size ?? $this->formatBytes($audio->file_size),
                    'duration' => $audio->durasi,
                    'duration_formatted' => $audio->formatted_duration ?? $audio->durasi,
                ];
            });

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_files' => $totalFiles,
                    'total_size_bytes' => $totalSize,
                    'total_size_mb' => round($totalSize / 1024 / 1024, 2),
                    'total_size_formatted' => $this->formatBytes($totalSize),
                    'estimated_zip_size_mb' => round(($totalSize * 0.95) / 1024 / 1024, 2),
                    'items_with_audio' => $itemsWithAudio,
                    'formats' => $formatStats,
                ],
                'files' => $fileList,
            ]);

        } catch (\Exception $e) {
            Log::error('Download Stats Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate README content
     */
    private function generateReadme(int $downloadedCount, array $failedFiles, int $totalSize): string
    {
        $content = "==============================================\n";
        $content .= "  AUDIO FILES EXPORT - Museum Audio Guide\n";
        $content .= "==============================================\n\n";
        $content .= "Export Date: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= "Total Files: {$downloadedCount}\n";
        $content .= "Total Size: " . $this->formatBytes($totalSize) . "\n\n";
        
        $content .= "FILE ORGANIZATION\n";
        $content .= "-----------------\n";
        $content .= "All audio files are in the root directory.\n";
        $content .= "File names include their original extensions (.mp3, .wav, etc.)\n\n";
        
        if (!empty($failedFiles)) {
            $content .= "FAILED DOWNLOADS\n";
            $content .= "----------------\n";
            $content .= "The following files could not be downloaded:\n\n";
            foreach ($failedFiles as $failed) {
                $content .= "  × {$failed['name']}\n";
                $content .= "    Reason: {$failed['reason']}\n\n";
            }
        }
        
        $content .= "\nNFC MAPPING\n";
        $content .= "-----------\n";
        $content .= "See 'nfc_audio_mapping.csv' for NFC tag to audio file mappings.\n\n";
        
        return $content;
    }

    /**
     * Generate CSV mapping content
     */
    private function generateCsvMapping($audioFiles): string
    {
        $csvLines = [];
        $csvLines[] = 'nfc_tag,audio_file,item_name,item_id';

        foreach ($audioFiles as $audio) {
            // Get file name with extension
            $fileName = $audio->nama_file;
            if (!pathinfo($fileName, PATHINFO_EXTENSION) && $audio->format_file) {
                $fileName = $fileName . '.' . $audio->format_file;
            }
            
            if ($audio->item && $audio->item->nfcTags->isNotEmpty()) {
                foreach ($audio->item->nfcTags as $tag) {
                    $csvLines[] = sprintf(
                        '%s,%s,%s,%d',
                        $tag->kode_tag,
                        $fileName,
                        str_replace(',', ';', $audio->item->nama_item),
                        $audio->item_id
                    );
                }
            }
        }

        return implode("\n", $csvLines);
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes(?int $bytes): string
    {
        if (!$bytes) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Build a StorageClient with proper authentication
     */
    private function gcsClient(): StorageClient
    {
        // Check if already bound in service container
        if (app()->bound('google.cloud.storage')) {
            return app('google.cloud.storage');
        }

        // Get configuration
        $keyFilePath = env('GOOGLE_CLOUD_KEY_FILE');
        $projectId = env('GOOGLE_CLOUD_PROJECT_ID');

        // Validate key file exists
        if (!$keyFilePath || !file_exists($keyFilePath)) {
            throw new \Exception('Google Cloud key file not found at: ' . $keyFilePath);
        }

        // Build options array
        $options = [
            'keyFilePath' => $keyFilePath,
        ];

        // Add project ID if available
        if ($projectId) {
            $options['projectId'] = $projectId;
        }

        Log::info('Initializing GCS client', [
            'key_file' => $keyFilePath,
            'key_file_exists' => file_exists($keyFilePath),
            'project_id' => $projectId,
        ]);

        return new StorageClient($options);
    }

    /**
     * Normalize object path for GCS
     */
    private function normalizeObjectPath(string $dbPath): string
    {
        $prefix = config('filesystems.disks.gcs.path_prefix', '');
        
        // Remove any leading slash from the database path
        $dbPath = ltrim($dbPath, '/');
        
        // If there's a prefix and the path doesn't start with it, prepend it
        if ($prefix && !str_starts_with($dbPath, $prefix)) {
            $prefix = rtrim($prefix, '/');
            return $prefix . '/' . $dbPath;
        }
        
        return $dbPath;
    }
}