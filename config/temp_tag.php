<?php

return [
    /*
     |--------------------------------------------------------------------------
     | Temp Tags Cache Storage Path
     |--------------------------------------------------------------------------
     |
     | Inorder to be immune from the artisan cache:clear command
     | temp-tag has its own storage path to store expiring data.
     |
     |
     */
    'cache_storage_path' => storage_path('framework/temp_tag'),

];
