<?php

require_once 'app/config/database.php';
require_once 'app/libs/ApiResponse.php';
require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/EnvHelper.php';
require_once 'app/models/SettingModel.php';

class AdminSettingsApiController
{
    private SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index(): void
    {
        $this->requireAdmin();
        ApiResponse::success('Settings retrieved successfully', $this->getResolvedSettings());
    }

    public function update(): void
    {
        $this->requireAdmin();

        $current = $this->getResolvedSettings();
        $data = $this->requestData();
        $settings = [
            'APP_URL' => trim((string) ($data['APP_URL'] ?? $current['APP_URL'])),
            'MAIL_MAILER' => trim((string) ($data['MAIL_MAILER'] ?? $current['MAIL_MAILER'])),
            'MAIL_HOST' => trim((string) ($data['MAIL_HOST'] ?? $current['MAIL_HOST'])),
            'MAIL_PORT' => trim((string) ($data['MAIL_PORT'] ?? $current['MAIL_PORT'])),
            'MAIL_USERNAME' => trim((string) ($data['MAIL_USERNAME'] ?? $current['MAIL_USERNAME'])),
            'MAIL_PASSWORD' => trim((string) ($data['MAIL_PASSWORD'] ?? '')),
            'MAIL_ENCRYPTION' => trim((string) ($data['MAIL_ENCRYPTION'] ?? $current['MAIL_ENCRYPTION'])),
            'MAIL_FROM_ADDRESS' => trim((string) ($data['MAIL_FROM_ADDRESS'] ?? $current['MAIL_FROM_ADDRESS'])),
            'MAIL_FROM_NAME' => trim((string) ($data['MAIL_FROM_NAME'] ?? $current['MAIL_FROM_NAME'])),
        ];

        if ($settings['MAIL_PASSWORD'] === '') {
            $settings['MAIL_PASSWORD'] = (string) $current['MAIL_PASSWORD'];
        }

        $errors = $this->validatePayload($settings);
        if (!empty($errors)) {
            ApiResponse::error('Validation failed', $errors, 422);
        }

        if (!$this->settingModel->saveMany($settings)) {
            ApiResponse::error('Settings update failed', null, 400);
        }

        ApiResponse::success('Settings updated successfully', $this->getResolvedSettings());
    }

    private function requireAdmin(): void
    {
        $user = AuthHelper::getCurrentUser();
        if (empty($user) || ($user['role'] ?? '') !== 'admin') {
            ApiResponse::error('Forbidden', null, 403);
        }
    }

    private function requestData(): array
    {
        if (!empty($_POST)) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);

        return is_array($json) ? $json : [];
    }

    private function getResolvedSettings(): array
    {
        $keys = [
            'APP_URL',
            'MAIL_MAILER',
            'MAIL_HOST',
            'MAIL_PORT',
            'MAIL_USERNAME',
            'MAIL_PASSWORD',
            'MAIL_ENCRYPTION',
            'MAIL_FROM_ADDRESS',
            'MAIL_FROM_NAME',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $this->settingModel->get($key, EnvHelper::get($key, ''));
        }

        return $settings;
    }

    private function validatePayload(array $settings): array
    {
        $errors = [];

        if ($settings['APP_URL'] === '') {
            $errors['APP_URL'] = 'APP_URL không được để trống';
        }
        if ($settings['MAIL_MAILER'] === '') {
            $errors['MAIL_MAILER'] = 'MAIL_MAILER không được để trống';
        }
        if ($settings['MAIL_HOST'] === '' && $settings['MAIL_MAILER'] === 'smtp') {
            $errors['MAIL_HOST'] = 'MAIL_HOST không được để trống khi dùng SMTP';
        }
        if ($settings['MAIL_PORT'] === '' || !ctype_digit((string) $settings['MAIL_PORT'])) {
            $errors['MAIL_PORT'] = 'MAIL_PORT phải là số hợp lệ';
        }
        if ($settings['MAIL_FROM_ADDRESS'] === '' || !filter_var($settings['MAIL_FROM_ADDRESS'], FILTER_VALIDATE_EMAIL)) {
            $errors['MAIL_FROM_ADDRESS'] = 'MAIL_FROM_ADDRESS phải là email hợp lệ';
        }

        return $errors;
    }
}
