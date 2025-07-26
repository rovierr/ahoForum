<?php
$msg = $_GET["message"] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="icon" href="/assets/ahoforum.png" type="image/png">
    <link rel="stylesheet" href="style/global.css">
    <link rel="stylesheet" href="style/signup.css">
</head>
<body>
    <button id="toggle-theme-btn" class="corner-right"></button>
    <main>
        <h2>Sign Up</h2>

        <?php if ($msg): ?>
            <p class="error-msg"><?php echo htmlspecialchars($msg) ?></p>
        <?php endif; ?>

        <form action="processa.php" method="post" id="signup-form">
            <label>Nome</label>
            <input type="text" name="nome" id="name" required>

            <label>Email</label>
            <input type="email" name="email" id="email" required>

            <label>Senha</label>
            <input type="password" name="senha" id="password" required>

            <label>Confirmar Senha</label>
            <input type="password" name="confirmar_senha" id="confirm-password" required>

            <div>
                <a href="/">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-corner-down-left-icon lucide-corner-down-left"><path d="M20 4v7a4 4 0 0 1-4 4H4"/><path d="m9 10-5 5 5 5"/></svg>
                </a>
                <button type="submit">Criar Conta</button>
            </div>
        </form>
    </main>
	<script src="/scripts/change_theme.js"></script>
</body>
</html>