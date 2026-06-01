<?php
require_once 'app/libs/ViewHelper.php';
$errors = $errors ?? [];
$success = $success ?? '';

// Chuẩn hóa lỗi: chấp nhận cả string, array phẳng hoặc array lồng nhau.
if (!function_exists('render_flash_errors_flatten')) {
    function render_flash_errors_flatten($value): array
    {
        if (is_string($value)) {
            return $value !== '' ? [$value] : [];
        }

        if (!is_array($value)) {
            return [];
        }

        $flat = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $flat = array_merge($flat, render_flash_errors_flatten($item));
            } elseif (is_string($item)) {
                $item = trim($item);
                if ($item !== '') {
                    $flat[] = $item;
                }
            }
        }

        return $flat;
    }
}

$errors = render_flash_errors_flatten($errors);
?>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi:</strong>
        <ul class="mb-0 mt-2 ps-3">
            <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công:</strong>
        <div class="mt-2"><?php echo htmlspecialchars($success); ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
