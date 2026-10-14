<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

$members = $pdo->query('SELECT MemberID, Name, Email, Phone FROM Members ORDER BY Name')->fetchAll();

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Anggota</title>
</head>
<body>
    <h1>Daftar Anggota Perpustakaan</h1>
    <p><a href="create.php">Tambah Anggota Baru</a> | <a href="../index.php">Kembali ke Katalog</a></p>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID Anggota</th>
                <th>Nama</th>
                <th>Email</th>
                <th>No. Telepon</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($members as $m): ?>
            <tr>
                <td><?= (int) $m['MemberID'] ?></td>
                <td><?= e($m['Name']) ?></td>
                <td><?= e($m['Email']) ?></td>
                <td><?= e($m['Phone']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>