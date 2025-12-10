<?php
// app/helpers.php

if (!function_exists('hasPermission')) {
    function hasPermission($permission) {
        return app(\App\View\Composers\NavigationComposer::class)->hasPermission($permission);
    }
}

if (!function_exists('formatCompanyCode')) {
    /**
     * Format company code to Roman numeral format
     * 
     * Examples:
     * - TBL1 -> TBL I
     * - TBL2 -> TBL II
     * - SB -> SB (no change)
     */
    function formatCompanyCode($code) {
        // Extract prefix (letters) and number
        preg_match('/^([A-Z]+)(\d+)$/', $code, $matches);
        
        if (count($matches) === 3) {
            $prefix = $matches[1]; // TBL
            $number = (int)$matches[2]; // 1, 2, 3
            
            // Roman numerals (1-20)
            $romans = [
                1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V',
                6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X',
                11 => 'XI', 12 => 'XII', 13 => 'XIII', 14 => 'XIV', 15 => 'XV',
                16 => 'XVI', 17 => 'XVII', 18 => 'XVIII', 19 => 'XIX', 20 => 'XX'
            ];
            
            if (isset($romans[$number])) {
                return $prefix . ' ' . $romans[$number];
            }
        }
        
        return $code; // Return original if no match
    }
}