<div x-data="{ showActivities: true, showReservations: false }">
    <div class="flex space-x-4 mb-4">
        <button @click="showActivities = true; showReservations = false" 
                :class="{ 'bg-blue-500': showActivities, 'bg-gray-300': !showActivities }"
                class="hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Activités
        </button>
        <button @click="showReservations = true; showActivities = false" 
                :class="{ 'bg-blue-500': showReservations, 'bg-gray-300': !showReservations }"
                class="hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Mes Réservations
        </button>
    </div>

    <div x-show="showActivities">
        <h2 class="text-2xl font-bold mb-4">Activités disponibles</h2>
        <div id="activitiesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php
            $activities = $user->getActivities();
            while ($row = $activities->fetch(PDO::FETCH_ASSOC)):
            ?>
                <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($row['nom_activite']); ?></h3>
                    <p class="mb-4"><?php echo htmlspecialchars($row['description']); ?></p>
                    <form class="reserveForm">
                        <input type="hidden" name="activity_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full">
                            Réserver
                        </button>
                    </form>
                    <div class="reservation-message mt-2"></div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <div x-show="showReservations">
        <h2 class="text-2xl font-bold my-4">Mes Réservations</h2>
        <div class="overflow-x-auto">
            <table id="reservationsTable" class="w-full bg-white shadow-md rounded mb-4">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Activité</th>
                        <th class="px-4 py-2">Date</th>
                        <th class="px-4 py-2">Statut</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $reservations = $user->getReservations();
                    while ($row = $reservations->fetch(PDO::FETCH_ASSOC)):
                    ?>
                        <tr data-reservation-id="<?php echo $row['id']; ?>">
                            <td class="border px-4 py-2"><?php echo htmlspecialchars($row['nom_activite']); ?></td>
                            <td class="border px-4 py-2"><?php echo $row['date_reservation']; ?></td>
                            <td class="border px-4 py-2"><?php echo $row['status']; ?></td>
                            <td class="border px-4 py-2">
                                <form class="updateReservationForm inline-block mr-2">
                                    <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                                    <input type="datetime-local" name="new_date" required 
                                           class="border rounded px-2 py-1 mr-2">
                                    <button type="submit" 
                                            class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded">
                                        Modifier
                                    </button>
                                </form>
                                <form class="deleteReservationForm inline-block">
                                    <input type="hidden" name="reservation_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" 
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">
                                        Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

