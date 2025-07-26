<?php
// inicia a sessão
session_start();

// inclui o arquivo de configuração do banco de dados
include 'db_config.php';

// verifica se o usuário está logado; caso contrário, redireciona para a página de login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// obtém o ID do usuário a partir da sessão
$id_usuario = $_SESSION['id'];

// recebe os dados enviados via POST
$id_topico = $_POST['id_topico'];
$conteudo = trim($_POST['conteudo']); // Remove espaços em branco do início e fim
$id_resposta = !empty($_POST['id_resposta']) ? $_POST['id_resposta'] : null; // Comentário de resposta (caso exista)

// define o tempo mínimo (em segundos) entre comentários consecutivos
$espera_segundos = 5;
$agora = time(); // Timestamp atual

// inicializa o tempo do último comentário, se ainda não estiver definido
if (!isset($_SESSION['ultimo_comentario'])) {
    $_SESSION['ultimo_comentario'] = 0;
}

// verifica se o usuário está comentando muito rápido (flood)
if (($agora - $_SESSION['ultimo_comentario']) < $espera_segundos) {
    $faltam = $espera_segundos - ($agora - $_SESSION['ultimo_comentario']);
    die("Aguarde $faltam segundo(s) antes de comentar novamente.");
}

// se o conteúdo do comentário não estiver vazio
if (!empty($conteudo)) {
    // prepara a inserção do comentário no banco de dados
    $stmt = $conn->prepare("INSERT INTO comentarios (id_topico, id_usuario, conteudo, id_resposta) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $id_topico, $id_usuario, $conteudo, $id_resposta);
    $stmt->execute();

    // atualiza o timestamp do último comentário na sessão
    $_SESSION['ultimo_comentario'] = $agora;
}

// redireciona de volta para a página principal após o envio do comentário
header("Location: home.php");
exit();
?>
