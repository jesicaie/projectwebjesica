<?php

return [
    'enabled' => env('THERMAL_PRINTER_ENABLED', false),
    'driver' => env('THERMAL_PRINTER_DRIVER', PHP_OS_FAMILY === 'Windows' ? 'windows' : 'file'),
    'device' => env('THERMAL_PRINTER_DEVICE', '/dev/usb/lp0'),
    'host' => env('THERMAL_PRINTER_HOST', '127.0.0.1'),
    'port' => env('THERMAL_PRINTER_PORT', 9100),
    'name' => env('THERMAL_PRINTER_NAME', env('RECEIPT_PRINTER', 'POS-80')),
];
