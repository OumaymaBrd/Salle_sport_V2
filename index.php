<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';
require_once 'models/Administrator.php'; // Update 1: Replaced Member with Administrator
require_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();

$user = null;
if(isset($_SESSION['user_id'])) {
    
    $user = new Administrator($db); // Update 2: Using Administrator class
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
        header("Location: index.php?matricule=" . $user->matricule);
        exit();
    } else {
        $error = "Identifiants invalides";
    }
}

if(isset($_GET['logout'])) {
    session_destroy();
    setcookie("user_logged", "", time() - 3600, "/");
    header("Location: index.php");
    exit();
}

// Handle POST requests for various actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = ['status' => 'error', 'message' => 'Action non reconnue'];

    if (isset($_POST['add_activity']) && $user instanceof Administrator) {
        $nom_activite = $_POST['nom_activite'];
        $description = $_POST['description'];
        $disponibilite = $_POST['disponibilite'];
        $result = $user->addActivity($nom_activite, $description, $_SESSION['matricule'], $disponibilite);
        if ($result) {
            $response = ['status' => 'success', 'message' => 'Activité ajoutée avec succès', 'data' => $result];
        } else {
            $response = ['status' => 'error', 'message' => 'Erreur lors de l\'ajout de l\'activité'];
        }
    } elseif (isset($_POST['update_reservation']) && $user instanceof Administrator) {
        $reservation_id = $_POST['reservation_id'];
        $status = $_POST['status'];
        $result = $user->updateReservationStatus($reservation_id, $status);
        if ($result) {
            $response = ['status' => 'success', 'message' => 'Statut de réservation mis à jour avec succès'];
        } else {
            $response = ['status' => 'error', 'message' => 'Erreur lors de la mise à jour du statut de réservation'];
        }
    } elseif (isset($_POST['update_activity']) && $user instanceof Administrator) {
        $activity_id = $_POST['activity_id'];
        $nom_activite = $_POST['nom_activite'];
        $description = $_POST['description'];
        $disponibilite = $_POST['disponibilite'];
        $result = $user->updateActivity($activity_id, $nom_activite, $description, $disponibilite);
        if ($result) {
            $response = ['status' => 'success', 'message' => 'Activité mise à jour avec succès'];
        } else {
            $response = ['status' => 'error', 'message' => 'Erreur lors de la mise à jour de l\'activité'];
        }
    } elseif (isset($_POST['delete_activity']) && $user instanceof Administrator) {
        $activity_id = $_POST['activity_id'];
        $result = $user->deleteActivity($activity_id);
        if ($result) {
            $response = ['status' => 'success', 'message' => 'Activité supprimée avec succès'];
        } else {
            $response = ['status' => 'error', 'message' => 'Erreur lors de la suppression de l\'activité'];
        }
    }

    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord administrateur</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>  
    <link href="assets/style/style_index.css" rel="stylesheet">
    
