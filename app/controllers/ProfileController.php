<?php

require_once 'app/libs/AuthHelper.php';

class ProfileController
{
    public function index(): void
    {
        AuthHelper::requireLogin();
        include 'app/views/profile/index.php';
    }

    public function edit(): void
    {
        AuthHelper::requireLogin();
        AuthHelper::requireVerifiedEmail();
        include 'app/views/profile/edit.php';
    }

    public function changePassword(): void
    {
        AuthHelper::requireLogin();
        AuthHelper::requireVerifiedEmail();
        include 'app/views/profile/change-password.php';
    }

    public function orders(): void
    {
        AuthHelper::requireLogin();
        AuthHelper::requireVerifiedEmail();
        include 'app/views/profile/orders.php';
    }
}
