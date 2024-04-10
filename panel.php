<?php
session_start();

// Vérifie si l'utilisateur est connecté
if (!$_SESSION['logged_in']) {
    // Si l'utilisateur n'est pas connecté, redirige vers la page de connexion
    header("Location: index.php");
    exit();
}

// Traitement de la déconnexion
if (isset($_POST['logout'])) {
    // Indiquer que l'utilisateur est déconnecté
    $_SESSION['logged_in'] = false;
    // Redirige vers la page de connexion
    header("Location: index.php");
    exit();
}

// Récupérer les données de connexion
$port = isset($_SESSION['port']) ? $_SESSION['port'] : '';
$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$password = isset($_SESSION['password']) ? $_SESSION['password'] : '';

// Fonction pour afficher les informations des joueurs bannis
function displayBannedPlayerDetails($userid) {
    // Lire le fichier JSON des joueurs bannis
    $banned_players_json = file_get_contents('ban_users.json');
    $banned_players_data = json_decode($banned_players_json, true);

    // Rechercher les informations du joueur banni par son ID
    foreach ($banned_players_data as $banned_player) {
        if ($banned_player['userid'] === $userid) {
            echo '<h3>Informations sur le joueur banni :</h3>';
            echo '<pre>';
            echo 'ID joueur : ' . $banned_player['userid'] . '\n';
            echo 'Nom : ' . $banned_player['player_name'] . '\n';
            echo 'Action : ' . $banned_player['action'] . '\n';
            echo 'Heure de banissement : ' . date("H:i:s d/m/Y", $banned_player['timestamp']) . '\n'; // Convertir le timestamp en format lisible
            // Vous pouvez ajouter d'autres détails du joueur banni si nécessaire
            echo '</pre>';
            break; // Sortir de la boucle une fois que le joueur est trouvé
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel</title>
    <link rel="stylesheet" href="/styles/styles.css">
</head>
<body>

<div class="container">
    <div class="Left-containers">
        <div class="request-box">
            <h2>Informations</h2>
            <form method="post">
            </form>

            <?php
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/info',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
					CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . base64_encode($username . ':' . $password),
            'Accept: application/json'
        ),
    ));
                $response = curl_exec($curl);

                if ($response === false) {
                    echo 'Curl Error: ' . curl_error($curl);
                } else {
                    $json_response = json_decode($response, true);


                    echo '<pre>';
                    echo 'Version : ' . $json_response['version'] . '<br>';
                    echo 'Nom du serveur : ' . $json_response['servername'] . '<br>';
                    echo 'Description : ' . $json_response['description'] . '<br>';
                    echo '</pre>';
                }

                curl_close($curl);
            ?>
        </div>

        <div class="request-box">
            <h2>Métriques</h2>
            <form method="post">
            </form>

            <?php
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/metrics',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic ' . base64_encode($username . ':' . $password),
                        'Accept: application/json'
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);

                $metrics = json_decode($response, true);

                function secondsToTime($seconds) {
                    $hours = floor($seconds / 3600);
                    $minutes = floor(($seconds % 3600) / 60);
                    $seconds = $seconds % 60;

                    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                }
                if ($metrics) {
                    echo '<pre>';
                    echo 'Tick Rate : ' . $metrics['serverfps'] . PHP_EOL;
                    echo 'Nombre actuel de joueurs : ' . $metrics['currentplayernum'] . PHP_EOL;
                    echo 'Temps de frames du serveur : ' . $metrics['serverframetime'] . ' ms' . PHP_EOL;
                    echo "Temps d'activité : " . secondsToTime($metrics['uptime']) . PHP_EOL;
                    echo 'Limite de joueurs : ' . $metrics['maxplayernum'] . PHP_EOL;
                    echo '</pre>';
                } else {
                    echo '<p>Aucune donnée trouvé.</p>';
                }
            ?>
        </div>

        <div class="request-box">
            <h2>Configuration serveur</h2>
            <form method="post">
            </form>

            <?php
                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/settings',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic ' . base64_encode($username . ':' . $password),
                        'Accept: application/json'
                    ),
                ));

                $response = curl_exec($curl);

                curl_close($curl);

                if ($response) {
                    echo '<pre>';

                    $settings = json_decode($response, true);

                    foreach ($settings as $key => $value) {
                        if ($key === 'PublicIP') {
                            echo 'Public IP Address: ' . $value . PHP_EOL;
                        } elseif ($key === 'ServerName') {
                            echo 'Server Name: ' . $value . PHP_EOL;
                        } elseif ($key === 'ServerDescription') {
                            echo 'Server Description: ' . ($value ? $value : 'None') . PHP_EOL;
                        } elseif ($key === 'PublicPort') {
                            echo 'Public Port: ' . $value . PHP_EOL;
                        } elseif ($key === 'RCONEnabled') {
                            echo 'RCON Enabled: ' . ($value ? 'Yes' : 'No') . PHP_EOL;
                        } elseif ($key === 'RCONPort') {
                            echo 'RCON Port: ' . $value . PHP_EOL;
                        } elseif ($key === 'Region') {
                            echo 'Region: ' . $value . PHP_EOL;
                        } elseif ($key === 'BanListURL') {
                            echo 'Ban List URL: ' . $value . PHP_EOL;
                        } elseif ($key === 'RESTAPIEnabled') {
                            echo 'REST API Enabled: ' . ($value ? 'Yes' : 'No') . PHP_EOL;
                        } elseif ($key === 'RESTAPIPort') {
                            echo 'REST API Port: ' . $value . PHP_EOL;
                        } elseif ($key === 'AllowConnectPlatform') {
                            echo 'Allowed Connection Platform: ' . $value . PHP_EOL;
                        } elseif ($key === 'LogFormatType') {
                            echo 'Log Format Type: ' . $value . PHP_EOL;
                        } else {
                            echo $key . ': ' . $value . PHP_EOL;
                        }
                    }

                    echo '</pre>';
                } else {
                    echo '<p>No settings available.</p>';
                }
            ?>
        </div>
    </div>
















