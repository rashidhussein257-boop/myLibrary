<?php
session_start();
require_once '../config/db.php'; // Connect to the database

// 1. Protection: If the user is not logged in, redirect them back to the login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit;
}

// 2. Extra Protection: Ensure the logged-in user role is strictly a Student
if ($_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // A. Count all books this student has ever requested/borrowed historically
    $stmt1 = $conn->prepare("SELECT COUNT(*) FROM borrowed_books WHERE user_id = :user_id");
    $stmt1->execute([':user_id' => $user_id]);
    $my_borrowed_books = $stmt1->fetchColumn();

    // B. Count books currently possessed by the student ('mkononi')
    $stmt2 = $conn->prepare("SELECT COUNT(*) FROM borrowed_books WHERE user_id = :user_id AND status = 'mkononi'");
    $stmt2->execute([':user_id' => $user_id]);
    $pending_returns = $stmt2->fetchColumn();

    // C. Fetch the list of books currently in the student's possession to display in the data table
    $stmt3 = $conn->prepare("
        SELECT b.title, bb.borrow_date, bb.return_date, bb.status 
        FROM borrowed_books bb
        JOIN books b ON bb.book_id = b.id
        WHERE bb.user_id = :user_id AND bb.status = 'mkononi'
    ");
    $stmt3->execute([':user_id' => $user_id]);
    $my_books_list = $stmt3->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Library System</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">
        
        <aside class="sidebar" style="background-color: #0f172a;">
            <div class="sidebar-brand">
                <h2><i class="fa-solid fa-graduation-cap"></i> Student Portal</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="student_index.php" class="active"><i class="fa-solid fa-house"></i> Home</a></li>
                <li><a href="search_books.php"><i class="fa-solid fa-magnifying-glass"></i> Search & Borrow</a></li>
                <li class="logout-link"><a href="../logout/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="top-navbar">
                <div class="welcome-text">
                    Welcome back, student <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
                </div>
                <div class="user-profile"><i class="fa-solid fa-user-graduate" style="color: #0ea5e9;"></i></div>
            </header>

            <section class="stats-container">
                <div class="card card-blue">
                    <div class="card-icon"><i class="fa-solid fa-book-open-reader"></i></div>
                    <div class="card-info">
                        <h3><?php echo $my_borrowed_books; ?></h3>
                        <p>Total Books Borrowed</p>
                    </div>
                </div>

                <div class="card card-orange">
                    <div class="card-icon"><i class="fa-solid fa-clock"></i></div>
                    <div class="card-info">
                        <h3><?php echo $pending_returns; ?></h3>
                        <p>Books Currently with Me</p>
                    </div>
                </div>
            </section>

            <section class="content-body">
                <div class="table-container">
                    <h3>Books Currently in My Possession</h3>
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Borrow Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                    <tbody>
                    <?php if (count($my_books_list) > 0): ?>
                        <?php foreach ($my_books_list as $book): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo $book['borrow_date']; ?></td>
                                <td><span class="badge badge-warning" style="background-color: #fef9c3; color: #a16207;">On Hand</span></td>
                            </tr>
                        <?php endforeach; ?> 
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: #64748b;">You do not have any books in your possession right now. Go to the "Search & Borrow" menu to request a book!</td>
                        </tr>
                    <?php endif; ?>
                     </tbody>
                   </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>