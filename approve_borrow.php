<?php
session_start();
require_once '../config/db.php';

// Protection: Strictly restrict page access to Admin users only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/login.php");
    exit;
}

// 1. PROCESS REQUEST APPROVAL ("ACCEPT")
if (isset($_GET['action']) && $_GET['action'] == 'accept' && isset($_GET['req_id'])) {
    $req_id = intval($_GET['req_id']);

    try {
        // Fetch the corresponding book_id for this specific request first
        $stmt = $conn->prepare("SELECT book_id FROM borrowed_books WHERE id = :id");
        $stmt->execute([':id' => $req_id]);
        $book_id = $stmt->fetchColumn();

        // A. Change the borrow request status to 'mkononi' (Possessed/Active)
        $upd_req = $conn->prepare("UPDATE borrowed_books SET status = 'mkononi' WHERE id = :id");
        $upd_req->execute([':id' => $req_id]);

        // B. Update the book state to 'borrowed' so it becomes hidden from other library students
        $upd_book = $conn->prepare("UPDATE books SET status = 'borrowed' WHERE id = :book_id");
        $upd_book->execute([':book_id' => $book_id]);

        header("Location: approve_borrow.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// 2. PROCESS REQUEST DECLINATION ("REJECT")
if (isset($_GET['action']) && $_GET['action'] == 'reject' && isset($_GET['req_id'])) {
    $req_id = intval($_GET['req_id']);

    try {
        // Switch the request status to 'rejected' (The book remains 'available' in the catalog)
        $upd_req = $conn->prepare("UPDATE borrowed_books SET status = 'rejected' WHERE id = :id");
        $upd_req->execute([':id' => $req_id]);

        header("Location: approve_borrow.php");
        exit;
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}

// 3. FETCH ALL OUTSTANDING PENDING BORROWING REQUESTS
$requests = $conn->query("
    SELECT bb.id AS req_id, u.username, b.title, bb.borrow_date, bb.status 
    FROM borrowed_books bb
    JOIN users u ON bb.user_id = u.id
    JOIN books b ON bb.book_id = b.id
    WHERE bb.status = 'pending'
    ORDER BY bb.id DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Book Requests</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .btn-accept { background-color: #16a34a; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; margin-right: 5px; }
        .btn-reject { background-color: #ef4444; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-weight: bold; }
        .btn-accept:hover { background-color: #15803d; }
        .btn-reject:hover { background-color: #b91c1c; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-brand"><h2><i class="fa-solid fa-book-open"></i> E-Library</h2></div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="manage_books.php"><i class="fa-solid fa-book"></i> Manage Books</a></li>
                <li><a href="approve_borrow.php" class="active"><i class="fa-solid fa-hand-holding-hand"></i> Book Requests</a></li>
                <li class="logout-link"><a href="../logout/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content" style="padding: 30px;">
            <h2>Pending Book Borrowing Requests</h2>
            <br>
            <div class="table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Book Title</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($requests) > 0): ?>
                            <?php foreach ($requests as $r): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($r['username']); ?></td>
                                <td><?php echo htmlspecialchars($r['title']); ?></td>
                                <td><?php echo $r['borrow_date']; ?></td>
                                <td><span class="badge badge-warning" style="background-color: #fef9c3; color: #a16207;">Pending</span></td>
                                <td>
                                    <a href="approve_borrow.php?action=accept&req_id=<?php echo $r['req_id']; ?>" class="btn-accept" onclick="return confirm('Approve this request?')"><i class="fa-solid fa-check"></i> Accept</a>
                                    <a href="approve_borrow.php?action=reject&req_id=<?php echo $r['req_id']; ?>" class="btn-reject" onclick="return confirm('Decline this request?')"><i class="fa-solid fa-xmark"></i> Reject</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center; color: #64748b;">No incoming requests found at the moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>