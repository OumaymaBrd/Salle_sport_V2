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
        exit();
    } else {
        $error = "Invalid credentials";
    }
}

if(isset($_GET['logout'])) {
    session_destroy();
    setcookie("user_logged", "", time() - 3600, "/");
    header("Location: index.php");
    exit();
}

if(isset($_POST['add_activity'])) {
    header('Content-Type: application/json');
    $response = array();
    
    if($user instanceof Administrator) {
        $nom_activite = $_POST['nom_activite'];
        $description = $_POST['description'];
        $matricule_administration = $user->matricule;

        $result = $user->addActivity($nom_activite, $description, $matricule_administration);
        if($result) {
            $response = array(
                'status' => 'success',
                'message' => 'Activité ajoutée avec succès!',
                'id' => $result
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Erreur lors de l\'ajout de l\'activité.'
            );
        }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Vous n\'avez pas les droits nécessaires.'
        );
    }
    
    echo json_encode($response);
    exit();
}

if(isset($_POST['update_reservation'])) {
    header('Content-Type: application/json');
    $response = array();
    
    if($user instanceof Administrator) {
        $reservation_id = $_POST['reservation_id'];
        $status = $_POST['status'];

        $result = $user->updateReservationStatus($reservation_id, $status);
        if($result) {
            $response = array(
                'status' => 'success',
                'message' => 'Statut de la réservation mis à jour avec succès!'
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour du statut de la réservation.'
            );
        }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Vous n\'avez pas les droits nécessaires.'
        );
    }
    
    echo json_encode($response);
    exit();
}

if(isset($_POST['update_activity'])) {
    header('Content-Type: application/json');
    $response = array();
    
    if($user instanceof Administrator) {
        $activity_id = $_POST['activity_id'];
        $nom_activite = $_POST['nom_activite'];
        $description = $_POST['description'];

        $result = $user->updateActivity($activity_id, $nom_activite, $description);
        if($result) {
            $response = array(
                'status' => 'success',
                'message' => 'Activité mise à jour avec succès!'
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour de l\'activité.'
            );
        }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Vous n\'avez pas les droits nécessaires.'
        );
    }
    
    echo json_encode($response);
    exit();
}

