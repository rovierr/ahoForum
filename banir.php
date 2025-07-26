<?php
// inicia a sessão
session_start();

// inclui o arquivo de configuração do banco de dados
include('db_config.php');

// verifica se o ID do usuário foi enviado via POST
if (isset($_POST['id_usuario'])) {
    // armazena o ID do usuário recebido
    $id_usuario = $_POST['id_usuario'];

    // prepara uma query SQL para banir o usuário (setar o campo 'banido' como 1)
    $stmt = $conn->prepare("UPDATE usuarios SET banido = 1 WHERE id = ?");
    $stmt->bind_param("i", $id_usuario); // substitui o ? pelo valor de $id_usuario

    // executa a query
    if ($stmt->execute()) {
        // Se o usuário foi banido com sucesso, redireciona para a página 'banido.php'
        header("Location: banido.php");
        exit();    
    } else {
        // caso ocorra erro na execução da query
        echo "Erro ao banir o usuário.";
    }

    // fecha o statement
    $stmt->close();
}
?>
