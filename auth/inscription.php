<?php
session_start();
require_once '../config/database.php';
require_once '../models/User.php';
require_once '../models/Member.php';
require_once '../models/Administrator.php';

$database = new Database();
$db = $database->getConnection();

$error = "";
$success = "";

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if(isset($_POST['register'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Invalid CSRF token";
    } else {
        // Input validation
        $nom = filter_var($_POST['nom'], FILTER_SANITIZE_STRING);
        $prenom = filter_var($_POST['prenom'], FILTER_SANITIZE_STRING);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $tel = filter_var($_POST['tel'], FILTER_SANITIZE_STRING);
        $password = $_POST['password'];

        if (!$email) {
            $error = "Email invalide";
        } elseif (strlen($password) < 8) {
            $error = "Le mot de passe doit contenir au moins 8 caractères";
        } else {
            // Default to member type if not specified
            $user_type = isset($_POST['type']) ? $_POST['type'] : 'membre';
            
            // Create appropriate object based on type
            $user = ($user_type == 'membre') ? new Member($db) : new Administrator($db);
            
            // Set properties
            $user->nom = $nom;
            $user->prenom = $prenom;
            $user->email = $email;
            $user->tel = $tel;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->post = $user_type;
            
            // Attempt registration
            if($user->register()) {
                $success = "Inscription réussie !";
            } else {
                $error = "Erreur lors de l'inscription";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FightClub - Inscription</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-black text-white">


    <section class="min-h-screen flex items-center justify-center pt-20">
        <div class="bg-zinc-900 p-8 rounded-lg w-full max-w-md">
            <h2 class="text-3xl font-bold text-center mb-8">Inscription</h2>

            <?php if ($error): ?>
                <div class="bg-red-600/20 border border-red-600 text-red-600 px-4 py-2 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="bg-green-600/20 border border-green-600 text-green-600 px-4 py-2 rounded mb-4">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <div>
                    <label for="nom" class="block text-sm font-medium mb-2">Nom</label>
                    <input type="text" id="nom" name="nom" required 
                           class="w-full px-4 py-3 bg-black rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                </div>

                <div>
                    <label for="prenom" class="block text-sm font-medium mb-2">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required 
                           class="w-full px-4 py-3 bg-black rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-4 py-3 bg-black rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                </div>

                <div>
                    <label for="tel" class="block text-sm font-medium mb-2">Téléphone</label>
                    <input type="tel" id="tel" name="tel" required 
                           class="w-full px-4 py-3 bg-black rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-2">Mot de passe</label>
                    <input type="password" id="password" name="password" required minlength="8"
                           class="w-full px-4 py-3 bg-black rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600">
                </div>

                <button type="submit" name="register"
                        class="w-full bg-red-600 text-white py-3 rounded-lg hover:bg-red-700 transition">
                    S'inscrire
                </button>
            </form>

            <p class="mt-4 text-center text-gray-400">
                Déjà membre ? 
                <a href="login.php" class="text-red-600 hover:text-red-500">Connectez-vous</a>
            </p>
        </div>
    </section>

    <script>
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('nav');
            if (window.scrollY > 0) {
                nav.classList.add('bg-black');
            } else {
                nav.classList.remove('bg-black');
            }
        });
    </script>
</body>
</html>