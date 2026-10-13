<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

$members = $pdo->query('SELECT MemberID, Name FROM Members ORDER BY Name')->fetchAll();
$books = $pdo->query('SELECT ISBN, Title, Quantity FROM Books WHERE Quantity > 0 ORDER BY Title')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $memberId = (int) ($_POST['member_id'] ?? 0);
    $isbn = trim($_POST['isbn'] ?? '');

    if ($memberId <= 0 || $isbn === '') {
        $error = 'Silakan pilih Anggota dan Buku yang valid.';
    } else {
        $pdo->beginTransaction();
        try {
            $upd = $pdo->prepare('UPDATE Books SET Quantity = Quantity - 1 WHERE ISBN = ? AND Quantity > 0');
            $upd->execute([$isbn]);

            if ($upd->rowCount() === 0) {
                throw new RuntimeException('Stok buku habis atau ISBN tidak ditemukan.');
            }

            $ins = $pdo->prepare('INSERT INTO Loans (MemberID, ISBN, LoanDate) VALUES (?, ?, CURDATE())');
            $ins->execute([$memberId, $isbn]);

            $pdo->commit();
            $success = 'Peminjaman berhasil dicatat!';
            
            // Refresh daftar buku setelah stok berkurang
            $books = $pdo->query('SELECT ISBN, Title, Quantity FROM Books WHERE Quantity > 0 ORDER BY Title')->fetchAll();
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Transaksi Peminjaman Buku</title>
</head>
<body>
    <h1>Transaksi Peminjaman Buku</h1>
    <?php if ($error): ?><p style="color: #c00"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color: #080"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <form method="post">
        <label>Pilih Anggota:
            <select name="member_id" required>
                <option value="">-- Pilih Anggota --</option>
                <?php foreach ($members as $m): ?>
                    <option value="<?= (int)$m['MemberID'] ?>"><?= htmlspecialchars($m['Name']) ?></option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <label>Pilih Buku:
            <select name="isbn" required>
                <option value="">-- Pilih Buku --</option>
                <?php foreach ($books as $b): ?>
                    <option value="<?= htmlspecialchars($b['ISBN']) ?>">
                        <?= htmlspecialchars($b['Title']) ?> (Stok: <?= (int)$b['Quantity'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Proses Peminjaman</button>
        <a href="../index.php">Batal</a>
    </form>
</body>
</html>