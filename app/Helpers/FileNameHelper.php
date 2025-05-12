<?php

namespace App\Helpers;

class FileNameHelper
{
    /**
     * Generate clean filename for reports without underscores
     * 
     * @param string $type Report type (UTS/UAS)
     * @param string $studentName Student name
     * @param string $className Class name
     * @param string|null $tahunAjaran Academic year (optional)
     * @return string
     */
    public static function generateReportFilename($type, $studentName, $className, $tahunAjaran = null)
    {
        // Clean student name - remove special characters and replace spaces with spaces
        $cleanName = preg_replace('/[^\w\s]/u', '', $studentName);
        // Instead of replacing spaces with underscores, just keep the spaces
        
        // Clean class name
        $cleanClassName = preg_replace('/[^\w\s]/u', '', $className);
        // Remove spaces
        $cleanClassName = str_replace(' ', '', $cleanClassName);
        
        // Format academic year if provided
        $tahunAjaranText = '';
        if ($tahunAjaran) {
            $tahunAjaranText = ' ' . str_replace('/', '-', $tahunAjaran);
        }
        
        // Format: Rapor UTS NamaSiswa Kelas TahunAjaran.docx
        // Use spaces instead of underscores
        return "Rapor {$type} {$cleanName} Kelas{$cleanClassName}{$tahunAjaranText}.docx";
    }
    
    /**
     * Generate clean filename for batch reports without underscores
     * 
     * @param string $type Report type (UTS/UAS)
     * @param string $className Class name
     * @param string|null $tahunAjaran Academic year (optional)
     * @return string
     */
    public static function generateBatchReportFilename($type, $className, $tahunAjaran = null)
    {
        // Clean class name
        $cleanClassName = preg_replace('/[^\w\s]/u', '', $className);
        $cleanClassName = str_replace(' ', '', $cleanClassName);
        
        // Format academic year if provided
        $tahunAjaranText = '';
        if ($tahunAjaran) {
            $tahunAjaranText = ' ' . str_replace('/', '-', $tahunAjaran);
        }
        
        // Add timestamp for uniqueness
        $timestamp = date('Ymd His');
        
        // Format: Rapor Batch UTS Kelas1A 2023-2024 20230501 123045.zip
        // Use spaces instead of underscores
        return "Rapor Batch {$type} Kelas{$cleanClassName}{$tahunAjaranText} {$timestamp}.zip";
    }
}