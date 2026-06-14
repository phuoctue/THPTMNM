<?php

namespace Admin;

require_once 'app/libs/AuthHelper.php';

class UserController
{
    public function index(): void
    {
        \AuthHelper::requireAdmin();
        include 'app/views/admin/users/index.php';
    }

    public function edit($id = null): void
    {
        \AuthHelper::requireAdmin();
        include 'app/views/admin/users/edit.php';
    }
}
