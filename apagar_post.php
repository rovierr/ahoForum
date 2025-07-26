<?php
// inicia a sessão
session_start();

// inclui o arquivo com as configurações de conexão com o banco de dados
include 'db_config.php';

// verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    die("usuário não logado."); // encerra o script se não estiver logado
}

// obtém o ID do usuário logado
$usuario_id = $_SESSION['id'];

// verifica se o usuário tem privilégio de moderador
$moderador = isset($_SESSION['moderator']) && $_SESSION['moderator'] == 1;

// verifica se a requisição foi feita via POST e se o ID do tópico foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_topico'])) {
    // garante que o valor recebido seja um número inteiro
    $id_topico = intval($_POST['id_topico']);

    // prepara e executa uma consulta para obter o ID do usuário dono do tópico
    $stmt = $conn->prepare("SELECT id_usuario FROM topicos WHERE id = ?");
    $stmt->bind_param("i", $id_topico);
    $stmt->execute();
    $stmt->bind_result($id_dono);

    // verifica se o tópico foi encontrado
    if (!$stmt->fetch()) {
        $stmt->close();
        die("oost não encontrado.");
    }
    $stmt->close();

    // berifica se o usuário logado é o dono do post ou um moderador
    if ($usuario_id !== $id_dono && !$moderador) {
        die("Você não tem permissão para apagar este post."); // Bloqueia ação indevida
    }

    // marca o post como invisível (soft delete)
    $stmtDel = $conn->prepare("UPDATE topicos SET visivel = 0 WHERE id = ?");
    $stmtDel->bind_param("i", $id_topico);
    $stmtDel->execute();
    $stmtDel->close();
}

// fecha a conexão com o banco
$conn->close();

// redireciona o usuário de volta para a página inicial
header("Location: home.php");
exit();
?>
