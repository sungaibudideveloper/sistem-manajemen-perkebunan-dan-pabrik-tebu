<?php
// app/helpers.php

if (!function_exists('hasPermission')) {
    function hasPermission($permission) {
        return app(\App\View\Composers\NavigationComposer::class)->hasPermission($permission);
    }
}