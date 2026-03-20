<?php

return [
    'file_storage_disk' => env('FILE_STORAGE_DISK', env('FILESYSTEM_DISK', 'local')),
];
