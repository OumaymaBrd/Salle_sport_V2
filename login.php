<?php
session_start();
require_once 'config/database.php';
require_once 'models/Member.php';
require_once 'models/Administrator.php';

$database = new Database();
$db = $database->getConnection();

$user = null;
if(isset($_SESSION['user_id'])) {
    if($_SESSION['user_type'] == 'membre') {
        $user = new Member($db);
    } else {
        $user = new Administrator($db);
    }
    $user->id = $_SESSION['user_id'];
    $user->matricule = $_SESSION['matricule'];
    $user->post = $_SESSION['user_type'];
    $user->nom = $_SESSION['nom'];
    $user->prenom = $_SESSION['prenom'];
}

if(isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $user = new User($db);
    $user->email = $email;
    $user->password = $password;

    if($user->login()) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['matricule'] = $user->matricule;
        $_SESSION['user_type'] = $user->post;
        $_SESSION['nom'] = $user->nom;
        $_SESSION['prenom'] = $user->prenom;
        setcookie("user_logged", "true", time() + (86400 * 30), "/");
        header("Location: index.php");
    } else {
        $error = "Invalid credentials";
    }
}

if(isset($_GET['logout'])) {
    session_destroy();
    setcookie("user_logged", "", time() - 3600, "/");
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Add Tailwind CSS or your custom CSS -->
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-96">
        <h2 class="text-2xl font-bold mb-4">Sign In</h2>
        <?php if ($error_message): ?>
            <div class="text-red-600 mb-4"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-gray-700">Email</label>
                <input type="email" id="email" name="email" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700">Password</label>
                <input type="password" id="password" name="password" class="w-full p-2 border border-gray-300 rounded" required>
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg w-full">Log In</button>
        </form>
    </div>
</body>
</html>
