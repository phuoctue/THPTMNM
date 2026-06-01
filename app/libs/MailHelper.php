<?php
/**
 * MailHelper.php
 * Gửi email xác thực và đặt lại mật khẩu bằng PHPMailer SMTP.
 */

require_once __DIR__ . '/EnvHelper.php';
require_once dirname(__DIR__) . '/models/SettingModel.php';

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    public static function baseUrl(): string
    {
        $settingModel = new SettingModel();
        $envUrl = trim((string) ($settingModel->get('APP_URL', EnvHelper::get('APP_URL', ''))));
        if ($envUrl !== '') {
            return rtrim($envUrl, '/');
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return $scheme . '://' . $host;
    }

    public static function send(string $to, string $subject, string $htmlMessage): bool
    {
        $settingModel = new SettingModel();
        $mailerType = strtolower((string) $settingModel->get('MAIL_MAILER', EnvHelper::get('MAIL_MAILER', 'smtp')));
        $fromAddress = (string) $settingModel->get('MAIL_FROM_ADDRESS', EnvHelper::get('MAIL_FROM_ADDRESS', 'no-reply@' . parse_url(self::baseUrl(), PHP_URL_HOST)));
        $fromName = (string) $settingModel->get('MAIL_FROM_NAME', EnvHelper::get('MAIL_FROM_NAME', 'My Store'));

        if (!class_exists(PHPMailer::class)) {
            error_log('PHPMailer not found. Run composer install.');
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->setFrom($fromAddress, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $htmlMessage;
            $mail->AltBody = trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlMessage)));

            if ($mailerType === 'smtp') {
                $mail->isSMTP();
                $mail->Host = (string) $settingModel->get('MAIL_HOST', EnvHelper::get('MAIL_HOST', 'smtp.gmail.com'));
                $mail->Port = (int) $settingModel->get('MAIL_PORT', EnvHelper::get('MAIL_PORT', 587));
                $mail->SMTPAuth = trim((string) $settingModel->get('MAIL_USERNAME', EnvHelper::get('MAIL_USERNAME', ''))) !== '';
                $mail->Username = (string) $settingModel->get('MAIL_USERNAME', EnvHelper::get('MAIL_USERNAME', ''));
                $mail->Password = (string) $settingModel->get('MAIL_PASSWORD', EnvHelper::get('MAIL_PASSWORD', ''));
                $encryption = strtolower((string) $settingModel->get('MAIL_ENCRYPTION', EnvHelper::get('MAIL_ENCRYPTION', 'tls')));
                if ($encryption === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($encryption === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
            } elseif ($mailerType === 'sendmail') {
                $mail->isSendmail();
            } else {
                $mail->isMail();
            }

            return $mail->send();
        } catch (Exception $e) {
            error_log('MailHelper send error: ' . $e->getMessage());
            return false;
        }
    }

    public static function verificationEmail(string $fullName, string $verifyLink): string
    {
        $safeName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');

        return '
            <div style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2937">
                <h2>Xác thực tài khoản My Store</h2>
                <p>Xin chào ' . $safeName . ',</p>
                <p>Vui lòng bấm vào nút bên dưới để xác thực email của bạn.</p>
                <p>
                    <a href="' . htmlspecialchars($verifyLink, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#4f46e5;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:bold">
                        Xác thực email
                    </a>
                </p>
                <p>Nếu bạn không tạo tài khoản, hãy bỏ qua email này.</p>
            </div>
        ';
    }

    public static function resetPasswordEmail(string $fullName, string $resetLink): string
    {
        $safeName = htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8');

        return '
            <div style="font-family:Arial,sans-serif;line-height:1.6;color:#1f2937">
                <h2>Đặt lại mật khẩu My Store</h2>
                <p>Xin chào ' . $safeName . ',</p>
                <p>Chúng tôi đã nhận yêu cầu đặt lại mật khẩu cho tài khoản của bạn.</p>
                <p>
                    <a href="' . htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#dc2626;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:bold">
                        Đặt lại mật khẩu
                    </a>
                </p>
                <p>Liên kết này sẽ hết hạn sau 1 giờ.</p>
                <p>Nếu bạn không yêu cầu, hãy bỏ qua email này.</p>
            </div>
        ';
    }
}