<div class="middle-containers">
    <div class="request-box">
        <h2>Joueurs en ligne</h2>
        <form method="post">
        </form>

        <?php
        // Utilisation de cURL pour récupérer les joueurs en ligne depuis l'API
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/players',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . base64_encode($username . ':' . $password),
                'Accept: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $json_response = json_decode($response, true);

        // Vérification et affichage des joueurs en ligne avec leurs informations
        if (isset($json_response['players']) && !empty($json_response['players'])) {
            foreach ($json_response['players'] as $player) {
                echo '<pre class="pre-player-info">';
                echo 'ID joueur : ' . $player['playerId'] . '<br>';
                echo 'Nom : ' . $player['name'] . '<br>';
                echo 'ID Utilisateur: ' . $player['userId'] . '<br>';
                echo 'Adresse IP : ' . $player['ip'] . '<br>';
                echo 'Ping : ' . $player['ping'] . '<br>';
                echo 'Position X : ' . $player['location_x'] . '<br>';
                echo 'Position Y : ' . $player['location_y'] . '<br>';
                echo 'Niveau : ' . $player['level'] . '<br>';
                echo '</pre>';

                // Formulaire de kick pour chaque joueur
                echo '<form method="post" style="display:inline-block;">';
                echo '<input type="hidden" name="userid" value="' . $player['userId'] . '">';
                echo '<button type="submit" name="kick_player">Kick</button>';
                echo '</form>';
                echo '<div style="display:inline-block; width: 10px;"></div>'; 

                // Formulaire de ban pour chaque joueur
                echo '<form method="post" style="display:inline-block;">';
                echo '<input type="hidden" name="userid" value="' . $player['userId'] . '">';
                echo '<button type="submit" name="ban_player">Ban</button>';
                echo '</form>';
            }
        } else {
            echo '<pre>Aucun joueur connecté.</pre>';
        }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Gestion de la soumission du formulaire de kick
if (isset($_POST['kick_player'])) {
    // Récupération de l'ID utilisateur du joueur à kicker
    $userid = isset($_POST['userid']) ? $_POST['userid'] : '';

    // Vérification si l'ID utilisateur est valide
    if (!empty($userid)) {
        // Utilisation de cURL pour envoyer une requête de kick au serveur
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/kick',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                  "userid": "' . $userid . '",
                  "message": "You are kicked."
                }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . base64_encode($username . ':' . $password),
                'Accept: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // Vérifier si la requête de kick a réussi
        if ($response !== false) {
            // Récupérer le nom du joueur à partir des informations déjà récupérées
            $player_name = ''; // initialiser la variable

            foreach ($json_response['players'] as $player) {
                if ($player['userId'] === $userid) {
                    $player_name = $player['name'];
                    break; // sortir de la boucle une fois que le joueur est trouvé
                }
            }

            if (!empty($player_name)) {
                // Utilisation de cURL pour envoyer un message d'annonce indiquant que le joueur a été kické
                $announce_curl = curl_init();

                curl_setopt_array($announce_curl, array(
                    CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/announce',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'{
                        "message": "Le joueur ' . $player_name . ' a été kické."
                    }',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic ' . base64_encode($username . ':' . $password),
                        'Accept: application/json'
                    ),
                ));

                $announce_response = curl_exec($announce_curl);

                curl_close($announce_curl);

                // Vérifier si la requête d'annonce a réussi
                if ($announce_response !== false) {
                    echo "Le joueur a été kické et l'annonce a été envoyée avec succès.";
                } else {
                    echo "Erreur lors de l'envoi de l'annonce.";
                }
            } else {
                echo "Aucun nom de joueur trouvé pour l'ID : " . $userid;
            }
        } else {
            echo "Erreur lors de l'expulsion du joueur.";
        }
    }
}

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////





