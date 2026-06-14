<?php

namespace Admin;

require_once 'app/libs/AuthHelper.php';

class SettingsController
{
    public function index(): void
    {
        \AuthHelper::requireAdmin();
        include 'app/views/admin/settings/index.php';
    }
}
