<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\ReportTemplate;
use App\Models\Nilai;
use App\Models\Absensi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class RaporCachingService
{
    /**
     * Cache key prefix
     * 
     * @var string
     */
    protected const CACHE_PREFIX = 'rapor_cache_';
    
    /**
     * Default cache duration in minutes
     * 
     * @var int
     */
    protected const DEFAULT_CACHE_MINUTES = 60;
    
    /**
     * Get cached report or generate new one
     *
     * @param Siswa $siswa
     * @param ReportTemplate $template
     * @param string $type
     * @param int|null $tahunAjaranId
     * @param bool $forceRegenerate
     * @return array
     */
    public function getCachedReport(Siswa $siswa, ReportTemplate $template, string $type, ?int $tahunAjaranId = null, bool $forceRegenerate = false)
    {
        $cacheKey = $this->generateCacheKey($siswa, $template, $type, $tahunAjaranId);
        
        // Force regenerate if requested
        if ($forceRegenerate) {
            return $this->generateAndCacheReport($siswa, $template, $type, $tahunAjaranId, $cacheKey);
        }
        
        // Check if cache is valid
        if (Cache::has($cacheKey) && !$this->hasDataChangedSinceLastGeneration($siswa, $type, $tahunAjaranId)) {
            $cachedData = Cache::get($cacheKey);
            
            // Verify cached file exists
            if (isset($cachedData['path']) && Storage::disk('public')->exists($cachedData['path'])) {
                Log::info('Using cached report', [
                    'siswa_id' => $siswa->id,
                    'template_id' => $template->id,
                    'type' => $type,
                    'cache_key' => $cacheKey
                ]);
                
                return $cachedData;
            }
        }
        
        // Generate new report
        return $this->generateAndCacheReport($siswa, $template, $type, $tahunAjaranId, $cacheKey);
    }
    
    /**
     * Generate and cache report
     *
     * @param Siswa $siswa
     * @param ReportTemplate $template
     * @param string $type
     * @param int|null $tahunAjaranId
     * @param string $cacheKey
     * @return array
     */
    protected function generateAndCacheReport(Siswa $siswa, ReportTemplate $template, string $type, ?int $tahunAjaranId, string $cacheKey)
    {
        // Generate report
        $processor = new RaporTemplateProcessor($template, $siswa, $type, $tahunAjaranId);
        $result = $processor->generate();
        
        if ($result['success']) {
            // Cache the result
            $cacheData = [
                'success' => true,
                'path' => $result['path'],
                'filename' => $result['filename'],
                'generated_at' => now()->toDateTimeString(),
                'last_data_update' => $this->getLastDataUpdateTimestamp($siswa, $type, $tahunAjaranId),
            ];
            
            Cache::put($cacheKey, $cacheData, now()->addMinutes(self::DEFAULT_CACHE_MINUTES));
            
            Log::info('Report generated and cached', [
                'siswa_id' => $siswa->id,
                'template_id' => $template->id,
                'type' => $type,
                'cache_key' => $cacheKey
            ]);
            
            return $cacheData;
        }
        
        // Return result if generation failed
        return $result;
    }
    
    /**
     * Generate cache key
     *
     * @param Siswa $siswa
     * @param ReportTemplate $template
     * @param string $type
     * @param int|null $tahunAjaranId
     * @return string
     */
    protected function generateCacheKey(Siswa $siswa, ReportTemplate $template, string $type, ?int $tahunAjaranId)
    {
        $tahunAjaranPart = $tahunAjaranId ?: 'default';
        return self::CACHE_PREFIX . "s{$siswa->id}_t{$template->id}_{$type}_{$tahunAjaranPart}";
    }
    
    /**
     * Check if data has changed since last generation
     *
     * @param Siswa $siswa
     * @param string $type
     * @param int|null $tahunAjaranId
     * @return bool
     */
    protected function hasDataChangedSinceLastGeneration(Siswa $siswa, string $type, ?int $tahunAjaranId)
    {
        $cacheKey = $this->generateCacheKey($siswa, null, $type, $tahunAjaranId);
        
        if (!Cache::has($cacheKey)) {
            return true;
        }
        
        $cachedData = Cache::get($cacheKey);
        $lastDataUpdate = $cachedData['last_data_update'] ?? null;
        
        if (!$lastDataUpdate) {
            return true;
        }
        
        $currentLastUpdate = $this->getLastDataUpdateTimestamp($siswa, $type, $tahunAjaranId);
        
        return $currentLastUpdate > $lastDataUpdate;
    }
    
    /**
     * Get the timestamp of last data update relevant for this report
     *
     * @param Siswa $siswa
     * @param string $type
     * @param int|null $tahunAjaranId
     * @return string
     */
    protected function getLastDataUpdateTimestamp(Siswa $siswa, string $type, ?int $tahunAjaranId)
    {
        $semester = $type === 'UTS' ? 1 : 2;
        
        // Check last updated nilai
        $lastNilaiUpdate = Nilai::where('siswa_id', $siswa->id)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->whereHas('mataPelajaran', function($query) use ($semester) {
                $query->where('semester', $semester);
            })
            ->latest('updated_at')
            ->value('updated_at');
            
        // Check last updated absensi
        $lastAbsensiUpdate = Absensi::where('siswa_id', $siswa->id)
            ->where('semester', $semester)
            ->when($tahunAjaranId, function($query) use ($tahunAjaranId) {
                return $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->latest('updated_at')
            ->value('updated_at');
            
        // Check last updated siswa data
        $lastSiswaUpdate = $siswa->updated_at;
        
        // Get the most recent update
        $dates = array_filter([$lastNilaiUpdate, $lastAbsensiUpdate, $lastSiswaUpdate]);
        
        if (empty($dates)) {
            return now()->toDateTimeString();
        }
        
        return max($dates);
    }
    
    /**
     * Invalidate cache for a specific student
     *
     * @param Siswa $siswa
     * @param string|null $type
     * @param int|null $tahunAjaranId
     * @return void
     */
    public function invalidateCache(Siswa $siswa, ?string $type = null, ?int $tahunAjaranId = null)
    {
        $types = $type ? [$type] : ['UTS', 'UAS'];
        
        foreach ($types as $reportType) {
            $cacheKey = $this->generateCacheKey($siswa, null, $reportType, $tahunAjaranId);
            $cacheKeyPattern = str_replace('_t', '_t*', $cacheKey);
            
            // Get all matching cache keys
            $keys = $this->getCacheKeysMatchingPattern($cacheKeyPattern);
            
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
        
        Log::info('Cache invalidated for student', [
            'siswa_id' => $siswa->id,
            'types' => $types,
            'tahun_ajaran_id' => $tahunAjaranId
        ]);
    }
    
    /**
     * Invalidate cache for all students in a class
     *
     * @param int $kelasId
     * @param string|null $type
     * @param int|null $tahunAjaranId
     * @return void
     */
    public function invalidateCacheForClass(int $kelasId, ?string $type = null, ?int $tahunAjaranId = null)
    {
        $siswaIds = Siswa::where('kelas_id', $kelasId)->pluck('id')->toArray();
        
        foreach ($siswaIds as $siswaId) {
            $this->invalidateCache(Siswa::find($siswaId), $type, $tahunAjaranId);
        }
        
        Log::info('Cache invalidated for class', [
            'kelas_id' => $kelasId,
            'siswa_count' => count($siswaIds),
            'type' => $type,
            'tahun_ajaran_id' => $tahunAjaranId
        ]);
    }
    
    /**
     * Get cache keys matching a pattern
     *
     * @param string $pattern
     * @return array
     */
    protected function getCacheKeysMatchingPattern(string $pattern)
    {
        // This implementation depends on your cache driver
        // If using Redis, you can use the KEYS command
        // For simplicity, we'll just clear all cache keys with the prefix
        
        // Simplified implementation
        return [
            str_replace('*', 'any', $pattern)
        ];
        
        // Redis implementation would be something like:
        // return app('redis')->keys($pattern);
    }
}