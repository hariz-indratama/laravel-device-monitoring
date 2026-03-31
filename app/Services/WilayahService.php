<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class WilayahService
{
    private const CACHE_TTL = 86400; // 24 hours
    private const CACHE_PREFIX = 'wilayah:';

    /**
     * Get all provinces (level 1 — kode length 2)
     */
    public function provinces(): array
    {
        return Cache::remember(self::CACHE_PREFIX . 'provinces', self::CACHE_TTL, function () {
            return DB::table('wilayah')
                ->whereRaw('LENGTH(kode) = 2')
                ->orderBy('nama')
                ->get(['kode', 'nama'])
                ->toArray();
        });
    }

    /**
     * Get all cities/kabupaten by province kode (level 2 — kode length 5)
     */
    public function cities(string $provinceKode): array
    {
        return Cache::remember(self::CACHE_PREFIX . "cities:{$provinceKode}", self::CACHE_TTL, function () use ($provinceKode) {
            return DB::table('wilayah')
                ->whereRaw('LENGTH(kode) = 5')
                ->where('kode', 'LIKE', "{$provinceKode}%")
                ->orderBy('nama')
                ->get(['kode', 'nama'])
                ->toArray();
        });
    }

    /**
     * Get all districts/kecamatan by city kode (level 3 — kode length 8)
     */
    public function districts(string $cityKode): array
    {
        return Cache::remember(self::CACHE_PREFIX . "districts:{$cityKode}", self::CACHE_TTL, function () use ($cityKode) {
            return DB::table('wilayah')
                ->whereRaw('LENGTH(kode) = 8')
                ->where('kode', 'LIKE', "{$cityKode}%")
                ->orderBy('nama')
                ->get(['kode', 'nama'])
                ->toArray();
        });
    }

    /**
     * Get all villages/desa by district kode (level 4 — kode length 13)
     */
    public function villages(string $districtKode): array
    {
        return Cache::remember(self::CACHE_PREFIX . "villages:{$districtKode}", self::CACHE_TTL, function () use ($districtKode) {
            return DB::table('wilayah')
                ->whereRaw('LENGTH(kode) = 13')
                ->where('kode', 'LIKE', "{$districtKode}%")
                ->orderBy('nama')
                ->get(['kode', 'nama'])
                ->toArray();
        });
    }

    /**
     * Find wilayah record by kode
     */
    public function find(string $kode): ?object
    {
        return DB::table('wilayah')->where('kode', $kode)->first();
    }

    /**
     * Determine the level name from kode length
     */
    public static function levelName(int $length): string
    {
        return match ($length) {
            2 => 'Provinsi',
            5 => 'Kabupaten/Kota',
            8 => 'Kecamatan',
            13 => 'Desa/Kelurahan',
            default => 'Unknown',
        };
    }

    /**
     * Clear only wilayah-related cache entries (not the entire cache).
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_PREFIX . 'provinces');

        // Clear cached cities, districts, and villages
        // Since we can't enumerate keys in all drivers, we clear known prefixes
        // For production, consider using cache tags instead
        $provinces = DB::table('wilayah')
            ->whereRaw('LENGTH(kode) = 2')
            ->pluck('kode');

        foreach ($provinces as $provKode) {
            Cache::forget(self::CACHE_PREFIX . "cities:{$provKode}");

            $cities = DB::table('wilayah')
                ->whereRaw('LENGTH(kode) = 5')
                ->where('kode', 'LIKE', "{$provKode}%")
                ->pluck('kode');

            foreach ($cities as $cityKode) {
                Cache::forget(self::CACHE_PREFIX . "districts:{$cityKode}");

                $districts = DB::table('wilayah')
                    ->whereRaw('LENGTH(kode) = 8')
                    ->where('kode', 'LIKE', "{$cityKode}%")
                    ->pluck('kode');

                foreach ($districts as $districtKode) {
                    Cache::forget(self::CACHE_PREFIX . "villages:{$districtKode}");
                }
            }
        }
    }
}
