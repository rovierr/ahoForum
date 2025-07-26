<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_config.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['id'];
$id_topico = !empty($_POST['id_topico']) ? intval($_POST['id_topico']) : null;
$id_resposta = !empty($_POST['id_resposta']) ? intval($_POST['id_resposta']) : null;
$tipo_voto = $_POST['tipo'] ?? null;

if (!$tipo_voto || ($id_topico === null && $id_resposta === null)) {
    header("Location: home.php");
    exit();
}

// obter autor do conteúdo
if ($id_topico !== null) {
    $sql_autor = "SELECT id_usuario FROM topicos WHERE id = ?";
    $stmt_autor = $conn->prepare($sql_autor);
    $stmt_autor->bind_param("i", $id_topico);
} else {
    $sql_autor = "SELECT id_usuario FROM comentarios WHERE id = ?"; // ajustado para 'comentarios' em vez de 'respostas'
    $stmt_autor = $conn->prepare($sql_autor);
    $stmt_autor->bind_param("i", $id_resposta);
}
$stmt_autor->execute();
$result_autor = $stmt_autor->get_result();
$autor = $result_autor->fetch_assoc();
$id_autor = $autor['id_usuario'] ?? null;

if (!$id_autor || $id_autor == $usuario_id) { // impede votação no próprio conteúdo
    header("Location: home.php");
    exit();
}

// verifica se já existe voto
$sql = "SELECT id, tipo FROM votos WHERE id_usuario = ? AND " .
       ($id_topico !== null ? "id_topico = ? AND id_resposta IS NULL" : "id_resposta = ?");
$stmt = $conn->prepare($sql);
if ($id_topico !== null) {
    $stmt->bind_param("ii", $usuario_id, $id_topico);
} else {
    $stmt->bind_param("ii", $usuario_id, $id_resposta);
}
$stmt->execute();
$result = $stmt->get_result();
$voto = $result->fetch_assoc();

$reputacao_alteracao = 0;

if ($tipo_voto === 'remover' && $voto) {
    // remover o voto
    $sql_delete = "DELETE FROM votos WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $voto['id']);
    $stmt_delete->execute();

    // ajustar reputação com base no voto removido
    $reputacao_alteracao = ($voto['tipo'] === 'positivo') ? -1 : 1;
} elseif ($voto) {
    if ($voto['tipo'] !== $tipo_voto) {
        // atualizar voto (de like para dislike ou vice-versa)
        $sql_update = "UPDATE votos SET tipo = ? WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("si", $tipo_voto, $voto['id']);
        $stmt_update->execute();

        // ajustar reputação: +2 para mudança de dislike para like, -2 para mudança de like para dislike
        $reputacao_alteracao = ($tipo_voto === 'positivo') ? 2 : -2;
    }
    // se o voto é o mesmo, nada é feito (mantém o voto atual)
} else {
    // novo voto
    $sql_insert = "INSERT INTO votos (id_usuario, id_topico, id_resposta, tipo) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $id_resposta_nullable = $id_resposta !== null ? $id_resposta : null;
    $id_topico_nullable = $id_topico !== null ? $id_topico : null;
    $stmt_insert->bind_param("iiss", $usuario_id, $id_topico_nullable, $id_resposta_nullable, $tipo_voto);
    $stmt_insert->execute();

    // ajustar reputação: +1 para like, -1 para dislike
    $reputacao_alteracao = ($tipo_voto === 'positivo') ? 1 : -1;
}

// atualiza reputação do autor, se necessário
if ($reputacao_alteracao != 0) {
    $sql_reputacao = "UPDATE usuarios SET reputacao = reputacao + ? WHERE id = ?";
    $stmt_reputacao = $conn->prepare($sql_reputacao);
    $stmt_reputacao->bind_param("ii", $reputacao_alteracao, $id_autor);
    $stmt_reputacao->execute();
}

header("Location: home.php");
exit();
?>
