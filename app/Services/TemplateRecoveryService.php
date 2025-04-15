<?php

namespace App\Services;

use App\Models\ReportTemplate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;
use PhpOffice\PhpWord\IOFactory;

class TemplateRecoveryService
{
    /**
     * Attempt to fix a corrupt or invalid template
     *
     * @param ReportTemplate $template
     * @return array
     */
    public function attemptRecovery(ReportTemplate $template)
    {
        Log::info('Starting template recovery attempt', [
            'template_id' => $template->id,
            'filename' => $template->filename
        ]);
        
        $templatePath = storage_path('app/public/' . $template->path);
        
        if (!file_exists($templatePath)) {
            return [
                'success' => false,
                'message' => 'Template file tidak ditemukan',
                'recovery_method' => 'none'
            ];
        }
        
        // Try different recovery methods
        $methods = [
            'recreate_from_backup',
            'fix_placeholders',
            'repair_document',
            'extract_content'
        ];
        
        foreach ($methods as $method) {
            $result = $this->$method($template, $templatePath);
            
            if ($result['success']) {
                return $result;
            }
            
            // Log failure and continue with next method
            Log::info("Recovery method '{$method}' failed", [
                'template_id' => $template->id,
                'reason' => $result['message']
            ]);
        }
        
        // If all recovery methods fail
        return [
            'success' => false,
            'message' => 'Semua metode recovery gagal',
            'recovery_method' => 'none'
        ];
    }
    
    /**
     * Create a new template from backup
     *
     * @param ReportTemplate $template
     * @param string $templatePath
     * @return array
     */
    protected function recreate_from_backup(ReportTemplate $template, $templatePath)
    {
        // Check for backup copy
        $backupPath = str_replace('.docx', '.backup.docx', $templatePath);
        
        if (!file_exists($backupPath)) {
            return [
                'success' => false,
                'message' => 'Backup tidak ditemukan',
                'recovery_method' => 'recreate_from_backup'
            ];
        }
        
        try {
            // Restore from backup
            copy($backupPath, $templatePath);
            
            // Verify if restored file is valid
            $templateProcessor = new TemplateProcessor($templatePath);
            $templateProcessor->getVariables(); // This will throw exception if file is invalid
            
            return [
                'success' => true,
                'message' => 'Template berhasil dipulihkan dari backup',
                'recovery_method' => 'recreate_from_backup'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memulihkan dari backup: ' . $e->getMessage(),
                'recovery_method' => 'recreate_from_backup'
            ];
        }
    }
    
    /**
     * Fix placeholder format issues
     *
     * @param ReportTemplate $template
     * @param string $templatePath
     * @return array
     */
    protected function fix_placeholders(ReportTemplate $template, $templatePath)
    {
        try {
            // Open template and extract content as XML
            $zip = new \ZipArchive();
            if ($zip->open($templatePath) !== true) {
                return [
                    'success' => false,
                    'message' => 'Gagal membuka template sebagai ZIP file',
                    'recovery_method' => 'fix_placeholders'
                ];
            }
            
            // Read document.xml
            $content = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if (!$content) {
                return [
                    'success' => false,
                    'message' => 'Gagal membaca content dokumen',
                    'recovery_method' => 'fix_placeholders'
                ];
            }
            
            // Fix common placeholder issues
            $fixedContent = $content;
            
            // 1. Fix spaces in placeholders: ${ name } -> ${name}
            $fixedContent = preg_replace('/\$\{\s+([^{}]+)\s+\}/', '${$1}', $fixedContent);
            
            // 2. Fix broken placeholder syntax: $name -> ${name}
            $fixedContent = preg_replace('/\$([a-z0-9_]+)/', '${$1}', $fixedContent);
            
            // 3. Fix malformed placeholders: {name} -> ${name}
            $fixedContent = preg_replace('/\{([a-z0-9_]+)\}/', '${$1}', $fixedContent);
            
            // Skip if no changes were made
            if ($fixedContent === $content) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada perbaikan placeholder yang diperlukan',
                    'recovery_method' => 'fix_placeholders'
                ];
            }
            
            // Save fixed content
            $tempPath = tempnam(sys_get_temp_dir(), 'docx');
            copy($templatePath, $tempPath);
            
            $zip = new \ZipArchive();
            if ($zip->open($tempPath)) {
                $zip->addFromString('word/document.xml', $fixedContent);
                $zip->close();
                
                // Create backup
                copy($templatePath, str_replace('.docx', '.backup.docx', $templatePath));
                
                // Replace original file
                copy($tempPath, $templatePath);
                unlink($tempPath);
                
                // Verify if fixed file is valid
                try {
                    $templateProcessor = new TemplateProcessor($templatePath);
                    $templateProcessor->getVariables();
                    
                    return [
                        'success' => true,
                        'message' => 'Placeholder berhasil diperbaiki',
                        'recovery_method' => 'fix_placeholders'
                    ];
                } catch (\Exception $e) {
                    // If still invalid, continue to next method
                    return [
                        'success' => false,
                        'message' => 'Perbaikan placeholder masih tidak valid: ' . $e->getMessage(),
                        'recovery_method' => 'fix_placeholders'
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Gagal menyimpan konten yang diperbaiki',
                'recovery_method' => 'fix_placeholders'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memperbaiki placeholder: ' . $e->getMessage(),
                'recovery_method' => 'fix_placeholders'
            ];
        }
    }
    