</head>
<body class="bg-gray-100">
    <div id="notification" class="fixed top-4 right-4 z-50 hidden animate__animated animate__fadeIn">
        <div class="bg-green-500 text-white px-4 py-2 rounded shadow-lg">
            <span id="notificationMessage"></span>
        </div>
    </div>
    <?php if(!$user): ?>
        <div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <div>
                    <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                        Connexion à votre compte
                    </h2>
                </div>
                <form class="mt-8 space-y-6" action="#" method="POST">
                    <input type="hidden" name="remember" value="true">
                    <div class="rounded-md shadow-sm -space-y-px">
                        <div>
                            <label for="email-address" class="sr-only">Adresse e-mail</label>
                            <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" placeholder="Adresse e-mail">
                        </div>
                        <div>
                            <label for="password" class="sr-only">Mot de passe</label>
                            <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-orange-500 focus:border-orange-500 focus:z-10 sm:text-sm" placeholder="Mot de passe">
                        </div>
                    </div>

                    <div>
                        <button type="submit" name="login" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-orange-gradient hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <svg class="h-5 w-5 text-orange-500 group-hover:text-orange-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            Se connecter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex-shrink-0 flex items-center">
                                <span class="text-2xl font-bold text-orange-gradient">Administrateur</span>
                            </div>
                        </div>
                        <div class="flex items-center">
                            <div class="ml-3 relative">
                                <div>
                                    <button type="button" class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" id="user-menu-button" aria-expanded="false" aria-haspopup="true">
                                        <span class="sr-only">Open user menu</span>
                                        <img class="h-8 w-8 rounded-full" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80" alt="">
                                    </button>
                                </div>
                            </div>
                            <a href="?logout=1" class="ml-4 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-gradient hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                Déconnexion
                            </a>
                        </div>
                    </div>
                </div>
            </nav>

            <div class="py-10">
                <header>
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <h1 class="text-3xl font-bold leading-tight text-gray-900">
                            Bienvenue, <?php echo $user->nom . ' ' . $user->prenom; ?> 
                        </h1>
                    </div>
                </header>
                <main>
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                        <div class="px-4 py-8 sm:px-0">
                            <div x-data="{ activeTab: 'dashboard' }">
                                <div class="border-b border-gray-200">
                                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                                        <button @click="activeTab = 'dashboard'" :class="{'border-orange-500 text-orange-600': activeTab === 'dashboard', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'dashboard'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                            Tableau de bord
                                        </button>
                                        <button @click="activeTab = 'activities'" :class="{'border-orange-500 text-orange-600': activeTab === 'activities', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'activities'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                            Activités
                                        </button>
                                        <button @click="activeTab = 'reservations'" :class="{'border-orange-500 text-orange-600': activeTab === 'reservations', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'reservations'}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                                            Réservations
                                        </button>
                                    </nav>
                                </div>

                                <div x-show="activeTab === 'dashboard'" class="mt-8 animate__animated animate__fadeIn">
                                    <h2 class="text-2xl font-semibold mb-6">Aperçu du tableau de bord</h2>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                        <?php
                                        $dashboardData = $user->getDashboardData(); // Update 3: Calling getDashboardData() on Administrator object
                                        $statLabels = [
                                            'reservations' => ['Réservations', 'calendar'],
                                            'members' => ['Membres', 'users'],
                                            'administrators' => ['Administrateurs', 'shield'],
                                            'activities' => ['Activités', 'activity'],
                                        ];
                                        foreach ($dashboardData as $key => $value):
                                        ?>
                                        <div class="bg-white overflow-hidden shadow rounded-lg hover-lift">
                                            <div class="p-5">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 bg-orange-gradient rounded-md p-3">
                                                        <i data-lucide="<?php echo $statLabels[$key][1]; ?>" class="h-6 w-6 text-white"></i>
                                                    </div>
                                                    <div class="ml-5 w-0 flex-1">
                                                        <dl>
                                                            <dt class="text-sm font-medium text-gray-500 truncate">
                                                                <?php echo $statLabels[$key][0]; ?>
                                                            </dt>
                                                            <dd>
                                                                <div class="text-lg font-medium text-gray-900">
                                                                    <?php echo $value; ?>
                                                                </div>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="mt-8 bg-white overflow-hidden shadow rounded-lg">
                                        <div class="p-6">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Statistiques des réservations</h3>
                                            <canvas id="reservationsChart" width="400" height="200"></canvas>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="activeTab === 'activities'" class="mt-8 animate__animated animate__fadeIn">
                                    <h2 class="text-2xl font-semibold mb-6">Gestion des activités</h2>
                                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                        <div class="px-4 py-5 sm:p-6">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Ajouter une nouvelle activité</h3>
                                            <form id="activityForm" class="space-y-6">
                                                <div>
                                                    <label for="nom_activite" class="block text-sm font-medium text-gray-700">Nom de l'activité</label>
                                                    <input type="text" name="nom_activite" id="nom_activite" class="mt-1 focus:ring-orange-500 focus:border-orange-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                </div>
                                                <div>
                                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                                    <textarea name="description" id="description" rows="3" class="mt-1 focus:ring-orange-500 focus:border-orange-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"></textarea>
                                                </div>
                                                <div>
                                                    <label for="disponibilite" class="block text-sm font-medium text-gray-700">Disponibilité</label>
                                                    <input type="number" name="disponibilite" id="disponibilite" min="0" class="mt-1 focus:ring-orange-500 focus:border-orange-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                                </div>
                                                <div>
                                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-orange-gradient hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                                                        Ajouter l'activité
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="mt-8 bg-white shadow overflow-hidden sm:rounded-md">
                                        <div class="px-4 py-5 sm:p-6">
                                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Liste des activités</h3>
                                            <div class="overflow-x-auto">
                                                <table id="activitiesTable" class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disponibilité</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Places restantes</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <?php
                                                        $activities = $user->getConfirmedActivities();
                                                        foreach ($activities as $activity):
                                                        ?>
                                                        <tr data-activity-id="<?php echo $activity['id']; ?>">
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 activity-name">
                                                                <?php echo $activity['nom_activite']; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 activity-description">
                                                                <?php echo $activity['description']; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 activity-disponibilite">
                                                                <?php echo $activity['disponibilite']; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 activity-reste-place">
                                                                <?php echo $activity['reste_place']; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                                <button onclick="showEditActivityForm(<?php echo htmlspecialchars(json_encode($activity)); ?>)" class="text-orange-600 hover:text-orange-900 mr-2">Modifier</button>
                                                                <button onclick="deleteActivity(<?php echo $activity['id']; ?>)" class="text-red-600 hover:text-red-900">Supprimer</button>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="activeTab === 'reservations'" class="mt-8 animate__animated animate__fadeIn">
                                    <h2 class="text-2xl font-semibold mb-6">Gestion des réservations</h2>
                                    <div class="bg-white shadow overflow-hidden sm:rounded-md">
                                        <div class="px-4 py-5 sm:p-6">
                                            <div class="overflow-x-auto">
                                                <table id="reservationsTable" class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Membre</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activité</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        <?php
                                                        $reservations = $user->getAllReservations();
                                                        foreach ($reservations as $reservation):
                                                        ?>
                                                        <tr data-reservation-id="<?php echo $reservation['id']; ?>">
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                <?php echo $reservation['id']; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                                <?php echo $reservation['nom'] . ' ' . $reservation['prenom']; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                <?php echo $reservation['nom_activite']; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                <?php echo $reservation['date_reservation']; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 reservation-status">
                                                                <?php echo $reservation['status']; ?>
                                                            </td>
                                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                                <button onclick="updateReservationStatus(<?php echo $reservation['id']; ?>, 'confirme')" class="text-green-600 hover:text-green-900 mr-2">Confirmer</button>
                                                                <button onclick="updateReservationStatus(<?php echo $reservation['id']; ?>, 'non confirme')" class="text-red-600 hover:text-red-900">Non confirmer</button>
                                                            </td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>

            <div id="editActivityModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Modifier l'activité
                            </h3>
                            <div class="mt-2">
                                <form id="editActivityForm">
                                    <input type="hidden" id="edit_activity_id" name="activity_id">
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_nom_activite">
                                            Nom de l'activité
                                        </label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500"
                                               id="edit_nom_activite"
                                               name="nom_activite"
                                               type="text"
                                               required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_description">
                                            Description
                                        </label>
                                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500"
                                                  id="edit_description"
                                                  name="description"
                                                  required></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_disponibilite">
                                            Disponibilité
                                        </label>
                                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-orange-500"
                                               id="edit_disponibilite"
                                               name="disponibilite"
                                               type="number"
                                               min="0"
                                               required>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" onclick="updateActivity()"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-orange-600 text-base font-medium text-white hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Enregistrer
                            </button>
                            <button type="button" onclick="closeEditActivityModal()"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Annuler
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('reservationsChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin'],
                            datasets: [{
                                label: 'Nombre de réservations',
                                data: [12, 19, 3, 5, 2, 3],
                                borderColor: 'rgb(255, 159, 67)',
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    lucide.createIcons();
                });

                function showNotification(message) {
                    const notification = document.getElementById('notification');
                    const notificationMessage = document.getElementById('notificationMessage');
                    notificationMessage.textContent = message;
                    notification.classList.remove('hidden');
                    setTimeout(() => {
                        notification.classList.add('hidden');
                    }, 3000);
                }

                document.getElementById('activityForm').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    formData.append('add_activity', '1');
                    
                    fetch('index.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            showNotification(data.message);
                            this.reset();
                            
                            // Add the new activity to the activities table
                            const activitiesTable = document.getElementById('activitiesTable');
                            if (activitiesTable) {
                                const newRow = activitiesTable.insertRow(-1);
                                newRow.dataset.activityId = data.data.id;
                                newRow.innerHTML = `
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 activity-name">${data.data.nom_activite}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 activity-description">${data.data.description}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 activity-disponibilite">${data.data.disponibilite}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 activity-reste-place">${data.data.reste_place}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button onclick='showEditActivityForm(${JSON.stringify(data.data)})'
                                                class="text-orange-600 hover:text-orange-900 mr-2">
                                            Modifier
                                        </button>
                                        <button onclick="deleteActivity(${data.data.id})"
                                                class="text-red-600 hover:text-red-900">
                                            Supprimer
                                        </button>
                                    </td>
                                `;
                            }
                        } else {
                            showNotification(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification("Une erreur est survenue lors de la communication avec le serveur.");
                    });
                });

                function updateReservationStatus(reservationId, status) {
                    const formData = new FormData();
                    formData.append('update_reservation', '1');
                    formData.append('reservation_id', reservationId);
                    formData.append('status', status);
                    
                    fetch('index.php', {
                        method: 'POST',
                        body:formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            showNotification(data.message);
                            
                            // Update the reservation status in the table
                            const reservationRow = document.querySelector(`tr[data-reservation-id="${reservationId}"]`);
                            if (reservationRow) {
                                reservationRow.querySelector('.reservation-status').textContent = status;
                            }
                        } else {
                            showNotification(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification("Une erreur est survenue lors de la communication avec le serveur.");
                    });
                }

                function showEditActivityForm(activity) {
                    document.getElementById('edit_activity_id').value = activity.id;
                    document.getElementById('edit_nom_activite').value = activity.nom_activite;
                    document.getElementById('edit_description').value = activity.description;
                    document.getElementById('edit_disponibilite').value = activity.disponibilite;
                    document.getElementById('editActivityModal').classList.remove('hidden');
                }

                function closeEditActivityModal() {
                    document.getElementById('editActivityModal').classList.add('hidden');
                }

                function updateActivity() {
                    const formData = new FormData(document.getElementById('editActivityForm'));
                    formData.append('update_activity', '1');
                    
                    fetch('index.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === 'success') {
                            showNotification(data.message);
                            closeEditActivityModal();
                            
                            // Update the activity in the table
                            const activityRow = document.querySelector(`tr[data-activity-id="${formData.get('activity_id')}"]`);
                            if (activityRow) {
                                activityRow.querySelector('.activity-name').textContent = formData.get('nom_activite');
                                activityRow.querySelector('.activity-description').textContent = formData.get('description');
                                activityRow.querySelector('.activity-disponibilite').textContent = formData.get('disponibilite');
                                activityRow.querySelector('.activity-reste-place').textContent = formData.get('disponibilite');
                            }
                        } else {
                            showNotification(data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification("Une erreur est survenue lors de la communication avec le serveur.");
                    });
                }

                function deleteActivity(activityId) {
                    if (confirm('Êtes-vous sûr de vouloir supprimer cette activité ?')) {
                        const formData = new FormData();
                        formData.append('delete_activity', '1');
                        formData.append('activity_id', activityId);
                        
                        fetch('index.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.status === 'success') {
                                showNotification(data.message);
                                
                                // Remove the activity from the table
                                const activityRow = document.querySelector(`tr[data-activity-id="${activityId}"]`);
                                if (activityRow) {
                                    activityRow.remove();
                                }
                            } else {
                                showNotification(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            showNotification("Une erreur est survenue lors de la suppression de l'activité.");
                        });
                    }
                }
            </script>
        <?php endif; ?>
    </body>
</html>

