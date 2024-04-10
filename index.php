<?php
session_start();

// Vérifie si l'utilisateur est déjà connecté
if ((!isset($_SESSION['username']) || !isset($_SESSION['password']) || !isset($_SESSION['port'])) && basename($_SERVER['PHP_SELF']) != 'index.php') {
    // Si l'utilisateur n'est pas déjà sur la page de connexion, redirige vers index.php
    header("Location: index.php");
    exit();
}

// Traitement de la soumission du formulaire de connexion
if (isset($_POST['submit_port'])) {
    $port = $_POST['port'];
    $login = $_POST['login'];
    $password = $_POST['password'];

    // Assurez-vous que le port est un nombre valide
    if (is_numeric($port)) {
        // Effectuer une requête cURL pour vérifier les identifiants
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
                'Authorization: Basic ' . base64_encode($login . ':' . $password),
                'Accept: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        // Vérifier si la connexion est autorisée
        if ($http_code == 200) {
            // Les identifiants sont valides, enregistrer les informations dans la session et rediriger vers le panel
            $_SESSION['port'] = $port;
            $_SESSION['username'] = $login;
            $_SESSION['password'] = $password;
            $_SESSION['logged_in'] = true;
            header("Location: panel.php");
            exit();
        } elseif ($http_code == 401) {
            // Les identifiants sont incorrects, afficher un message d'erreur
            $error_message = "Identifiants incorrects. Veuillez réessayer.";
        } else {
            // Une erreur s'est produite lors de la requête cURL, afficher un message d'erreur générique
            $error_message = "Une erreur s'est produite lors de la vérification des identifiants. Veuillez réessayer.";
        }
    } else {
        // Afficher un message d'erreur si le port n'est pas valide
        $error_message = "Le port saisi n'est pas valide.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" type="text/css" href="/styles/styles.css">
</head>
<body>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" type="text/css" href="/styles/login.css">
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h2>Connexion</h2>
        <?php if (isset($error_message)) { ?>
            <p><?php echo $error_message; ?></p>
        <?php } ?>
        <form method="post">

			
			<div class="form-group">
                <label for="login">Nom d'utilisateur :</label>
                <input type="text" id="login" name="login" class="input-field" required>
            </div>
			
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" class="input-field" required>
            </div>
            
            <div class="form-group">
                <label for="port">Port du serveur :</label>
                <input type="text" id="port" name="port" class="input-field" required>
            </div>

            <button type="submit" name="submit_port">Connexion</button>
        </form>
    </div>
</div>

</body>
</html>