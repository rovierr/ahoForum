<?php
session_start();
include('db_config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['email']) || !isset($_POST['senha'])) {
		$erro = "Preencha todos os campos.";
        return;
    }
        
	$email = trim($_POST['email']);
    $senha = $_POST['senha'];

	$sql = "SELECT id, nome, senha, banido, moderator FROM usuarios WHERE email = ?";
    $stmt = $conn->prepare($sql);

	if ($stmt) {
    	$stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {
        	$usuario = $resultado->fetch_assoc();

            if ($usuario['banido'] == 1) {
            	$erro = "Sua conta está banida. Você não pode acessar o fórum.";
			} elseif (password_verify($senha, $usuario['senha'])) {
            	$_SESSION['id'] = $usuario['id'];
                $_SESSION['nome'] = $usuario['nome'];
                $_SESSION['moderator'] = $usuario['moderator']; 
                header("Location: home.php");
                exit();
			} else {
            	$erro = "Senha incorreta.";
			}
		} else {
        	$erro = "Usuário não encontrado.";
        }

		$stmt->close();
	} else {
    	$erro = "Erro ao preparar a consulta: " . $conn->error;
	}
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Log In</title>
	<link rel="icon" href="/assets/ahoforum.png" type="image/png">
	<link rel="stylesheet" href="style/global.css">
	<link rel="stylesheet" href="style/login.css">
</head>
<body>
	<button id="toggle-theme-btn" class="corner-right"></button>
    <main>
		<h2>Log In</h2>
        <?php if (isset($erro)) { echo "<p class='error-msg'>$erro</p>"; } ?>
		<form action="login.php" method="post">
            <label>Email</label>
            <br />
            <input type="email" name="email" id="email" required>
            <br />
            <label>Senha</label>
			<br />
            <input type="password" name="senha" id="password" required>
            <br />
        	<button type="submit" id="login-btn">Log In</button>
		</form>
		<a href="./signup.php" class="register">Não tens conta? Regista-te</a>
	</main>
    <script>
		document.addEventListener('DOMContentLoaded', () => {
        	const email = document.getElementById("email");
			const password = document.getElementById("password");
			const button = document.getElementById("login-btn");
            
        	const changeButtonColor = () => {
        		if (email.value.trim() && password.value.trim()) {
                    button.disabled = false;
            	}
				else {
					button.disabled = true;
            	}
        	}
        
        	email.addEventListener("input", changeButtonColor);
        	password.addEventListener("input", changeButtonColor);
                        
        	changeButtonColor();
        	
        });
	</script>
	<script src="/scripts/change_theme.js"></script>
</body>
</html>
