<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isbn = trim($_POST['isbn'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $genre = trim($_POST['genre'] ?? '');
    $qty = filter_var($_POST['quantity'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

    if ($isbn === '' || $title === '' || $author === '' || $qty === false) {
        $error = 'ISBN, judul, penulis, dan stok (bilangan >= 0) wajib diisi.';
    } else {
        try {
            $stmt = $pdo->prepare('INSERT INTO Books (ISBN, Title, Author, Genre, Quantity) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$isbn, $title, $author, $genre ?: null, $qty]);
            header('Location: list.php');
            exit;
        } catch (PDOException $e) {
            $error = $e->getCode() === '23000' ? 'ISBN sudah terdaftar.' : 'Data gagal disimpan.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tambah Buku</title>
</head>
<body>
    <h1>Tambah Buku Baru</h1>
    <?php if ($error): ?>
        <p style="color: #c00"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <label>ISBN <input name="isbn" required></label><br><br>
        <label>Judul <input name="title" required></label><br><br>
        <label>Penulis <input name="author" required></label><br><br>
        <label>Genre <input name="genre"></label><br><br>
        <label>Stok <input name="quantity" type="number" min="0" value="1" required></label><br><br>
        <button type="submit">Simpan</button>
        <a href="list.php">Batal</a>
    </form>
</body>
</html>