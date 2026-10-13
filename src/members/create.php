<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($name === '' || $email === '') {
        $error = 'Nama dan Email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO Members (Name, Email, Phone) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $phone ?: null]);
            header('Location: list.php');
            exit;
        } catch (PDOException $e) {
            $error = $e->getCode() === '23000' ? 'Email sudah terdaftar.' : 'Data gagal disimpan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Registrasi Anggota</title>
</head>
<body>
    <h1>Registrasi Anggota Baru</h1>
    <?php if ($error): ?>
        <p style="color: #c00"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Nama Lengkap <input name="name" required></label><br><br>
        <label>Email <input name="email" type="email" required></label><br><br>
        <label>No. Telepon <input name="phone"></label><br><br>
        <button type="submit">Simpan</button>
        <a href="list.php">Batal</a>
    </form>
</body>
</html>