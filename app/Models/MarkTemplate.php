<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarkTemplate extends Model
{
    protected $fillable = ['name', 'configuration'];

    protected $casts = [
        'configuration' => 'array',
    ];

    // Mark keys are dynamically generated based on slot index:
    // - Non-final slots => t{index+1}
    // - Final slot => exm
    protected static $markKeys = ['t1', 't2', 't3', 't4', 'exm'];

    /**
     * Configuration is stored as array of { label, max }. Last slot is treated as Exam.
     */
    public function getConfigurationAttribute($value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    public function totalMax(): int
    {
        $sum = 0;
        foreach ($this->configuration ?? [] as $item) {
            $sum += (int) ($item['max'] ?? 0);
        }
        return $sum;
    }

    /**
     * Slots for display in marks grid: each config row + slot_index for locking.
     */
    public function slotsForDisplay(): array
    {
        $config = $this->configuration ?? [];
        if (empty($config)) {
            $config = self::defaultConfiguration();
        }
        // Buffer columns supported in `marks` table: t1..t10 + exm.
        // Because exm is the final slot, cap configuration length to 11 items.
        $config = array_slice($config, 0, 11);
        $s = [];
        $lastIdx = count($config) - 1;
        foreach ($config as $i => $item) {
            // Final slot is always Exam (exm). All previous slots map sequentially to t1..tN.
            $key = ($i === $lastIdx) ? 'exm' : ('t' . ($i + 1));
            $s[] = [
                'key' => $key,
                'label' => $item['label'] ?? 'Slot ' . ($i + 1),
                'max' => (int) ($item['max'] ?? 0),
                'slot_index' => $i,
            ];
        }
        if (empty($s)) {
            $s[] = ['key' => 'exm', 'label' => 'Exam', 'max' => 60, 'slot_index' => 0];
        }
        return $s;
    }

    public static function defaultConfiguration(): array
    {
        return [
            ['label' => '1st CA', 'max' => 20],
            ['label' => '2nd CA', 'max' => 20],
            ['label' => 'Exam', 'max' => 60],
        ];
    }
}
