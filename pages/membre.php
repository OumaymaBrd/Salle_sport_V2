<?php
session_start();
require_once '../config/database.php';
require_once '../models/Member.php';
require_once '../models/User.php';

// Vérification des cookies et redirection
if (!isset($_COOKIE['user_logged']) || $_COOKIE['user_logged'] !== 'true') {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_type'])) {
    $user = new User($db);
    $user->matricule = $_COOKIE['matricule'];
    $userInfo = $user->getUserByMatricule();
    if ($userInfo) {
        $_SESSION['user_type'] = $userInfo['post'];
        $_SESSION['matricule'] = $userInfo['matricule'];
    } else {
        header("Location: login.php");
        exit();
    }
}

if ($_SESSION['user_type'] === 'administration') {
    header("Location: admin_dashboard.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$member = new Member($db);

$matricule = isset($_GET['matricule']) ? $_GET['matricule'] : null;

if (!$matricule) {
    die("Erreur : Matricule non spécifié");
}

$memberInfo = $member->getMemberInfo($matricule);

if (!$memberInfo) {
    die("Erreur : Membre non trouvé");
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reserve'])) {
        $activity_id = $_POST['activity_id'];
        $result = $member->addReservation($matricule, $activity_id);
        $message = $result['message'];
        $messageType = $result['status'];
    } elseif (isset($_POST['cancel'])) {
        $reservation_id = $_POST['reservation_id'];
        $result = $member->cancelReservation($reservation_id, $matricule);
        $message = $result['message'];
        $messageType = $result['status'];
    }
}

$activities = $member->getActivitiesWithAvailability();
$reservations = $member->getReservations($matricule);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activités et Réservations</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <h1 class="text-3xl font-bold mb-4">Bienvenue, <?php echo htmlspecialchars($memberInfo['prenom'] . ' ' . $memberInfo['nom']); ?> (Matricule: <?php echo htmlspecialchars($matricule); ?>)</h1>
        
        <?php if ($message): ?>
            <div class="<?php echo $messageType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <div x-data="{ showReservations: false }">
            <button @click="showReservations = !showReservations" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mb-4">
                Afficher/Masquer les réservations
            </button>

            <div x-show="showReservations">
                <h2 class="text-2xl font-bold mb-2">Mes Réservations</h2>
                <?php if ($reservations->rowCount() == 0): ?>
                    <p>Vous n'avez pas de réservations.</p>
                <?php else: ?>
                    <table class="w-full bg-white shadow-md rounded mb-4">
                        <thead>
                            <tr>
                                <th class="border px-4 py-2">Activité</th>
                                <th class="border px-4 py-2">Date de réservation</th>
                                <th class="border px-4 py-2">Statut</th>
                                <th class="border px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($reservation = $reservations->fetch(PDO::FETCH_ASSOC)): ?>
                                <tr>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($reservation['nom_activite']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($reservation['date_reservation']); ?></td>
                                    <td class="border px-4 py-2"><?php echo htmlspecialchars($reservation['status']); ?></td>
                                    <td class="border px-4 py-2">
                                        <form method="POST">
                                            <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                            <button type="submit" name="cancel" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                                Annuler
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <h2 class="text-2xl font-bold mb-2">Activités disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php while ($activity = $activities->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                        <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($activity['nom_activite']); ?></h3>
                        <p class="mb-4"><?php echo htmlspecialchars($activity['description']); ?></p>
                        <p class="mb-4">Places restantes: <?php echo htmlspecialchars($activity['reste_place']); ?> / <?php echo htmlspecialchars($activity['disponibilite']); ?></p>
                        <?php if ($activity['reste_place'] > 0): ?>
                            <form method="POST">
                                <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                <button type="submit" name="reserve" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Réserver
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-red-500">Complet</p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
            Déconnexion
        </a>
    </div>
    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var alertMessage = document.querySelector('[role="alert"]');
            if (alertMessage) {
                setTimeout(function() {
                    alertMessage.style.display = 'none';
                }, 5000);
            }
        });
    </script>
</body>
</html>

