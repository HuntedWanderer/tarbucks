<?php
require '_base.php'; // 1. Use standard base file

// --- DELETE MEMBER LOGIC ---
if (is_post() && isset($_POST['delete'])) {
    
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        temp('error', 'Invalid ID');
        redirect('member_list.php');
    }

    try {
        // A. Find the photo filename FIRST (before deleting the user)
        $stm = $_db->prepare("SELECT photo FROM member WHERE id = ?");
        $stm->execute([$id]);
        $targetMember = $stm->fetch();

        // B. Delete from S3 (if they have a photo)
        if ($targetMember && !empty($targetMember->photo)) {
            $bucket = 'tarbucks-bucket'; // <--- UPDATE THIS!
            $s3Path = "s3://{$bucket}/images/" . $targetMember->photo;
            // Run the AWS delete command
            exec("aws s3 rm \"{$s3Path}\" --region us-east-1 > /dev/null 2>&1");
        }

        // C. Now delete from Database
        $stmt = $_db->prepare("DELETE FROM member WHERE id = ?");
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            temp('success', 'Member & Photo Deleted Successfully');
        } else {
            temp('info', 'Error: Member not found');
        }
    } catch (PDOException $e) {
        temp('error', 'System Error');
        error_log("Delete member error: " . $e->getMessage()); 
    }

    redirect('member_list.php'); 
}

// --- SEARCH / LIST LOGIC ---
$search = $_GET['search'] ?? '';
$sql = "SELECT * FROM member";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE user_id LIKE ? OR email LIKE ? OR fav_person LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$stmt = $_db->prepare($sql);
$stmt->execute($params);
// Fetch as Objects to match your HTML usage ($member->id)
$arr = $stmt->fetchAll(PDO::FETCH_OBJ);

$_title = 'Member Listing';
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Listing | Tarbuck Coffee</title>
    <link rel="icon" href="/images/logo.webp">
    <link rel="stylesheet" href="/css/style.css"> 
    <style>
        :root {
            --primary-green:#4caf50;
            --text-dark: #2E2E2E;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .member-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .member-table th {
            background: var(--primary-green);
            color: white;
            padding: 15px;
            text-align: left;
        }
        .member-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        .member-photo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 2px solid var(--primary-green);
        }
        .delete-btn {
            padding: 8px 15px;
            background: #e74c3c;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: 0.3s;
        }
        .delete-btn:hover { background: #c0392b; }
        
        /* Search Form Styles */
        .search-form { margin-bottom: 20px; }
        .search-form input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .search-form button {
            padding: 8px 15px;
            background-color: var(--primary-green);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    
    <header style="background:#333; color:white; padding:10px; text-align:center;">
        <h1>Tarbuck Admin Panel</h1>
        <a href="product_listing.php" style="color:white; margin:0 10px;">Products</a>
        <a href="member_list.php" style="color:yellow; margin:0 10px;">Members</a>
        <a href="/" style="color:white; margin:0 10px;">Logout</a>
    </header>

    <div class="container">
        
        <div id="info"><?= temp('info') ?></div>
        <div id="success" style="color:green"><?= temp('success') ?></div>
        <div id="error" style="color:red"><?= temp('error') ?></div>

        <form method="get" action="member_list.php" class="search-form">
            <input type="text" name="search" placeholder="Search members..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button type="submit">Search</button>
        </form>

        <table class="member-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User Name</th>
                    <th>Email</th>
                    <th>Fav Person</th>
                    <th>Photo</th>
                    <th>Role</th>
                    <th>Operation</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($arr as $member): ?>
                <tr>
                    <td><?= htmlspecialchars($member->id) ?></td>
                    <td><?= htmlspecialchars($member->user_id) ?></td>
                    <td><?= htmlspecialchars($member->email) ?></td>
                    <td><?= htmlspecialchars($member->fav_person) ?></td>
                    <td>
                        <?php if (!empty($member->photo)): ?>
                            <img src="view.php?image=<?= htmlspecialchars($member->photo) ?>" 
                                 class="member-photo" 
                                 alt="Member Photo">
                        <?php else: ?>
                            <div style="color:#999; font-size:0.8em;">No Photo</div>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($member->role) ?></td>
                    <td>
                        <form class="delete-form" method="post" 
                              onsubmit="return confirm('Are you sure you want to delete this member? This cannot be undone.');">
                            <input type="hidden" name="id" value="<?= $member->id ?>">
                            <button type="submit" name="delete" class="delete-btn">DELETE</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>