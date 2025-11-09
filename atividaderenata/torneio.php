<?php
session_start();

/*
  torneio.php (corrigido)
  - Admin cria/exclui torneios
  - Usuários entram/saem
  - Garante que "jogadores" sempre exista como array
*/

// --- Dados iniciais ---
if (!isset($_SESSION["usuarios"])) {
    $_SESSION["usuarios"] = [
        ["nome" => "admin", "senha" => "123", "tipo" => "admin"]
    ];
}
if (!isset($_SESSION["torneios"])) {
    $_SESSION["torneios"] = [];
}

// --- Garantir compatibilidade: todo torneio tem 'jogadores' como array ---
foreach ($_SESSION["torneios"] as $i => $t) {
    if (!isset($t["jogadores"]) || !is_array($t["jogadores"])) {
        $_SESSION["torneios"][$i]["jogadores"] = [];
    }
}
unset($t);

// --- Criar torneio (somente admin) ---
if (isset($_POST["criar"]) && isset($_SESSION["logado"]) && $_SESSION["logado"]["tipo"] === "admin") {
    $novo = [
        "id" => count($_SESSION["torneios"]) + 1,
        "nome" => trim($_POST["nome"]),
        "local" => trim($_POST["local"]),
        "inicio" => $_POST["inicio"],
        "fim" => $_POST["fim"],
        "jogadores" => []
    ];
    $_SESSION["torneios"][] = $novo;
    header("Location: torneio.php");
    exit;
}

// --- Excluir torneio (apenas admin) ---
if (isset($_GET["del"]) && isset($_SESSION["logado"]) && $_SESSION["logado"]["tipo"] === "admin") {
    $delId = (int)$_GET["del"];
    $filtrados = [];
    foreach ($_SESSION["torneios"] as $t) {
        if ($t["id"] !== $delId) $filtrados[] = $t;
    }
    $_SESSION["torneios"] = $filtrados;
    header("Location: torneio.php");
    exit;
}

// --- Entrar / Sair (usuário logado) ---
if (isset($_SESSION["logado"]) && isset($_GET["acao"]) && isset($_GET["id"])) {
    $acao = $_GET["acao"];
    $id = (int)$_GET["id"];
    $nomeUser = $_SESSION["logado"]["nome"];

    foreach ($_SESSION["torneios"] as $i => $t) {
        if ($t["id"] === $id) {
            // garante array
            if (!isset($t["jogadores"]) || !is_array($t["jogadores"])) {
                $_SESSION["torneios"][$i]["jogadores"] = [];
            }
            // entrar
            if ($acao === "entrar") {
                if (!in_array($nomeUser, $_SESSION["torneios"][$i]["jogadores"]) && count($_SESSION["torneios"][$i]["jogadores"]) < 8) {
                    $_SESSION["torneios"][$i]["jogadores"][] = $nomeUser;
                }
            }
            // sair
            if ($acao === "sair") {
                $_SESSION["torneios"][$i]["jogadores"] = array_values(array_diff($_SESSION["torneios"][$i]["jogadores"], [$nomeUser]));
            }
            break;
        }
    }
    header("Location: torneio.php");
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
    <a href="torneio.php">Torneios</a>
</nav>

<div class="container">
    <h2>Torneios</h2>

    <?php if (empty($_SESSION["torneios"])): ?>
        <p>Nenhum torneio cadastrado.</p>
    <?php else: ?>
        <?php foreach ($_SESSION["torneios"] as $t): ?>
            <?php $jogadores = isset($t["jogadores"]) && is_array($t["jogadores"]) ? $t["jogadores"] : []; ?>
            <div class="card">
                <h3><?= htmlspecialchars($t["nome"]) ?></h3>
                <p><strong>Local:</strong> <?= htmlspecialchars($t["local"]) ?></p>
                <p><strong>Período:</strong> <?= htmlspecialchars($t["inicio"]) ?> a <?= htmlspecialchars($t["fim"]) ?></p>
                <p><strong>Participantes (<?= count($jogadores) ?>/8):</strong></p>

                <?php if (!empty($jogadores)): ?>
                    <ul>
                        <?php foreach ($jogadores as $j): ?>
                            <li><?= htmlspecialchars($j) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <?php if (isset($_SESSION["logado"])): ?>
                    <?php $nomeUser = $_SESSION["logado"]["nome"]; ?>
                    <?php if ($_SESSION["logado"]["tipo"] === "admin"): ?>
                        <a href="?del=<?= $t["id"] ?>" onclick="return confirm('Excluir torneio?')"><button class="btn-danger">Excluir</button></a>
                    <?php else: ?>
                        <?php if (in_array($nomeUser, $jogadores)): ?>
                            <a href="?acao=sair&id=<?= $t["id"] ?>"><button class="btn-danger">Sair</button></a>
                        <?php elseif (count($jogadores) < 8): ?>
                            <a href="?acao=entrar&id=<?= $t["id"] ?>"><button>Participar</button></a>
                        <?php else: ?>
                            <p><em>Torneio cheio.</em></p>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php else: ?>
                    <p><em>Faça login para participar.</em></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION["logado"]) && $_SESSION["logado"]["tipo"] === "admin"): ?>
        <hr>
        <h3>Novo Torneio</h3>
        <form method="post">
            <input name="nome" placeholder="Nome" required>
            <input name="local" placeholder="Local" required>
            <label>Início:</label><input type="date" name="inicio" required>
            <label>Fim:</label><input type="date" name="fim" required>
            <button name="criar">Criar</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