    /**
     * Repair document structure
     *
     * @param ReportTemplate $template
     * @param string $templatePath
     * @return array
     */
    protected function repair_document(ReportTemplate $template, $templatePath)
    {
        try {
            // Create a new PhpWord document and try to load the corrupt one
            $tempFile = tempnam(sys_get_temp_dir(), 'repaired_');
            $tempFile .= '.docx';
            
            // Attempt to open document and save as new file
            $phpWord = IOFactory::load($templatePath);
            $phpWord->save($tempFile);
            
            // Verify the repaired document
            try {
                $templateProcessor = new TemplateProcessor($tempFile);
                $variables = $templateProcessor->getVariables();
                
                // Create backup of original
                copy($templatePath, str_replace('.docx', '.backup.docx', $templatePath));
                
                // Replace original with repaired
                copy($tempFile, $templatePath);
                @unlink($tempFile);
                
                return [
                    'success' => true,
                    'message' => 'Dokumen berhasil diperbaiki',
                    'recovery_method' => 'repair_document',
                    'variables' => $variables
                ];
            } catch (\Exception $e) {
                @unlink($tempFile);
                return [
                    'success' => false,
                    'message' => 'Dokumen hasil perbaikan masih tidak valid: ' . $e->getMessage(),
                    'recovery_method' => 'repair_document'
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal memperbaiki struktur dokumen: ' . $e->getMessage(),
                'recovery_method' => 'repair_document'
            ];
        }
    }
    
    /**
     * Extract content and create new template
     *
     * @param ReportTemplate $template
     * @param string $templatePath
     * @return array
     */
    protected function extract_content(ReportTemplate $template, $templatePath)
    {
        try {
            // Create a simple template
            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $section = $phpWord->addSection();
            
            // Try to extract text content from corrupted file
            $zip = new \ZipArchive();
            if ($zip->open($templatePath) !== true) {
                return [
                    'success' => false,
                    'message' => 'Gagal membuka template sebagai ZIP file',
                    'recovery_method' => 'extract_content'
                ];
            }
            
            // Read document.xml content
            $xmlContent = $zip->getFromName('word/document.xml');
            $zip->close();
            
            if (!$xmlContent) {
                return [
                    'success' => false,
                    'message' => 'Gagal membaca konten dokumen',
                    'recovery_method' => 'extract_content'
                ];
            }
            
            // Extract text and placeholders using regex
            preg_match_all('/\$\{([^}]+)\}/', $xmlContent, $matches);
            $placeholders = $matches[1];
            
            // Add warning text
            $section->addText('DOKUMEN PEMULIHAN - TEMPLATE ASLI RUSAK', ['bold' => true, 'size' => 14], ['alignment' => 'center']);
            $section->addTextBreak();
            $section->addText('Template asli rusak dan telah dibuat ulang secara otomatis. Silahkan sesuaikan kembali format.', ['italic' => true], ['alignment' => 'center']);
            $section->addTextBreak(2);
            
            // Add placeholder info
            $section->addText('PLACEHOLDER YANG DITEMUKAN:', ['bold' => true]);
            $section->addTextBreak();
            
            if (!empty($placeholders)) {
                foreach ($placeholders as $placeholder) {
                    $text = '${' . $placeholder . '}';
                    $section->addText($text);
                }
            } else {
                $section->addText('Tidak ditemukan placeholder dalam template asli.', ['italic' => true]);
                
                // Add common placeholders as guidance
                $section->addTextBreak();
                $section->addText('PLACEHOLDER YANG DISARANKAN:', ['bold' => true]);
                $section->addTextBreak();
                
                $suggestedPlaceholders = [
                    'nama_siswa', 'nisn', 'nis', 'kelas', 'tahun_ajaran',
                    'nilai_matematika', 'nilai_bahasa_indonesia', 'nilai_ipa'
                ];
                
                foreach ($suggestedPlaceholders as $placeholder) {
                    $text = '${' . $placeholder . '}';
                    $section->addText($text);
                }
            }
            
            // Save the recovery template
            $recoveryFileName = 'recovery_' . time() . '_' . basename($templatePath);
            $recoveryPath = dirname($templatePath) . '/' . $recoveryFileName;
            $phpWord->save($recoveryPath);
            
            // Update template record with new file
            $relativeRecoveryPath = 'templates/' . $recoveryFileName;
            $template->update([
                'path' => $relativeRecoveryPath,
                'filename' => $recoveryFileName
            ]);
            
            return [
                'success' => true,
                'message' => 'Konten berhasil diekstrak dan template baru dibuat',
                'recovery_method' => 'extract_content',
                'recovery_path' => $relativeRecoveryPath
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Gagal mengekstrak konten: ' . $e->getMessage(),
                'recovery_method' => 'extract_content'
            ];
        }
    }
}