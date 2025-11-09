<?php
session_start();

// Cria conta padrão
if (!isset($_SESSION["usuarios"])) {
    $_SESSION["usuarios"] = [
        ["nome" => "admin", "senha" => "123", "tipo" => "admin"]
    ];
}

// Login
if (isset($_POST["login"])) {
    foreach ($_SESSION["usuarios"] as $u) {
        if ($u["nome"] == $_POST["nome"] && $u["senha"] == $_POST["senha"]) {
            $_SESSION["logado"] = $u;
            $msg = "Login realizado com sucesso!";
        }
    }
    if (!isset($_SESSION["logado"])) $msg = "Usuário ou senha incorretos.";
}

// Registro
if (isset($_POST["registrar"])) {
    $_SESSION["usuarios"][] = [
        "nome" => $_POST["novo_nome"],
        "senha" => $_POST["nova_senha"],
        "tipo" => "user"
    ];
    $msg = "Conta criada! Agora faça login.";
}

// Logout
if (isset($_GET["logout"])) {
    unset($_SESSION["logado"]);
    header("Location: home.php");
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
    <?php if (isset($msg)) echo "<div class='msg'>$msg</div>"; ?>

    <?php if (!isset($_SESSION["logado"])): ?>
        <h2>Login</h2>
        <form method="post">
            <input type="text" name="nome" placeholder="Usuário" required>
            <input type="password" name="senha" placeholder="Senha" required>
            <button name="login">Entrar</button>
        </form>

        <h3>Criar Conta</h3>
        <form method="post">
            <input type="text" name="novo_nome" placeholder="Novo Usuário" required>
            <input type="password" name="nova_senha" placeholder="Senha" required>
            <button name="registrar">Registrar</button>
        </form>
    <?php else: ?>
        <p>Bem-vindo, <strong><?= htmlspecialchars($_SESSION["logado"]["nome"]) ?></strong> 
        (<?= $_SESSION["logado"]["tipo"] ?>)</p>
        <a href="?logout=1"><button>Sair</button></a>
    <?php endif; ?>
</div>

</body>
</html>
