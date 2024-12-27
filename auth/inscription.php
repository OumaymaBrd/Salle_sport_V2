<?php
// register.php
session_start();
require '../config/database.php';
require '../models/User.php';
require  '../models/Member.php';
require '../models/Administrator.php';

$database = new Database();
$db = $database->getConnection();

$error = "";
$success = "";

if(isset($_POST['register'])) {
    $user_type = $_POST['type']; 

    if($user_type == 'membre') {
        $user = new Member($db);
    } else {
        $user = new Administrator($db);
    }
    
    
    $user->matricule = $_POST['matricule'];
    $user->post = $user_type;
    $user->email = $_POST['email'];
    $user->tel = $_POST['tel'];
    $user->nom = $_POST['nom'];
    $user->prenom = $_POST['prenom'];
    $user->password = $_POST['password'];
    
    if($user->register()) {
        $success = "Inscription réussie !";
    } else {
        $error = "Erreur lors de l'inscription";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Inscription</h1>
        
        <?php if($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Type de compte</label>
                <select name="type" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                    <option value="membre">Membre</option>
                    <option value="administration">Administration</option>
                </select>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Matricule</label>
                <input type="text" name="matricule" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Téléphone</label>
                <input type="tel" name="tel" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Nom</label>
                <input type="text" name="nom" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Prénom</label>
                <input type="text" name="prenom" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Mot de passe</label>
                <input type="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
            </div>
            
            <div class="flex items-center justify-between">
                <button type="submit" name="register" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    S'inscrire
                </button>
                <a href="login.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                    Déjà inscrit ? Connectez-vous
                </a>
            </div>
        </form>
    </div>
</body>
</html>