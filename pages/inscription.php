<?php
require_once '../config/database.php';
require_once '../models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user->post = $_POST['post'];
    $user->email = $_POST['email'];
    $user->tel = $_POST['tel'];
    $user->nom = $_POST['nom'];
    $user->prenom = $_POST['prenom'];
    $user->password = $_POST['password'];

    if ($user->register()) {
        $message = "Compte créé avec succès. Votre matricule est : " . $user->matricule;
    } else {
        $message = "Une erreur est survenue lors de la création du compte.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - FitZone</title>
    <link href="../assets/style/style_inscription.css" rel="stylesheet">
</head>
<body>

<a href="accueil.html" style="color:rgb(207, 89, 21); text-decoration: none; font-weight: bold; font-size: 16px; 
position:relative; margin-right:40px;
">
    Accueil
</a>

    

    <div class="container">
        <h1>Inscription</h1>
        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'succès') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="post">Poste</label>
            <select name="post" id="post" required>
                <option value="membre">Membre</option>
                <option value="administration">Administration</option>
            </select>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <label for="tel">Téléphone</label>
            <input type="tel" name="tel" id="tel" required>

            <label for="nom">Nom</label>
            <input type="text" name="nom" id="nom" required>

            <label for="prenom">Prénom</label>
            <input type="text" name="prenom" id="prenom" required>

            <label for="password">Mot de passe</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">S'inscrire</button>
        </form>
    </div>
</body>
</html>