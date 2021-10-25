<?php
return [
    'providers' => [
        /*
         * Package Service Providers...
         */
        Maatwebsite\Excel\ExcelServiceProvider::class,
    ],
    'aliases' => [
        'Excel' => Maatwebsite\Excel\Facades\Excel::class,
    ]
];