<?php
// Inicia a sessão
session_start();

// Inclui o arquivo com as configurações de conexão com o banco de dados
include 'db_config.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    die("usuário não logado."); // Encerra o script se não estiver logado
}

// Obtém o ID do usuário logado
$usuario_id = $_SESSION['id'];

// Verifica se o usuário tem privilégio de moderador
$moderador = isset($_SESSION['moderator']) && $_SESSION['moderator'] == 1;

// Verifica se a requisição foi feita via POST e se o ID do tópico foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_topico'])) {
    // Garante que o valor recebido seja um número inteiro
    $id_topico = intval($_POST['id_topico']);

    // Prepara e executa uma consulta para obter o ID do usuário dono do tópico
    $stmt = $conn->prepare("SELECT id_usuario FROM topicos WHERE id = ?");
    $stmt->bind_param("i", $id_topico);
    $stmt->execute();
    $stmt->bind_result($id_dono);

    // Verifica se o tópico foi encontrado
    if (!$stmt->fetch()) {
        $stmt->close();
        die("oost não encontrado."); // Obs: erro de digitação aqui, "post"
    }
    $stmt->close();

    // Verifica se o usuário logado é o dono do post ou um moderador
    if ($usuario_id !== $id_dono && !$moderador) {
        die("Você não tem permissão para apagar este post."); // Bloqueia ação indevida
    }

    // Marca o post como invisível (soft delete)
    $stmtDel = $conn->prepare("UPDATE topicos SET visivel = 0 WHERE id = ?");
    $stmtDel->bind_param("i", $id_topico);
    $stmtDel->execute();
    $stmtDel->close();
}

// Fecha a conexão com o banco
$conn->close();

// Redireciona o usuário de volta para a página inicial
header("Location: home.php");
exit();
?>