//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Gestion de la soumission du formulaire de ban
if (isset($_POST['ban_player'])) {
    // Récupération de l'ID utilisateur du joueur à bannir
    $userid = isset($_POST['userid']) ? $_POST['userid'] : '';

    // Vérification si l'ID utilisateur est valide
    if (!empty($userid)) {
        // Utilisation de cURL pour envoyer une requête de ban au serveur
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/ban',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                  "userid": "' . $userid . '",
                  "message": "You are banned."
                }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic ' . base64_encode($username . ':' . $password),
                'Accept: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        // Vérifier si la requête de ban a réussi
        if ($response !== false) {
            // Récupérer le nom et le niveau du joueur à partir des informations déjà récupérées
            $player_name = ''; // Initialiser la variable
            $player_level = ''; // Initialiser la variable

            foreach ($json_response['players'] as $player) {
                if ($player['userId'] === $userid) {
                    $player_name = $player['name'];
                    $player_level = $player['level'];
                    break; // Sortir de la boucle une fois que le joueur est trouvé
                }
            }

            if (!empty($player_name)) {
                // Utilisation de cURL pour envoyer un message d'annonce indiquant que le joueur a été banni
                $announce_curl = curl_init();

                curl_setopt_array($announce_curl, array(
                    CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/announce',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS =>'{
                        "message": "Le joueur ' . $player_name . ' a été banni."
                    }',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic ' . base64_encode($username . ':' . $password),
                        'Accept: application/json'
                    ),
                ));

                $announce_response = curl_exec($announce_curl);

                curl_close($announce_curl);

                // Vérifier si la requête d'annonce a réussi
                if ($announce_response !== false) {
                    echo "Le joueur a été banni et l'annonce a été envoyée avec succès.";

                    // Enregistrement des informations du joueur banni dans un fichier JSON
$ban_data = array(
    'userid' => $userid,
    'player_name' => $player_name,
    'action' => 'ban',
    'timestamp' => time() // Utilisation de time() pour obtenir le timestamp actuel
);

// Lors de l'affichage des informations des joueurs bannis dans la section des bannis
// Récupérez la date de banissement à partir du fichier JSON
// Convertissez le timestamp en un format lisible
// Affichez la date formatée
echo "Heure de banissement : " . date("H:i:s d/m/Y", $ban_timestamp); // Remplacez $ban_timestamp par le timestamp récupéré depuis le JSON

                    // Chemin vers le fichier JSON
                    $file_path = 'ban_users.json';

                    // Lecture du contenu actuel du fichier JSON s'il existe
                    $current_data = file_exists($file_path) ? json_decode(file_get_contents($file_path), true) : array();

                    // Ajout des informations du joueur banni au tableau de données
                    $current_data[] = $ban_data;

                    // Encodage des données au format JSON
                    $json_data = json_encode($current_data, JSON_PRETTY_PRINT);

                    // Écriture des données JSON dans le fichier
                    if (file_put_contents($file_path, $json_data)) {
                        echo " Les informations du joueur ont été enregistrées dans le fichier JSON avec succès.";
                    } else {
                        echo " Erreur lors de l'enregistrement des informations du joueur dans le fichier JSON.";
                    }
                } else {
                    echo "Erreur lors de l'envoi de l'annonce.";
                }
            } else {
                echo "Aucun nom de joueur trouvé pour l'ID : " . $userid;
            }
        } else {
            echo "Erreur lors du bannissement du joueur.";
        }
    }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>


</div>
</div>
<div class="right-containers">
    <div class="request-box">
        <h2>Envoyer une annonce</h2>
        <form method="post">
            <label for="announcement_message">Message:</label>
            <input type="text" id="announcement_message" name="announcement_message" required>
            <button type="submit" name="send_announcement_request">Envoyer</button>
        </form>

        <?php
        if (isset($_POST['send_announcement_request'])) {
            $message = isset($_POST['announcement_message']) ? $_POST['announcement_message'] : '';

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/announce',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode(array('message' => $message)),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Basic ' . base64_encode($username . ':' . $password),
                    'Accept: application/json'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            
            // Affichage de la réponse à droite du bouton
            echo '<div class="response-container">' . $response . '</div>';
        }
        ?>
    </div>
    
