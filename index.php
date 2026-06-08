<?php
session_start();

// 1. Protection: If the user is not logged in, redirect them back to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit;
}

// 2. Extra Protection: Ensure the logged-in user is strictly an Admin
if ($_SESSION['role'] !== 'admin') {
    echo "<h2>Sorry! This page is strictly for Admins only.</h2>";
    echo "<a href='../logout/logout.php'>Click here to logout</a>";
    exit;
}

// Future database queries to fetch real-time statistics
$total_books = 120;       // Placeholder static data
$total_students = 45;     // Placeholder static data
$borrowed_books = 18;     // Placeholder static data
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Library System</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h2><i class="fa-solid fa-book-open"></i> E-Library</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="approve_borrow.php"><i class="fa-solid fa-hand-holding-hand"></i> Book Requests</a></li>
                <li><a href="#"><i class="fa-solid fa-users"></i> Manage Students</a></li>
                <li><a href="#"><i class="fa-solid fa-book"></i> Borrowed Books</a></li>
                <li><a href="#"><i class="fa-solid fa-gear"></i> Settings</a></li>
                <li class="logout-link"><a href="../logout/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content">
            
            <header class="top-navbar">
                <div class="welcome-text">
                    Hello, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (Admin)
                </div>
                <div class="user-profile">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
            </header>

            <section class="stats-container">
                
                <div class="card card-blue">
                    <div class="card-icon">
                        <i class="fa-solid fa-book"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $total_books; ?></h3>
                        <p>Total Books</p>
                    </div>
                </div>

                <div class="card card-green">
                    <div class="card-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $total_students; ?></h3>
                        <p>Registered Students</p>
                    </div>
                </div>

                <div class="card card-orange">
                    <div class="card-icon">
                        <i class="fa-solid fa-clock-history"></i>
                    </div>
                    <div class="card-info">
                        <h3><?php echo $borrowed_books; ?></h3>
                        <p>Currently Borrowed</p>
                    </div>
                </div>

            </section>

            <section class="content-body">
                <div class="table-container">
                    <h3>Recent Book Borrowings</h3>
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>Book Title</th>
                                <th>Borrow Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Juma Hamisi</td>
                                <td>Introduction to PHP plain</td>
                                <td>08-06-2026</td>
                                <td><span class="badge badge-warning" style="background-color: #fef9c3; color: #a16207;">Not Returned</span></td>
                            </tr>
                            <tr>
                                <td>Asha Said</td>
                                <td>Database Systems (MySQL)</td>
                                <td>05-06-2026</td>
                                <td><span class="badge badge-success" style="background-color: #dcfce7; color: #15803d;">Returned</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>

    </div>

</body>
</html>