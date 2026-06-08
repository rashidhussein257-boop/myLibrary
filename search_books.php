<?php
session_start();
require_once '../config/db.php';

// Protection: Ensure the user is logged in and is strictly a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$message = "";

// BOOK BORROWING REQUEST LOGIC
if (isset($_GET['action']) && $_GET['action'] == 'borrow' && isset($_GET['book_id'])) {
    $book_id = intval($_GET['book_id']);
    $today = date('Y-m-d');

    try {
        // 1. Check if this student already has a pending request for this exact book
        $check_req = $conn->prepare("SELECT COUNT(*) FROM borrowed_books WHERE user_id = :user_id AND book_id = :book_id AND status = 'pending'");
        $check_req->execute([':user_id' => $user_id, ':book_id' => $book_id]);
        
        if ($check_req->fetchColumn() > 0) {
            $message = "<p style='color: #856404; padding: 10px; background: #fff3cd; border-radius: 5px;'>You have already submitted a request for this book. Please wait for Admin approval!</p>";
        } else {
            // 2. Insert a new borrowing request entry with a status state of 'pending'
            $ins = $conn->prepare("INSERT INTO borrowed_books (user_id, book_id, borrow_date, status) VALUES (:user_id, :book_id, :borrow_date, 'pending')");
            $ins->execute([':user_id' => $user_id, ':book_id' => $book_id, ':borrow_date' => $today]);

            $message = "<p style='color: #004085; padding: 10px; background: #cce5ff; border-radius: 5px;'>Your request has been sent! Awaiting Admin verification.</p>";
        }
    } catch (PDOException $e) {
        $message = "Error: " . $e->getMessage();
    }
}

// Fetch all books in the inventory catalog
$books = $conn->query("SELECT * FROM books")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search & Borrow Books</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        
        <aside class="sidebar" style="background-color: #0f172a;">
            <div class="sidebar-brand"><h2><i class="fa-solid fa-graduation-cap"></i> Student Portal</h2></div>
            <ul class="sidebar-menu">
                <li><a href="student_index.php"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="search_books.php" class="active"><i class="fa-solid fa-magnifying-glass"></i> Search & Borrow</a></li>
                <li class="logout-link"><a href="../logout/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content" style="padding: 30px;">
            <h2>Library Books Catalogue</h2>
            <br>
            <?php echo $message; ?>
            <br>
            <div class="table-container">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['title']); ?></td>
                            <td><?php echo htmlspecialchars($b['author']); ?></td>
                            <td><?php echo htmlspecialchars($b['category']); ?></td>
                            <td>
                                <?php if($b['status'] == 'available'): ?>
                                    <span class="badge badge-success">Available</span>
                                <?php else: ?>
                                    <span class="badge badge-warning" style="background-color: #fee2e2; color: #dc2626;">Borrowed</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($b['status'] == 'available'): ?>
                                    <a href="search_books.php?action=borrow&book_id=<?php echo $b['id']; ?>" class="badge" style="background-color: #0284c7; color: white; text-decoration: none;">Borrow Book</a>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 13px;">Unavailable</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>