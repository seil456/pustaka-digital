<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

// Ambil transaksi peminjaman yang belum dikembalikan (ReturnDate NULL)
$activeLoans = $pdo->query('
    SELECT L.LoanID, L.ISBN, L.LoanDate, M.Name AS MemberName, B.Title AS BookTitle
    FROM Loans L
    JOIN Members M ON L.MemberID = M.MemberID
    JOIN Books B ON L.ISBN = B.ISBN
    WHERE L.ReturnDate IS NULL
    ORDER BY L.LoanDate DESC
')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanId = (int) ($_POST['loan_id'] ?? 0);

    if ($loanId <= 0) {
        $error = 'Pilih transaksi peminjaman yang valid.';
    } else {
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('SELECT ISBN FROM Loans WHERE LoanID = ? AND ReturnDate IS NULL');
            $stmt->execute([$loanId]);
            $loan = $stmt->fetch();

            if (!$loan) {
                throw new RuntimeException('Transaksi peminjaman tidak ditemukan atau sudah dikembalikan.');
            }

            // Update tanggal pengembalian
            $updLoan = $pdo->prepare('UPDATE Loans SET ReturnDate = CURDATE() WHERE LoanID = ?');
            $updLoan->execute([$loanId]);

            // Tambahkan kembali stok buku (+1)
            $updBook = $pdo->prepare('UPDATE Books SET Quantity = Quantity + 1 WHERE ISBN = ?');
            $updBook->execute([$loan['ISBN']]);

            $pdo->commit();
            $success = 'Buku berhasil dikembalikan!';

            // Refresh daftar peminjaman aktif
            $activeLoans = $pdo->query('
                SELECT L.LoanID, L.ISBN, L.LoanDate, M.Name AS MemberName, B.Title AS BookTitle
                FROM Loans L
                JOIN Members M ON L.MemberID = M.MemberID
                JOIN Books B ON L.ISBN = B.ISBN
                WHERE L.ReturnDate IS NULL
                ORDER BY L.LoanDate DESC
            ')->fetchAll();
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
    <title>Transaksi Pengembalian Buku</title>
</head>
<body>
    <h1>Pengembalian Buku</h1>
    <?php if ($error): ?><p style="color: #c00"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color: #080"><?= htmlspecialchars($success) ?></p><?php endif; ?>

    <form method="post">
        <label>Pilih Transaksi Peminjaman:
            <select name="loan_id" required>
                <option value="">-- Pilih Buku yang Dipinjam --</option>
                <?php foreach ($activeLoans as $l): ?>
                    <option value="<?= (int)$l['LoanID'] ?>">
                        <?= htmlspecialchars($l['MemberName']) ?> - <?= htmlspecialchars($l['BookTitle']) ?> (Tgl Pinjam: <?= htmlspecialchars($l['LoanDate']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </label><br><br>

        <button type="submit">Proses Pengembalian</button>
        <a href="../index.php">Batal</a>
    </form>
</body>
</html>