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
    <title>User Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-3xl font-bold">Welcome, <?php echo $user->nom . ' ' . $user->prenom; ?></h1>
                <a href="?logout=1" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</a>
            </div>
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
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <?php
                    $dashboard_data = $user->getDashboardData();
                    foreach($dashboard_data as $key => $value) {
                        echo '<div class="bg-white shadow-md rounded px-8 pt-6 pb-8">';
                        echo '<h3 class="text-xl font-bold mb-2 capitalize">' . $key . '</h3>';
                        echo '<p class="text-3xl font-bold">' . $value . '</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
                <h2 class="text-2xl font-bold mb-4">Add New Activity</h2>
                <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="nom_activite">
                            Activity Name
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nom_activite" type="text" name="nom_activite" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                            Description
                        </label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" required></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="add_activity">
                            Add Activity
                        </button>
                    </div>
                </form>
                <h2 class="text-2xl font-bold my-4">Pending Reservations</h2>
                <table class="w-full bg-white shadow-md rounded mb-4">
                    <thead>
                        <tr>
                            <th class="px-4 py-2">Member</th>
                            <th class="px-4 py-2">Activity</th>
                            <th class="px-4 py-2">Date</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $reservations = $user->getReservations();
                        while ($row = $reservations->fetch(PDO::FETCH_ASSOC)){
                            echo '<tr>';
                            echo '<td class="border px-4 py-2">' . $row['nom'] . ' ' . $row['prenom'] . '</td>';
                            echo '<td class="border px-4 py-2">' . $row['nom_activite'] . '</td>';
                            echo '<td class="border px-4 py-2">' . $row['date_reservation'] . '</td>';
                            echo '<td class="border px-4 py-2">';
                            echo '<form method="POST" class="inline-block mr-2">';
                            echo '<input type="hidden" name="reservation_id" value="' . $row['id'] . '">';
                            echo '<button type="submit" name="accept" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded">Accept</button>';
                            echo '</form>';
                            echo '<form method="POST" class="inline-block">';
                            echo '<input type="hidden" name="reservation_id" value="' . $row['id'] . '">';
                            echo '<button type="submit" name="reject" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">Reject</button>';
                            echo '</form>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
    if(isset($_POST['reserve'])) {
        $activity_id = $_POST['activity_id'];
        if($user->makeReservation($activity_id)) {
            echo "<script>alert('Reservation made successfully!');</script>";
        } else {
            echo "<script>alert('Failed to make reservation.');</script>";
        }
    }

    if(isset($_POST['cancel'])) {
        $reservation_id = $_POST['reservation_id'];
        if($user->cancelReservation($reservation_id)) {
            echo "<script>alert('Reservation cancelled successfully!');</script>";
        } else {
            echo "<script>alert('Failed to cancel reservation.');</script>";
        }
    }

    if(isset($_POST['add_activity'])) {
        $nom_activite = $_POST['nom_activite'];
        $description = $_POST['description'];
        if($user->addActivity($nom_activite, $description)) {
            echo "<script>alert('Activity added successfully!');</script>";
        } else {
            echo "<script>alert('Failed to add activity.');</script>";
        }
    }

    if(isset($_POST['accept'])) {
        $reservation_id = $_POST['reservation_id'];
        if($user->updateReservationStatus($reservation_id, 'confirme')) {
            echo "<script>alert('Reservation accepted successfully!');</script>";
        } else {
            echo "<script>alert('Failed to accept reservation.');</script>";
        }
    }

    if(isset($_POST['reject'])) {
        $reservation_id = $_POST['reservation_id'];
        if($user->updateReservationStatus($reservation_id, 'non confirme')) {
            echo "<script>alert('Reservation rejected successfully!');</script>";
        } else {
            echo "<script>alert('Failed to reject reservation.');</script>";
        }
    }
    ?>
</body>
</html>