if(isset($_POST['delete_activity'])) {
    header('Content-Type: application/json');
    $response = array();
    
    if($user instanceof Administrator) {
        $activity_id = $_POST['activity_id'];

        $result = $user->deleteActivity($activity_id);
        if($result) {
            $response = array(
                'status' => 'success',
                'message' => 'Activité supprimée avec succès!'
            );
        } else {
            $response = array(
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de l\'activité.'
            );
        }
    } else {
        $response = array(
            'status' => 'error',
            'message' => 'Vous n\'avez pas les droits nécessaires.'
        );
    }
    
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <?php if(!$user): ?>
            <h1 class="text-3xl font-bold mb-4">Login</h1>
            <?php if(isset($error)): ?>
                <p class="text-red-500"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" type="email" name="email" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" type="password" name="password" required>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="login">
                        Sign In
                    </button>
                </div>
            </form>
        <?php else: ?>
            
            <?php if($user->post == 'membre'): ?>
                <h2 class="text-2xl font-bold mb-4">Activities</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php
                    $activities = $user->getActivities();
                    while ($row = $activities->fetch(PDO::FETCH_ASSOC)){
                        echo '<div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">';
                        echo '<h3 class="text-xl font-bold mb-2">' . $row['nom_activite'] . '</h3>';
                        echo '<p class="mb-4">' . $row['description'] . '</p>';
                        echo '<form method="POST">';
                        echo '<input type="hidden" name="activity_id" value="' . $row['id'] . '">';
                        echo '<button type="submit" name="reserve" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Reserve</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <h2 class="text-2xl font-bold my-4">My Reservations</h2>
                <table class="w-full bg-white shadow-md rounded mb-4">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Activity</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $reservations = $user->getReservations();
                        while ($row = $reservations->fetch(PDO::FETCH_ASSOC)){
                            echo '<tr>';
                            echo '<td class="border px-4 py-2">' . $row['nom_activite'] . '</td>';
                            echo '<td class="border px-4 py-2">' . $row['date_reservation'] . '</td>';
                            echo '<td class="border px-4 py-2">' . $row['status'] . '</td>';
                            echo '<td class="border px-4 py-2">';
                            echo '<form method="POST">';
                            echo '<input type="hidden" name="reservation_id" value="' . $row['id'] . '">';
                            echo '<button type="submit" name="cancel" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Cancel</button>';
                            echo '</form>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            <?php else: ?>
               
                <h1 class="text-3xl font-bold mb-6">Tableau de bord administrateur</h1>
            
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
                    <?php
                    $dashboardData = $user->getDashboardData();
                    $statLabels = [
                        'reservations' => 'Réservations',
                        'members' => 'Membres',
                        'administrators' => 'Administrateurs',
                        'activities' => 'Activités',
                        'visits' => 'Visites'
                    ];
                    foreach ($dashboardData as $key => $value):
                    ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-xl font-semibold mb-2"><?php echo $statLabels[$key]; ?></h2>
                            <p class="text-3xl font-bold"><?php echo $value; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div x-data="{ showActivityForm: false, showReservations: false, showActivities: false }">
                    <div class="flex space-x-4 mb-4">
                        <button @click="showActivityForm = !showActivityForm" 
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Ajouter une activité
                        </button>
                        <button @click="showReservations = !showReservations" 
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                            Afficher les réservations
                        </button>
                        <button @click="showActivities = !showActivities" 
                                class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            Afficher les activités
                        </button>
                    </div>

                    <div x-show="showActivityForm" class="mb-8">
                        <form id="activityForm" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="nom_activite">
                                    Nom de l'activité
                                </label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                       id="nom_activite"
                                       name="nom_activite"
                                       type="text"
                                       required>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                                    Description
                                </label>
                                <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                          id="description"
                                          name="description"
                                          required></textarea>
                            </div>

                            <div class="flex items-center justify-between">
                                <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                                    Ajouter
                                </button>
                            </div>
                        </form>

                        <div id="activityMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"></span>
                        </div>
                    </div>

                    <div x-show="showReservations" class="mb-8">
                        <h2 class="text-2xl font-bold mb-4">Liste des réservations</h2>
                        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2">ID</th>
                                        <th class="px-4 py-2">Membre</th>
                                        <th class="px-4 py-2">Activité</th>
                                        <th class="px-4 py-2">Date</th>
                                        <th class="px-4 py-2">Statut</th>
                                        <th class="px-4 py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $reservations = $user->getAllReservations();
                                    foreach ($reservations as $reservation):
                                    ?>
                                    <tr>
                                        <td class="border px-4 py-2"><?php echo $reservation['id']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['nom'] . ' ' . $reservation['prenom']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['nom_activite']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['date_reservation']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $reservation['status']; ?></td>
                                        <td class="border px-4 py-2">
                                            <button onclick="updateReservationStatus(<?php echo $reservation['id']; ?>, 'confirme')"
                                                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded mr-2">
                                                Confirmer
                                            </button>
                                            <button onclick="updateReservationStatus(<?php echo $reservation['id']; ?>, 'non confirme')"
                                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                                Non confirmer
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="reservationMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"></span>
                        </div>
                    </div>

                    <div x-show="showActivities" class="mb-8">
                        <h2 class="text-2xl font-bold mb-4">Liste des activités</h2>
                        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2">ID</th>
                                        <th class="px-4 py-2">Nom de l'activité</th>
                                        <th class="px-4 py-2">Description</th>
                                        <th class="px-4 py-2">Administrateur</th>
                                        <th class="px-4 py-2">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $activities = $user->getConfirmedActivities();
                                    foreach ($activities as $activity):
                                    ?>
                                    <tr>
                                        <td class="border px-4 py-2"><?php echo $activity['id']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $activity['nom_activite']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $activity['description']; ?></td>
                                        <td class="border px-4 py-2"><?php echo $activity['nom'] . ' ' . $activity['prenom']; ?></td>
                                        <td class="border px-4 py-2">
                                            <button onclick="showEditActivityForm(<?php echo htmlspecialchars(json_encode($activity)); ?>)"
                                                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded mr-2">
                                                Modifier
                                            </button>
                                            <button onclick="deleteActivity(<?php echo $activity['id']; ?>)"
                                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                                Supprimer
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="activityMessage" class="hidden bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline"></span>
                        </div>
                    </div>
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
                                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                   id="edit_nom_activite"
                                                   name="nom_activite"
                                                   type="text"
                                                   required>
                                        </div>
                                        <div class="mb-4">
                                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_description">
                                                Description
                                            </label>
                                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                      id="edit_description"
                                                      name="description"
                                                      required></textarea>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" onclick="updateActivity()"
                                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
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
                            const activityMessage = document.getElementById('activityMessage');
                            
                            if(data.status === 'success') {
                                activityMessage.textContent = data.message;
                                activityMessage.classList.remove('hidden');
                                this.reset();
                                
                                setTimeout(() => {
                                    activityMessage.classList.add('hidden');
                                    location.reload();
                                }, 3000);
                            } else {
                                activityMessage.textContent = data.message;
                                activityMessage.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            const activityMessage = document.getElementById('activityMessage');
                            activityMessage.textContent = "Une erreur est survenue lors de la communication avec le serveur.";
                            activityMessage.classList.remove('hidden');
                        });
                    });

                    function updateReservationStatus(reservationId, status) {
                        const formData = new FormData();
                        formData.append('update_reservation', '1');
                        formData.append('reservation_id', reservationId);
                        formData.append('status', status);
                        
                        fetch('index.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            const reservationMessage = document.getElementById('reservationMessage');
                            
                            if(data.status === 'success') {
                                reservationMessage.textContent = data.message;
                                reservationMessage.classList.remove('hidden');
                                
                                setTimeout(() => {
                                    reservationMessage.classList.add('hidden');
                                    location.reload();
                                }, 3000);
                            } else {
                                reservationMessage.textContent = data.message;
                                reservationMessage.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            const reservationMessage = document.getElementById('reservationMessage');
                            reservationMessage.textContent = "Une erreur est survenue lors de la communication avec le serveur.";
                            reservationMessage.classList.remove('hidden');
                        });
                    }

                    function showEditActivityForm(activity) {
                        document.getElementById('edit_activity_id').value = activity.id;
                        document.getElementById('edit_nom_activite').value = activity.nom_activite;
                        document.getElementById('edit_description').value = activity.description;
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
                            const activityMessage = document.getElementById('activityMessage');
                            
                            if(data.status === 'success') {
                                activityMessage.textContent = data.message;
                                activityMessage.classList.remove('hidden');
                                closeEditActivityModal();
                                
                                setTimeout(() => {
                                    activityMessage.classList.add('hidden');
                                    location.reload();
                                }, 3000);
                            } else {
                                activityMessage.textContent = data.message;
                                activityMessage.classList.remove('hidden');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            const activityMessage = document.getElementById('activityMessage');
                            activityMessage.textContent = "Une erreur est survenue lors de la communication avec le serveur.";
                            activityMessage.classList.remove('hidden');
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
                                const activityMessage = document.getElementById('activityMessage');
                                
                                if(data.status === 'success') {
                                    activityMessage.textContent = data.message;
                                    activityMessage.classList.remove('hidden');
                                    
                                    setTimeout(() => {
                                        activityMessage.classList.add('hidden');
                                        location.reload();
                                    }, 3000);
                                } else {
                                    activityMessage.textContent = data.message;
                                    activityMessage.classList.remove('hidden');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                const activityMessage = document.getElementById('activityMessage');
                                activityMessage.textContent = "Une erreur est survenue lors de la suppression de l'activité.";
                                activityMessage.classList.remove('hidden');
                            });
                        }
                    }
                </script>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>