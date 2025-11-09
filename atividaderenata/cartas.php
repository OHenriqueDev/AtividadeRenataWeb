<?php
session_start();

/* ==========================================
   cartas.php
   - Lista e cadastra cartas com imagem e valor
   - Usuário logado pode criar e excluir as suas
   - Admin pode excluir qualquer carta
========================================== */

// Inicializa dados básicos
if (!isset($_SESSION["usuarios"])) {
    $_SESSION["usuarios"] = [
        ["nome" => "admin", "senha" => "123", "tipo" => "admin"]
    ];
}
if (!isset($_SESSION["cartas"])) {
    $_SESSION["cartas"] = [];
}

// Excluir carta
if (isset($_GET["del"]) && isset($_SESSION["logado"])) {
    $id = (int) $_GET["del"];
    foreach ($_SESSION["cartas"] as $i => $c) {
        $usuario = $_SESSION["logado"];
        if ($usuario["tipo"] === "admin" || $c["autor"] === $usuario["nome"]) {
            if ($c["id"] === $id) {
                unset($_SESSION["cartas"][$i]);
                $_SESSION["cartas"] = array_values($_SESSION["cartas"]);
                break;
            }
        }
    }
    header("Location: cartas.php");
    exit;
}

// Criar nova carta
if (isset($_POST["nova_carta"]) && isset($_SESSION["logado"])) {
    $nome = trim($_POST["nome"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "");
    $valor = floatval($_POST["valor"] ?? 0);
    $img = null;

    if (!empty($_FILES["imagem"]["tmp_name"])) {
        $tmp = $_FILES["imagem"]["tmp_name"];
        $conteudo = file_get_contents($tmp);
        $mime = mime_content_type($tmp) ?: 'image/png';
        $img = "data:$mime;base64," . base64_encode($conteudo);
    }

    $_SESSION["cartas"][] = [
        "id" => count($_SESSION["cartas"]) + 1,
        "nome" => $nome,
        "tipo" => $tipo,
        "valor" => $valor,
        "img" => $img,
        "autor" => $_SESSION["logado"]["nome"]
    ];

    header("Location: cartas.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>PokeCenter</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>PokeCenter</header>
<nav>
    <a href="home.php">Home</a>
    <a href="cartas.php">Cartas</a>
    <a href="torneio.php">Torneio</a>
</nav>

<div class="container">
    <h2>Cartas Disponíveis</h2>

    <?php if (empty($_SESSION["cartas"])): ?>
        <p>Nenhuma carta cadastrada.</p>
    <?php else: ?>
        <?php foreach ($_SESSION["cartas"] as $c): ?>
            <div class="card">
                <h3><?= htmlspecialchars($c["nome"]) ?></h3>
                <p>Tipo: <?= htmlspecialchars($c["tipo"]) ?></p>
                <p><strong>Valor:</strong> R$ <?= number_format($c["valor"], 2, ',', '.') ?></p>
                <?php if (!empty($c["img"])): ?>
                    <img src="<?= htmlspecialchars($c["img"]) ?>" alt="<?= htmlspecialchars($c["nome"]) ?>">
                <?php endif; ?>
                <small>Criado por <?= htmlspecialchars($c["autor"]) ?></small><br>
                <small>Disponivel na PokeCenter</small><br>
                <?php if (isset($_SESSION["logado"])): ?>
                    <?php
                    $usuario = $_SESSION["logado"];
                    if ($usuario["tipo"] === "admin" || $usuario["nome"] === $c["autor"]):
                    ?>
                        <a href="?del=<?= $c["id"] ?>" onclick="return confirm('Excluir esta carta?')">
                            <button>Excluir</button>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION["logado"])): ?>
        <hr>
        <h3>Adicionar Nova Carta</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="nome" placeholder="Nome da Carta" required><br><br>
            <input type="text" name="tipo" placeholder="Tipo" required><br><br>
            <input type="number" step="0.01" name="valor" placeholder="Valor (R$)" required><br><br>
            <input type="file" name="imagem" accept="image/*"><br><br>
            <button name="nova_carta" type="submit">Cadastrar</button>
        </form>
    <?php else: ?>
        <p>Faça login em <a href="home.php">Home</a> para criar ou excluir cartas.</p>
    <?php endif; ?>

</div>
</body>
</html>
