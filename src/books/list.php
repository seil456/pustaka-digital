<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

$books = $pdo->query('SELECT ISBN, Title, Author, Genre, Quantity FROM Books ORDER BY Title')->fetchAll();

function e(?string $s): string {
    return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Buku</title>
</head>
<body>
    <h1>Daftar Buku Perpustakaan</h1>
    <p><a href="create.php">Tambah Buku Baru</a></p>
    <table border="1" cellpadding="6">
        <thead>
            <tr><th>ISBN</th><th>Judul</th><th>Penulis</th><th>Genre</th><th>Stok</th></tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
            <tr>
                <td><?= e($book['ISBN']) ?></td>
                <td><?= e($book['Title']) ?></td>
                <td><?= e($book['Author']) ?></td>
                <td><?= e($book['Genre']) ?></td>
                <td><?= (int) $book['Quantity'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
