<div x-data="{ showDashboard: true, showActivityForm: false, showReservations: false, showActivities: false }">
    <div class="flex space-x-4 mb-4">
        <button @click="showDashboard = true; showActivityForm = false; showReservations = false; showActivities = false" 
                :class="{ 'bg-blue-500': showDashboard, 'bg-gray-300': !showDashboard }"
                class="hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Tableau de bord
        </button>
        <button @click="showActivityForm = true; showDashboard = false; showReservations = false; showActivities = false" 
                :class="{ 'bg-blue-500': showActivityForm, 'bg-gray-300': !showActivityForm }"
                class="hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Ajouter une activité
        </button>
        <button @click="showReservations = true; showDashboard = false; showActivityForm = false; showActivities = false" 
                :class="{ 'bg-blue-500': showReservations, 'bg-gray-300': !showReservations }"
                class="hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Afficher les réservations
        </button>
        <button @click="showActivities = true; showDashboard = false; showActivityForm = false; showReservations = false" 
                :class="{ 'bg-blue-500': showActivities, 'bg-gray-300': !showActivities }"
                class="hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Afficher les activités
        </button>
    </div>

    <div x-show="showDashboard">
        <h2 class="text-2xl font-bold mb-4">Tableau de bord administrateur</h2>
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
    </div>

    <div x-show="showActivityForm">
        <h2 class="text-2xl font-bold mb-4">Ajouter une activité</h2>
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

    <div x-show="showReservations">
        <h2 class="text-2xl font-bold mb-4">Liste des réservations</h2>
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 overflow-x-auto">
            <table id="reservationsTable" class="min-w-full">
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

    <div x-show="showActivities">
        <h2 class="text-2xl font-bold mb-4">Liste des activités</h2>
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 overflow-x-auto">
            <table id="activitiesTable" class="min-w-full">
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

