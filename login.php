<?php
session_start();
require_once 'config/database.php';
require_once 'models/Administrator.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $admin = new Administrator($db);
    $admin->matricule = $_POST['matricule'];
    $admin->mot_de_passe = $_POST['mot_de_passe'];

    if ($admin->login()) {
        $_SESSION['user_id'] = $admin->id;
        $_SESSION['matricule'] = $admin->matricule;
        $_SESSION['user_type'] = 'administration';
        $_SESSION['nom'] = $admin->nom;
        $_SESSION['prenom'] = $admin->prenom;
        header("Location: index.php");
        exit();
    } else {
        $error = "Matricule ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded shadow-md w-96">
            <h2 class="text-2xl font-bold mb-6 text-center">Connexion Administrateur</h2>
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-4">
                    <label for="matricule" class="block text-gray-700 text-sm font-bold mb-2">Matricule</label>
                    <input type="text" id="matricule" name="matricule" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-6">
                    <label for="mot_de_passe" class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Se connecter
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

