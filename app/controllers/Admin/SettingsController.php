<?php
namespace Admin;

/**
 * SettingsController.php
 * Trang cấu hình SMTP và các biến hệ thống cơ bản cho admin.
 */

require_once 'app/libs/AuthHelper.php';
require_once 'app/libs/EnvHelper.php';
require_once 'app/models/SettingModel.php';

class SettingsController
{
    private \SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new \SettingModel();
    }

    public function index(): void
    {
        \AuthHelper::requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSave();
        }

        $flash = $this->consumeFlash();
        $errors = $flash['errors'];
        $success = $flash['success'];
        $settings = $this->getResolvedSettings();

        include 'app/views/admin/settings/index.php';
    }

    private function handleSave(): void
    {
        $current = $this->getResolvedSettings();

        $settings = [
            'APP_URL' => trim($_POST['APP_URL'] ?? $current['APP_URL']),
            'MAIL_MAILER' => trim($_POST['MAIL_MAILER'] ?? $current['MAIL_MAILER']),
            'MAIL_HOST' => trim($_POST['MAIL_HOST'] ?? $current['MAIL_HOST']),
            'MAIL_PORT' => trim($_POST['MAIL_PORT'] ?? $current['MAIL_PORT']),
            'MAIL_USERNAME' => trim($_POST['MAIL_USERNAME'] ?? $current['MAIL_USERNAME']),
            'MAIL_PASSWORD' => trim($_POST['MAIL_PASSWORD'] ?? ''),
            'MAIL_ENCRYPTION' => trim($_POST['MAIL_ENCRYPTION'] ?? $current['MAIL_ENCRYPTION']),
            'MAIL_FROM_ADDRESS' => trim($_POST['MAIL_FROM_ADDRESS'] ?? $current['MAIL_FROM_ADDRESS']),
            'MAIL_FROM_NAME' => trim($_POST['MAIL_FROM_NAME'] ?? $current['MAIL_FROM_NAME']),
        ];

        if ($settings['MAIL_PASSWORD'] === '') {
            $settings['MAIL_PASSWORD'] = (string) $current['MAIL_PASSWORD'];
        }

        $errors = [];
        if ($settings['APP_URL'] === '') {
            $errors[] = 'APP_URL không được để trống';
        }
        if ($settings['MAIL_MAILER'] === '') {
            $errors[] = 'MAIL_MAILER không được để trống';
        }
        if ($settings['MAIL_HOST'] === '' && $settings['MAIL_MAILER'] === 'smtp') {
            $errors[] = 'MAIL_HOST không được để trống khi dùng SMTP';
        }
        if ($settings['MAIL_PORT'] === '' || !ctype_digit($settings['MAIL_PORT'])) {
            $errors[] = 'MAIL_PORT phải là số hợp lệ';
        }
        if ($settings['MAIL_FROM_ADDRESS'] === '' || !filter_var($settings['MAIL_FROM_ADDRESS'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'MAIL_FROM_ADDRESS phải là email hợp lệ';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_data'] = $settings;
            header('Location: /admin/settings');
            exit;
        }

        if (!$this->settingModel->saveMany($settings)) {
            $_SESSION['errors'] = ['Không thể lưu cấu hình SMTP'];
            $_SESSION['old_data'] = $settings;
            header('Location: /admin/settings');
            exit;
        }

        $_SESSION['success'] = 'Đã lưu cấu hình SMTP thành công.';
        header('Location: /admin/settings');
        exit;
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
            $settings[$key] = $this->settingModel->get($key, \EnvHelper::get($key, ''));
        }

        return $settings;
    }

    private function consumeFlash(): array
    {
        $flash = [
            'errors' => $_SESSION['errors'] ?? [],
            'success' => $_SESSION['success'] ?? '',
            'old_data' => $_SESSION['old_data'] ?? [],
        ];

        unset($_SESSION['errors'], $_SESSION['success'], $_SESSION['old_data']);

        return $flash;
    }
}
