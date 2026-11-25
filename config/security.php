<?php

return [
    'admin_user_ids' => array_values(array_filter(array_map('trim', explode(',', (string) env('ADMIN_USER_IDS', ''))))),
];
