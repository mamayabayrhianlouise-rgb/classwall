<?php

ini_set('session.cookie_lifetime', '0');
session_start();

$_SESSION['visits'] = ($_SESSION['visits'] ?? 0) + 1;

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $author = trim($_POST['author'] ?? '');
    $body = trim($_POST['body'] ?? '');
    $remember = isset($_POST['remember']);

    if ($author !== '' && $body !== '') {

        $stmt = $pdo->prepare(
            "INSERT INTO messages (author, body)
             VALUES (:author, :body)"
        );

        $stmt->execute([
            ':author' => $author,
            ':body' => $body
        ]);

        if ($remember) {
            setcookie('remembered_name', $author, [
                'expires' => time() + (30 * 24 * 60 * 60),
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        } else {

            setcookie('remembered_name', '', [
                'expires' => time() - 3600,
                'path' => '/'
            ]);
        }

        header('Location: index.php');
        exit;
    }
}

$search = trim($_GET['search'] ?? '');

if ($search !== '') {

    $stmt = $pdo->prepare(
        "SELECT * FROM messages
         WHERE body ILIKE :search
         ORDER BY created_at DESC"
    );

    $stmt->execute([
        ':search' => '%' . $search . '%'
    ]);

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

} else {

    $stmt = $pdo->query(
        "SELECT * FROM messages ORDER BY created_at DESC"
    );

    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$rememberedName = $_COOKIE['remembered_name'] ?? '';

?>

<!DOCTYPE html>
<html>
<head>
    <title>Class Message Board</title>
</head>
<body>

    <h1>Class Message Board</h1>

    <p>
        Your visits:
        <?php echo htmlspecialchars((string) $_SESSION['visits'], ENT_QUOTES, 'UTF-8'); ?>
    </p>

    <h2>Post a Message</h2>

    <form method="POST" action="index.php">

        <label>Your Name:</label><br>
        <input
            type="text"
            name="author"
            value="<?php echo htmlspecialchars($rememberedName, ENT_QUOTES, 'UTF-8'); ?>"
            required
        ><br><br>

        <label>Your Message:</label><br>
        <textarea name="body" required></textarea><br><br>

        <input type="checkbox" name="remember" value="1">
        <label>Remember my name</label><br><br>

        <button type="submit">Post Message</button>

    </form>

    <hr>

    <h2>Search Messages</h2>

    <form method="GET" action="index.php">

        <input
            type="text"
            name="search"
            placeholder="Search messages..."
            value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
        >

        <button type="submit">Search</button>

        <a href="index.php">Show All</a>

    </form>

    <hr>

    <h2>Messages</h2>

    <?php if (count($messages) > 0): ?>

        <?php foreach ($messages as $message): ?>

            <div>
                <h3>
                    <?php echo htmlspecialchars($message['author'], ENT_QUOTES, 'UTF-8'); ?>
                </h3>

                <p>
                    <?php echo nl2br(htmlspecialchars($message['body'], ENT_QUOTES, 'UTF-8')); ?>
                </p>

                <small>
                    <?php echo htmlspecialchars($message['created_at'], ENT_QUOTES, 'UTF-8'); ?>
                </small>
            </div>

            <hr>

        <?php endforeach; ?>

    <?php else: ?>

        <p>No messages found.</p>

    <?php endif; ?>

</body>
</html>
