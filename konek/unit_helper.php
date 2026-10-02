<?php

if (!function_exists('unit_label_map')) {
    function unit_label_map()
    {
        return [
            'wb_1' => 'SMP',
            'wb_2' => 'SMK 1',
            'wb_3' => 'SMK 2',
        ];
    }

    function unit_normalize($unit)
    {
        $value = strtolower(trim((string) $unit));
        if ($value === '') {
            return '';
        }

        $compact = str_replace([' ', '-', '_'], '', $value);
        $aliases = [
            'smp' => 'wb_1',
            'wb1' => 'wb_1',
            'wb2' => 'wb_2',
            'smk1' => 'wb_2',
            'smk01' => 'wb_2',
            'wb3' => 'wb_3',
            'smk2' => 'wb_3',
            'smk02' => 'wb_3',
        ];
        if (isset($aliases[$compact])) {
            return $aliases[$compact];
        }

        $labels = array_change_key_case(array_flip(unit_label_map()), CASE_LOWER);
        return $labels[$value] ?? $value;
    }

    function unit_label($unit)
    {
        $code = unit_normalize($unit);
        $map = unit_label_map();
        return $map[$code] ?? (string) $unit;
    }
}
