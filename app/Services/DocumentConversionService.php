<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class DocumentConversionService
{
    /**
     * Convert DOCX file to PDF using LibreOffice
     *
     * @param string $sourcePath Full path to source DOCX file
     * @param string $outputDir Directory where the PDF should be saved
     * @return array Success status and path to PDF or error message
     */
    public function convertDocxToPdf(string $sourcePath, string $outputDir): array
    {
        // Ensure output directory exists
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        // Detect if running in Windows
        $isWindows = PHP_OS === 'WINNT' || PHP_OS === 'WIN32';

        try {
            if ($isWindows) {
                // Windows approach (Laragon)
                $libreOfficePath = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
                
                // Format the command for Windows
                $command = '"' . $libreOfficePath . '" --headless --convert-to pdf --outdir "' . $outputDir . '" "' . $sourcePath . '"';
                
                // Log the command for debugging
                Log::info('Running LibreOffice command in Windows', [
                    'command' => $command
                ]);
                
                // Execute the command
                exec($command, $output, $returnCode);
                
                if ($returnCode !== 0) {
                    Log::error('LibreOffice conversion failed in Windows', [
                        'command' => $command,
                        'output' => $output,
                        'return_code' => $returnCode
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'PDF conversion failed with error code: ' . $returnCode,
                        'output' => $output
                    ];
                }
                
                // Get the output filename (same as input but with .pdf extension)
                $filename = pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';
                $pdfPath = $outputDir . '\\' . $filename;
                
                // Check if the file was actually created
                if (!file_exists($pdfPath)) {
                    Log::error('PDF file not created', [
                        'expected_path' => $pdfPath
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'PDF file was not created at: ' . $pdfPath
                    ];
                }
                
                // Convert Windows path to forward slashes for consistency in Laravel
                $normalizedPath = str_replace('\\', '/', $pdfPath);
                
                return [
                    'success' => true,
                    'path' => $normalizedPath,
                    'filename' => $filename
                ];
            } else {
                // Linux/WSL approach using Process
                $command = [
                    'soffice',
                    '--headless',
                    '--convert-to', 'pdf',
                    '--outdir', $outputDir,
                    $sourcePath
                ];
                
                // Create and run the process
                $process = new Process($command);
                $process->setTimeout(60); // Increase timeout for large documents
                $process->run();
                
                // Check if the process was successful
                if (!$process->isSuccessful()) {
                    Log::error('LibreOffice conversion failed', [
                        'command' => $process->getCommandLine(),
                        'error' => $process->getErrorOutput()
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'PDF conversion failed: ' . $process->getErrorOutput()
                    ];
                }
                
                // Get the output filename (same as input but with .pdf extension)
                $filename = pathinfo($sourcePath, PATHINFO_FILENAME) . '.pdf';
                $pdfPath = $outputDir . '/' . $filename;
                
                // Check if the file was actually created
                if (!file_exists($pdfPath)) {
                    return [
                        'success' => false,
                        'message' => 'PDF file was not created'
                    ];
                }
                
                return [
                    'success' => true,
                    'path' => $pdfPath,
                    'filename' => $filename
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception during PDF conversion', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'PDF conversion error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Convert DOCX to PDF using Storage paths
     *
     * @param string $storagePath Path relative to storage/app/public
     * @param string $outputFolder Folder within storage/app/public to save the PDF
     * @return array Success status and path to PDF or error message
     */
    public function convertStorageDocxToPdf(string $storagePath, string $outputFolder = 'pdf'): array
    {
        // Get full source path
        $fullSourcePath = storage_path('app/public/' . $storagePath);
        
        // Set output directory
        $outputDir = storage_path('app/public/' . $outputFolder);
        
        // Perform conversion
        $result = $this->convertDocxToPdf($fullSourcePath, $outputDir);
        
        // If successful, adjust the path to be relative to storage
        if ($result['success']) {
            // Extract just the filename from the full path
            $filename = $result['filename'];
            
            // Create path relative to storage/app/public
            $relativePath = $outputFolder . '/' . $filename;
            
            $result['storage_path'] = $relativePath;
            $result['url'] = Storage::url($relativePath);
        }
        
        return $result;
    }
}