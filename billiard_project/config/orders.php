<?php

return [
    // Number of hours after order creation to treat pending orders as expired
    'expiration_hours' => env('ORDER_EXPIRATION_HOURS', 1),

    // How often (in minutes) the scheduler should run this job — informational only
    'scheduler_interval_minutes' => env('ORDER_EXPIRATION_SCHEDULE_MINUTES', 10),
];
