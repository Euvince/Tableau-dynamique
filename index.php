<?php
require "vendor/autoload.php";

use App\NumberHelper;
use App\TableHelper;
use App\URLHelper;

define('PER_PAGE', 20);

$server = 'localhost';
$login = 'root';
$password = '';

$pdo = new PDO("mysql:host=$server;dbname=tableau_dynamique;", $login, $password, [
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$query = 'SELECT * FROM products';
$queryCount = "SELECT COUNT(id) as count FROM products";
$params = [];

$sortable = ['id', 'name', 'cty', 'price', 'address'];

//RECHERCHE PAR VILLE

if(!empty($_GET['q'])) {
    $query .= 'WHERE city LIKE :city';
    $queryCount .= 'WHERE city LIKE :city';
    $params['city'] = '%' . $_GET['q'] . '%';
}

//ORGANISATION

if (!empty($_GET['sort']) && in_array($_GET['sort'], $sortable)) {
    $direction = $_GET['dir'] ?? 'asc';
    if(!in_array($direction, ['asc', 'desc'])) {
        $direction = 'asc';
    }
    $query .= "ORDER BY" . $_GET['sort'] . "$direction";
}

//PAGINATION

$page = (int)($_GET['page'] ?? 1);
$offset = ($page - 1) * PER_PAGE;

// $query .= "LIMIT" . PER_PAGE . " OFFSET $offset";

$statement = $pdo->prepare($query);
$statement->execute($params);
$products = $statement->fetchAll();

$statement2 = $pdo->prepare($queryCount);
$statement2->execute($params);
$count = (int)$statement2->fetch()['count'];

$pages = ceil($count / PER_PAGE);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biens Immobiliers</title>

    <link rel="stylesheet" href="./bootstrap-5.1.3-dist/css/bootstrap.min.css">
</head>
<body class="p-4">

    <form action="" class="mb-4">
        <h1>Les Biens Immobiliers</h1>
        <div class="form-group">
            <input type="text" class="form-control" name="q" placeholder="Rechercher par Ville" value="<?= htmlentities($_GET['q'] ?? null); ?>">
        </div>
        <button class="btn btn-primary mt-3">Rechercher</button>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th><?= TableHelper::sort('id', 'ID', $_GET) ?></th>
                <th><?= TableHelper::sort('name', 'Nom', $_GET) ?></th>
                <th><?= TableHelper::sort('price', 'Prix', $_GET) ?></th>
                <th><?= TableHelper::sort('city', 'Ville', $_GET) ?></th>
                <th><?= TableHelper::sort('address', 'Adresse', $_GET) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>#<?= $product['id']; ?></td>
                    <td><?= $product['name']; ?></td>
                    <td><?= NumberHelper::price ($product['price']); ?></td>
                    <td><?= $product['city']; ?></td>
                    <td><?= $product['address']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if($pages > 1 && $page > 1):  ?>
        <a href="<?= URLHelper::withParam($_GET, 'p', $page - 1); ?>" class="btn btn-primary">Page précédente</a>
    <?php endif; ?>

    <?php if($pages > 1 && $page < $pages):  ?>
        <a href="<?= URLHelper::withParam($_GET, 'p', $page + 1); ?>">Page suivante</a>
    <?php endif; ?>
</body>
</html>