<!-- Section de gestion des bannis -->
<div class="request-box ban-management"> <!-- Ajout de la classe ban-management -->
    <h2>Gestion des bannis</h2>
    <form method="post">
        <label for="banned_players">Joueurs bannis :</label>
        <select name="banned_player" id="banned_players">
            <option value="" disabled selected>Sélectionner</option> <!-- Option "Sélectionner" par défaut -->
            <?php
            // Lire le fichier JSON des joueurs bannis
            $banned_players_json = file_get_contents('ban_users.json');
            $banned_players_data = json_decode($banned_players_json, true);

            // Afficher les options de la liste déroulante avec les noms des joueurs bannis
            foreach ($banned_players_data as $banned_player) {
                echo '<option value="' . $banned_player['userid'] . '">' . $banned_player['player_name'] . '</option>';
            }
            ?>
        </select>
    </form>
    
    <div class="banned-player-details" id="banned_player_details"> <!-- Ajout de l'id "banned_player_details" -->
    </div>
</div>

<!-- Script JavaScript pour afficher automatiquement les informations du joueur sélectionné -->
<script>
    document.getElementById('banned_players').addEventListener('change', function() {
        var selectedPlayerId = this.value;
        var bannedPlayersData = <?php echo json_encode($banned_players_data); ?>; // Données des joueurs bannis en JavaScript
        
        var bannedPlayerDetailsDiv = document.getElementById('banned_player_details');
        bannedPlayerDetailsDiv.innerHTML = ''; // Effacer le contenu précédent
        
        bannedPlayersData.forEach(function(bannedPlayer) {
            if (bannedPlayer.userid === selectedPlayerId) {
                var playerDetailsHtml = '<h3>Informations sur le joueur banni :</h3>';
                playerDetailsHtml += '<pre>';
                playerDetailsHtml += 'ID joueur : ' + bannedPlayer.userid + '\n';
                playerDetailsHtml += 'Nom : ' + bannedPlayer.player_name + '\n';
                playerDetailsHtml += 'Action : ' + bannedPlayer.action + '\n';
                playerDetailsHtml += 'Heure de banissement : ' + new Date(bannedPlayer.timestamp * 1000).toLocaleString('fr-FR', { timeZone: 'UTC' }) + '\n'; // Convertir le timestamp en format lisible
                // Ajouter d'autres détails du joueur banni si nécessaire
                playerDetailsHtml += '</pre>';
                
                // Bouton de débannissement
                playerDetailsHtml += '<form method="post">';
                playerDetailsHtml += '<input type="hidden" name="userid" value="' + bannedPlayer.userid + '">';
                playerDetailsHtml += '<button type="submit" name="unban_player">Débannir</button>';
                playerDetailsHtml += '</form>';
                
                bannedPlayerDetailsDiv.innerHTML = playerDetailsHtml;
            }
        });
    });
    
    // Traitement du débannissement en PHP
    <?php
    if (isset($_POST['unban_player'])) {
        $userid = $_POST['userid'];
        
        // Appel à l'API de débannissement
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://game1.playserver.fr:' . $port . '/v1/api/unban',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode(array('userid' => $userid)),
            CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic ' . base64_encode($username . ':' . $password),
                        'Accept: application/json'
                    ),
                ));

        $response = curl_exec($curl);

        curl_close($curl);

        // Afficher la réponse dans le conteneur
        echo 'document.getElementById("banned_player_details").innerHTML = "<div class=\"response-container\">' . $response . '</div>";';
        
        // Lire le fichier JSON des joueurs bannis
        $banned_players_json = file_get_contents('ban_users.json');
        $banned_players_data = json_decode($banned_players_json, true);

        // Filtrer les joueurs pour ne garder que ceux qui ne sont pas débannis
        $filtered_banned_players_data = array_filter($banned_players_data, function($player) use ($userid) {
            return $player['userid'] !== $userid;
        });

        // Réécrire le fichier JSON avec les données filtrées
        file_put_contents('ban_users.json', json_encode(array_values($filtered_banned_players_data), JSON_PRETTY_PRINT));
    }
    ?>
</script>



<form method="post">
    <button type="submit" name="logout" class="logout-button">Déconnexion</button>
</form>

<script src="/scripts/scripts.js"></script>

</body>
</html>
