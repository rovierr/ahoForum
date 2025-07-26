<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['nome']) && isset($_POST['email']) && isset($_POST['senha'])) {
        $nome = trim($_POST["nome"]);
        $email = trim($_POST["email"]);
        $senha = $_POST["senha"];
		$confirmar_senha = $_POST["confirmar_senha"];
            
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        	header("Location: signup.php?message=Email+Inválido");
            exit;
        }
		
        if ($senha == $confirmar_senha) {
	        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        } else {
        	header("Location: signup.php?message=As+senhas+não+coincidem");
            $check->close();
            $conn->close();
            exit();
        }

        $check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            header("Location: signup.php?message=O+Usuário+já+está+registrado");
            $check->close();
            $conn->close();
            exit();
        }
        $check->close();

        $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nome, $email, $senha_hash);

        if ($stmt->execute()) {
            header('Location: login.php');
            exit();
        } else {
            echo "erro ao registrar: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "todos campos são obrigatorios";
    }
} else {
    echo "acesso inválido.";
}
?>
