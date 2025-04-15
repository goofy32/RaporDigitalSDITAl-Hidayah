<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Exception\Exception as PhpWordException;

class ErrorLogger
{
    const LOG_LEVEL_INFO = 'info';
    const LOG_LEVEL_WARNING = 'warning';
    const LOG_LEVEL_ERROR = 'error';
    const LOG_LEVEL_CRITICAL = 'critical';
    
    const CATEGORY_TEMPLATE = 'template';
    const CATEGORY_REPORT = 'report';
    const CATEGORY_DATA = 'data';
    const CATEGORY_SYSTEM = 'system';
    
    /**
     * Log error with detailed context and optional file output
     *
     * @param string $message Error message
     * @param string $category Error category
     * @param string $level Log level
     * @param array $context Additional context data
     * @param bool $saveToFile Whether to save error to file
     * @return string|null Path to error log file if saved
     */
    public static function log($message, $category = self::CATEGORY_SYSTEM, $level = self::LOG_LEVEL_ERROR, array $context = [], $saveToFile = false)
    {
        // Enrich context with additional data
        $enrichedContext = array_merge([
            'timestamp' => now()->toIso8601String(),
            'category' => $category,
            'user_id' => auth()->id() ?? auth()->guard('guru')->id() ?? 'guest',
            'session_id' => session()->getId(),
            'url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
            'ip' => request()->ip(),
            'tahun_ajaran_id' => session('tahun_ajaran_id'),
        ], $context);
        
        // Log to Laravel's logging system
        Log::{$level}($message, $enrichedContext);
        
        // Save to file if requested
        if ($saveToFile) {
            return self::saveToFile($message, $category, $level, $enrichedContext);
        }
        
        return null;
    }
    
    /**
     * Log a template-related error
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param bool $saveToFile Whether to save error to file
     * @return string|null Path to error log file if saved
     */
    public static function logTemplateError($message, array $context = [], $saveToFile = true)
    {
        return self::log($message, self::CATEGORY_TEMPLATE, self::LOG_LEVEL_ERROR, $context, $saveToFile);
    }
    
    /**
     * Log a report generation error
     *
     * @param string $message Error message
     * @param array $context Additional context data
     * @param bool $saveToFile Whether to save error to file
     * @return string|null Path to error log file if saved
     */
    public static function logReportError($message, array $context = [], $saveToFile = true)
    {
        return self::log($message, self::CATEGORY_REPORT, self::LOG_LEVEL_ERROR, $context, $saveToFile);
    }
    
    /**
     * Format PhpWord exception for better debugging
     *
     * @param PhpWordException $exception
     * @return array
     */
    public static function formatPhpWordException(PhpWordException $exception)
    {
        return [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'recommended_fix' => self::getRecommendedFix($exception)
        ];
    }
    
    /**
     * Get recommended fix based on exception
     *
     * @param \Exception $exception
     * @return string
     */
    protected static function getRecommendedFix(\Exception $exception)
    {
        $message = $exception->getMessage();
        
        // PhpWord specific errors
        if ($exception instanceof PhpWordException) {
            if (strpos($message, 'Cannot find section') !== false) {
                return 'Template mungkin rusak. Coba download template contoh dan copy-paste kontennya ke template baru.';
            }
            
            if (strpos($message, 'variable not found') !== false || strpos($message, 'Undefined variable') !== false) {
                return 'Ada placeholder yang tidak terdefinisi. Pastikan semua placeholder ditulis dengan benar: ${nama_placeholder}';
            }
            
            if (strpos($message, 'Invalid style') !== false) {
                return 'Template memiliki style yang tidak valid. Coba buat template baru dengan style standar.';
            }
        }
        
        // File related errors
        if (strpos($message, 'No such file') !== false || strpos($message, 'not found') !== false) {
            return 'File tidak ditemukan. Coba upload ulang template.';
        }
        
        // Memory related errors
        if (strpos($message, 'memory') !== false || strpos($message, 'Memory') !== false) {
            return 'File template mungkin terlalu besar atau kompleks. Coba sederhanakan template.';
        }
        
        // Default recommendation
        return 'Coba download template contoh dan gunakan format yang sama untuk membuat template Anda.';
    }
    
    /**
     * Save error log to file
     *
     * @param string $message Error message
     * @param string $category Error category
     * @param string $level Log level
     * @param array $context Additional context data
     * @return string Path to error log file
     */
    protected static function saveToFile($message, $category, $level, array $context)
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "error_logs/{$category}_{$timestamp}.log";
        
        $content = "=== ERROR LOG: {$timestamp} ===\n";
        $content .= "Message: {$message}\n";
        $content .= "Category: {$category}\n";
        $content .= "Level: {$level}\n\n";
        $content .= "=== CONTEXT ===\n";
        
        foreach ($context as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $content .= "{$key}: " . json_encode($value, JSON_PRETTY_PRINT) . "\n";
            } else {
                $content .= "{$key}: {$value}\n";
            }
        }
        
        Storage::disk('local')->put($filename, $content);
        
        return storage_path("app/{$filename}");
    }
    
    /**
     * Get all error logs for a category
     *
     * @param string $category Error category
     * @param int $limit Maximum number of logs to return
     * @return array
     */
    public static function getErrorLogs($category = null, $limit = 50)
    {
        $pattern = $category ? "error_logs/{$category}_*.log" : "error_logs/*.log";
        $files = Storage::disk('local')->files($pattern);
        
        // Sort by date (newest first)
        usort($files, function($a, $b) {
            return filemtime(storage_path("app/{$b}")) - filemtime(storage_path("app/{$a}"));
        });
        
        // Limit results
        $files = array_slice($files, 0, $limit);
        
        $logs = [];
        foreach ($files as $file) {
            $content = Storage::disk('local')->get($file);
            $logs[] = [
                'file' => $file,
                'content' => $content,
                'created_at' => date('Y-m-d H:i:s', filemtime(storage_path("app/{$file}"))),
            ];
        }
        
        return $logs;
    }
}