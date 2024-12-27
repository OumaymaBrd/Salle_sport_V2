<?php
session_start();
require_once '../config/database.php';
require_once '../models/Member.php';
require_once '../models/User.php';

if (!isset($_COOKIE['user_logged']) || $_COOKIE['user_logged'] !== 'true') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['user_type'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $user = new User($db);
    $user->matricule = $_COOKIE['matricule'];
    $userInfo = $user->getUserByMatricule();
    if ($userInfo) {
        $_SESSION['user_type'] = $userInfo['post'];
        $_SESSION['matricule'] = $userInfo['matricule'];
    } else {
        
        header("Location: ../login.php");
        exit();
    }
}


if ($_SESSION['user_type'] === 'administration') {
    header("Location: index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$member = new Member($db);

$matricule = isset($_SESSION['matricule']) ? $_SESSION['matricule'] : null;

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
    <title>Espace Membre - FitZone</title> 
    <link href="../assets/style/style_membre.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-2xl font-bold text-red-gradient">FitZone</span>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4">
                        <?php echo htmlspecialchars($memberInfo['prenom'] . ' ' . $memberInfo['nom']); ?>
                    </span>
                    <a href="../logout.php" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300 ease-in-out">
                        Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h1 class="text-4xl font-bold mb-8 text-center text-red-gradient">Espace Membre</h1>
        
        <?php if ($message): ?>
            <div class="<?php echo $messageType === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700'; ?> px-4 py-3 rounded relative mb-6 animate-fade-in-down" role="alert">
                <span class="block sm:inline"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <div x-data="{ showReservations: false }" class="mb-8">
            <button @click="showReservations = !showReservations" class="bg-red-gradient hover:opacity-90 text-white font-bold py-3 px-6 rounded-full mb-6 transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                <span x-text="showReservations ? 'Masquer mes réservations' : 'Afficher mes réservations'"></span>
            </button>

            <div x-show="showReservations" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">
                <h2 class="text-3xl font-bold mb-4 text-red-600">Mes Réservations</h2>
                <?php if ($reservations->rowCount() == 0): ?>
                    <p class="text-gray-600 italic">Vous n'avez pas de réservations.</p>
                <?php else: ?>
                    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-red-gradient text-white">
                                <tr>
                                    <th class="px-6 py-3 text-left text-sm font-semibold">Activité</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold">Date de réservation</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold">Statut</th>
                                    <th class="px-6 py-3 text-left text-sm font-semibold">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php while ($reservation = $reservations->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($reservation['nom_activite']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($reservation['date_reservation']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $reservation['status'] === 'confirme' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                                <?php echo htmlspecialchars($reservation['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form method="POST">
                                                <input type="hidden" name="reservation_id" value="<?php echo $reservation['id']; ?>">
                                                <button type="submit" name="cancel" class="text-red-600 hover:text-red-900 font-medium">
                                                    Annuler
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <h2 class="text-3xl font-bold mb-6 text-red-600">Activités disponibles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($activity = $activities->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="bg-white shadow-lg rounded-lg overflow-hidden hover-lift">
                    <div class="p-6">
                        <h3 class="text-xl font-bold mb-2 text-red-600"><?php echo htmlspecialchars($activity['nom_activite']); ?></h3>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($activity['description']); ?></p>
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-semibold text-gray-500">Places restantes:</span>
                            <span class="text-lg font-bold <?php echo $activity['reste_place'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                <?php echo htmlspecialchars($activity['reste_place']); ?> / <?php echo htmlspecialchars($activity['disponibilite']); ?>
                            </span>
                        </div>
                        <?php if ($activity['reste_place'] > 0): ?>
                            <form method="POST">
                                <input type="hidden" name="activity_id" value="<?php echo $activity['id']; ?>">
                                <button type="submit" name="reserve" class="w-full bg-red-gradient hover:opacity-90 text-white font-bold py-2 px-4 rounded-full transition duration-300 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                                    Réserver
                                </button>
                            </form>
                        <?php else: ?>
                            <p class="text-center text-red-600 font-semibold">Complet</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4 text-center">
            <p>&copy; 2023 FitZone. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>

