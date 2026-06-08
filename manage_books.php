<?php
session_start();
require_once '../config/db.php'; // Connect to the database

// 1. Protection: Ensure the user is logged in and is strictly an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login/login.php");
    exit;
}

$message = "";

// 2. NEW BOOK REGISTRATION LOGIC (INSERT)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_book'])) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $category = trim($_POST['category']);

    try {
        $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category, status) VALUES (:title, :author, :isbn, :category, 'available')");
        $stmt->execute([
            ':title' => $title,
            ':author' => $author,
            ':isbn' => $isbn,
            ':category' => $category
        ]);
        $message = "<p style='color: green; padding: 10px; background: #dcfce7; border-radius: 5px; margin-bottom: 15px;'>Book added successfully!</p>";
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) { // Duplicate entry error code for unique ISBN constraint
            $message = "<p style='color: red; padding: 10px; background: #fee2e2; border-radius: 5px; margin-bottom: 15px;'>Error: This ISBN number is already in use!</p>";
        } else {
            $message = "<p style='color: red; padding: 10px; background: #fee2e2; border-radius: 5px; margin-bottom: 15px;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}

// 3. BOOK REMOVAL LOGIC (DELETE)
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $book_id = intval($_GET['id']);

    try {
        $stmt = $conn->prepare("DELETE FROM books WHERE id = :id");
        $stmt->execute([':id' => $book_id]);
        header("Location: manage_books.php"); // Refresh the page instantly post deletion
        exit;
    } catch (PDOException $e) {
        $message = "<p style='color: red; padding: 10px; background: #fee2e2; border-radius: 5px; margin-bottom: 15px;'>You cannot delete a book that is currently borrowed!</p>";
    }
}

// 4. FETCH ALL CURRENT BOOKS IN CATALOGUE TO DISPLAY IN THE TABLE
$books = $conn->query("SELECT * FROM books ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Books - Admin</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional custom admin form responsive styles */
        .admin-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            align-items: end;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            margin-bottom: 5px;
            font-size: 14px;
            color: #475569;
            font-weight: 600;
        }
        .form-group input {
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            font-size: 15px;
        }
        .btn-submit {
            background-color: #16a34a;
            color: white;
            border: none;
            padding: 11px;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-submit:hover { background-color: #15803d; }
        .btn-delete {
            background-color: #ef4444;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 13px;
        }
        .btn-delete:hover { background-color: #b91c1c; }
    </style>
</head>
<body>

    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <h2><i class="fa-solid fa-book-open"></i> E-Library</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="index.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="manage_books.php" class="active"><i class="fa-solid fa-book"></i> Manage Books</a></li>
                <li class="logout-link"><a href="../logout/logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
            </ul>
        </aside>

        <main class="main-content" style="padding: 30px;">
            <h2>Library Books Management</h2>
            <br>
            
            <?php echo $message; ?>

            <h3>Add New Book</h3>
            <form method="POST" action="" class="admin-form">
                <div class="form-group">
                    <label>Book Title</label>
                    <input type="text" name="title" required placeholder="Ex. Intro to PHP">
                </div>
                <div class="form-group">
                    <label>Author Name</label>
                    <input type="text" name="author" required placeholder="Ex. Juma Said">
                </div>
                <div class="form-group">
                    <label>ISBN Number</label>
                    <input type="text" name="isbn" required placeholder="Ex. 98765432">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" required placeholder="Ex. Programming">
                </div>
                <button type="submit" name="add_book" class="btn-submit"><i class="fa-solid fa-plus"></i> Add Book</button>
            </form>

            <div class="table-container">
                <h3>All Registered Books</h3>
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Book Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($books) > 0): ?>
                            <?php foreach ($books as $b): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($b['title']); ?></td>
                                <td><?php echo htmlspecialchars($b['author']); ?></td>
                                <td><?php echo htmlspecialchars($b['isbn']); ?></td>
                                <td><?php echo htmlspecialchars($b['category']); ?></td>
                                <td>
                                    <?php if($b['status'] == 'available'): ?>
                                        <span class="badge badge-success">Available</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning" style="background-color: #fee2e2; color: #dc2626;">Borrowed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="manage_books.php?action=delete&id=<?php echo $b['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this book?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" style="text-align: center; color: #64748b;">No books found in the library database at this moment.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

</body>
</html>