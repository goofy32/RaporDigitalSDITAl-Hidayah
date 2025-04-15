<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\ReportPlaceholder;
use Illuminate\Support\Facades\Log;

class EnhancedTemplateValidator
{
    protected $templatePath;
    protected $type;
    protected $variables = [];
    protected $requiredPlaceholders = [];
    protected $allPlaceholders = [];
    
    public function __construct(string $templatePath, string $type = 'UTS')
    {
        $this->templatePath = $templatePath;
        $this->type = strtoupper($type);
        $this->loadPlaceholders();
    }
    
    protected function loadPlaceholders()
    {
        // Load required placeholders based on type
        $this->requiredPlaceholders = ReportPlaceholder::where('is_required', true)
            ->where(function($query) {
                if ($this->type === 'UTS') {
                    $query->where('category', '!=', 'uas_only');
                } else {
                    $query->where('category', '!=', 'uts_only');
                }
            })
            ->pluck('placeholder_key')
            ->toArray();
        
        // Load all valid placeholders
        $this->allPlaceholders = ReportPlaceholder::pluck('placeholder_key')->toArray();
    }
    
    public function validate()
    {
        try {
            $processor = new TemplateProcessor($this->templatePath);
            $this->variables = $processor->getVariables();
            
            $result = [
                'is_valid' => true,
                'errors' => [],
                'warnings' => [],
                'stats' => [
                    'total_placeholders' => count($this->variables),
                    'unique_placeholders' => count(array_unique($this->variables))
                ]
            ];
            
            // Check for missing required placeholders
            $missingPlaceholders = $this->checkMissingPlaceholders();
            if (!empty($missingPlaceholders)) {
                $result['is_valid'] = false;
                $result['errors'][] = [
                    'type' => 'missing_required',
                    'message' => 'Placeholder wajib tidak ditemukan',
                    'placeholders' => $missingPlaceholders
                ];
            }
            
            // Check for duplicate placeholders
            $duplicatePlaceholders = $this->checkDuplicatePlaceholders();
            if (!empty($duplicatePlaceholders)) {
                $result['warnings'][] = [
                    'type' => 'duplicates',
                    'message' => 'Ditemukan placeholder duplikat dalam template',
                    'placeholders' => $duplicatePlaceholders
                ];
            }
            
            // Check for invalid format placeholders
            $invalidFormatPlaceholders = $this->checkInvalidFormatPlaceholders();
            if (!empty($invalidFormatPlaceholders)) {
                $result['warnings'][] = [
                    'type' => 'invalid_format',
                    'message' => 'Ditemukan placeholder dengan format tidak standar',
                    'placeholders' => $invalidFormatPlaceholders
                ];
            }
            
            // Check for unknown placeholders
            $unknownPlaceholders = $this->checkUnknownPlaceholders();
            if (!empty($unknownPlaceholders)) {
                $result['warnings'][] = [
                    'type' => 'unknown',
                    'message' => 'Ditemukan placeholder yang tidak terdaftar',
                    'placeholders' => $unknownPlaceholders
                ];
            }
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Template validation error:', [
                'error' => $e->getMessage(),
                'file_path' => $this->templatePath
            ]);
            
            return [
                'is_valid' => false,
                'errors' => [
                    [
                        'type' => 'processing_error',
                        'message' => 'Gagal memproses template: ' . $e->getMessage()
                    ]
                ]
            ];
        }
    }
    
    protected function checkMissingPlaceholders()
    {
        return array_diff($this->requiredPlaceholders, $this->variables);
    }
    
    protected function checkDuplicatePlaceholders()
    {
        $counts = array_count_values($this->variables);
        return array_filter($counts, function($count) {
            return $count > 1;
        });
    }
    
    protected function checkInvalidFormatPlaceholders()
    {
        $invalidFormat = [];
        
        foreach ($this->variables as $var) {
            // Check for common format issues
            if (strpos($var, ' ') !== false) {
                $invalidFormat[$var] = 'contains spaces';
            } elseif (preg_match('/[,;:\/\(\)]/', $var)) {
                $invalidFormat[$var] = 'contains invalid characters';
            } elseif (preg_match('/^[A-Z]/', $var)) {
                $invalidFormat[$var] = 'starts with uppercase (inconsistent naming)';
            }
        }
        
        return $invalidFormat;
    }
    
    protected function checkUnknownPlaceholders()
    {
        // Check for dynamic placeholders (e.g., nama_matapelajaran1)
        $dynamicPatterns = [
            '/^nama_matapelajaran\d+$/',
            '/^nilai_matapelajaran\d+$/',
            '/^capaian_matapelajaran\d+$/',
            '/^nama_mulok\d+$/',
            '/^nilai_mulok\d+$/',
            '/^capaian_mulok\d+$/',
            '/^ekskul\d+_nama$/',
            '/^ekskul\d+_keterangan$/'
        ];
        
        $unknownPlaceholders = [];
        
        foreach ($this->variables as $var) {
            if (in_array($var, $this->allPlaceholders)) {
                continue; // Skip known placeholders
            }
            
            $isDynamicPlaceholder = false;
            foreach ($dynamicPatterns as $pattern) {
                if (preg_match($pattern, $var)) {
                    $isDynamicPlaceholder = true;
                    break;
                }
            }
            
            if (!$isDynamicPlaceholder) {
                $unknownPlaceholders[] = $var;
            }
        }
        
        return $unknownPlaceholders;
    }
    
    /**
     * Extend the validation methods to also analyze the document content
     * looking for potential placeholder-like text that might be malformed
     */
    public function deepValidate()
    {
        $result = $this->validate();
        
        // Additional Deep Validation logic can be added here
        // For example, extract text from docx and look for ${...} patterns
        // that might not be properly formed
        
        return $result;
    }
}