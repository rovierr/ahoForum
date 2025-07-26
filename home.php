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

$usuario_nome = $_SESSION['nome'];
$usuario_id = $_SESSION['id'];

$query = "SELECT banido, moderator, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($usuario_banido, $usuario_mod, $foto_perfil);
$stmt->fetch();
$stmt->close();

$query = "SELECT foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($foto_perfil);
$stmt->fetch();
$stmt->close();

if ($usuario_banido == 1) {
    header("Location: banido.php");
    exit();
}

$_SESSION['moderator'] = $usuario_mod;

$query = "SELECT p.id, p.titulo, p.conteudo, p.data_postagem, p.id_usuario, u.nome, u.reputacao, u.foto_perfil
          FROM topicos p 
          JOIN usuarios u ON p.id_usuario = u.id 
          WHERE p.visivel = 1 
          ORDER BY p.data_postagem DESC";
$result = $conn->query($query);

function corReputacao($reputacao) {
    if ($reputacao < 10) return ["novato", "tag-novato"];
    elseif ($reputacao < 30) return ["amador", "tag-amador"];
    elseif ($reputacao < 60) return ["intermediário", "tag-intermediario"];
    elseif ($reputacao < 100) return ["avançado", "tag-advanced"];
    else return ["veterano", "tag-veterano"];
}

function exibirVotos($conn, $id_topico = null, $id_comentario = null) {
    global $usuario_id;

    if ($id_topico !== null) {
        $sql = "SELECT tipo FROM votos WHERE id_usuario = ? AND id_topico = ? AND id_resposta IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $id_topico);
    } elseif ($id_comentario !== null) {
        $sql = "SELECT tipo FROM votos WHERE id_usuario = ? AND id_resposta = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $id_comentario);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $voto = $result->fetch_assoc();

    $voto_positivo = $voto && $voto['tipo'] == 'positivo' ? 'disabled' : '';
    $voto_negativo = $voto && $voto['tipo'] == 'negativo' ? 'disabled' : '';
    ?>
    <form action="votar.php" method="post">
    <?php
    if ($id_topico !== null) {
        ?>
        <input type="hidden" name="id_topico" value="<?= htmlspecialchars($id_topico) ?>">
        <input type="hidden" name="id_resposta" value="">
    <?php
    } elseif ($id_comentario !== null) {
        ?>
        <input type="hidden" name="id_topico" value="">
        <input type="hidden" name="id_resposta" value="<?= htmlspecialchars($id_comentario) ?>">
    <?php
    }
    ?>
        <!-- Rafael -->
    	<div class="btn-icon-container">
    		<button type='submit' name='tipo' value='positivo' <?= $voto_positivo ?> class="btn-icon">
				<svg class="<?= $voto_positivo ? 'voted-up' : '' ?>" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-big-up-icon lucide-arrow-big-up"><path d="M9 18v-6H5l7-7 7 7h-4v6H9z"/></svg>
			</button>
    		<button type='submit' name='tipo' value='negativo' <?= $voto_negativo ?> class="btn-icon">
				<svg class="<?= $voto_negativo ? 'voted-down' : '' ?>" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-big-down-icon lucide-arrow-big-down"><path d="M15 6v6h4l-7 7-7-7h4V6h6z"/></svg>
			</button>
    		<?php if ($voto): ?>
        		<button type='submit' name='tipo' value='remover' class="btn-icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-undo-icon lucide-undo"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
        		</button>
    		<?php endif; ?>
		</div>
		<!-- Fim -->
    </form>
    <?php
}

function exibirComentarios($conn, $id_topico, $id_resposta = null, $nivel = 0) {
    $sql = "SELECT c.*, u.nome, u.reputacao FROM comentarios c 
            JOIN usuarios u ON c.id_usuario = u.id 
            WHERE c.id_topico = ? AND " . 
            ($id_resposta === null ? "c.id_resposta IS NULL" : "c.id_resposta = ?") . 
            " ORDER BY c.data_comentario ASC";

    $stmt = $conn->prepare($sql);
    if ($id_resposta === null) {
        $stmt->bind_param("i", $id_topico);
    } else {
        $stmt->bind_param("ii", $id_topico, $id_resposta);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    while ($comentario = $result->fetch_assoc()) {
        list($nivel_texto, $tag) = corReputacao($comentario['reputacao']);
        ?>
        <div style="margin-left: <?= 20 * $nivel ?>px;" class="reply-post-container">
			<span class="tag <?= htmlspecialchars($tag) ?>">
            	<strong>
                	<?= htmlspecialchars($comentario['nome']) ?> <span class="uppercase">[<?= htmlspecialchars($nivel_texto) ?>]</span>
            	</strong>
			</span>
            <p class="conteudo" style="margin-bottom: 15px;"><?= nl2br(htmlspecialchars($comentario['conteudo'])) ?></p>
            <p class="date" style="margin-bottom: 15px;"><?= htmlspecialchars($comentario['data_comentario']) ?></p>

            <?php exibirVotos($conn, null, $comentario['id']); ?>

            <form action="responder.php" method="post" style="margin-top: 5px;">
                <input type="hidden" name="id_topico" value="<?= htmlspecialchars($id_topico) ?>">
                <input type="hidden" name="id_resposta" value="<?= htmlspecialchars($comentario['id']) ?>">
                <textarea name="conteudo" rows="2" required placeholder="Responder comentário"></textarea><br>
                <button type="submit" class="btn-text btn-reply">
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle-reply-icon lucide-message-circle-reply"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/><path d="m10 15-3-3 3-3"/><path d="M7 12h7a2 2 0 0 1 2 2v1"/></svg>
                    <span>Responder</span>
				</button>
            </form>

            <?php
            exibirComentarios($conn, $id_topico, $comentario['id'], $nivel + 1);
            ?>
        </div>
        <?php
    }
}
?>
<!-- Rafael -->

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Home - ahoForum</title>
    <link rel="icon" href="/assets/ahoforum.png" type="image/png">
    <link rel="stylesheet" href="style/global.css">
    <link rel="stylesheet" href="style/home.css">
</head>
<body>
	<header>
		<nav>
    		<ul>
				<div class="nav-left">
        		<li>
        			<a href="home.php">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            		</a>
				</li>
        		<li>
            		<a href="post.php">
                		<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square-icon lucide-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            		</a>
        		</li>
        		<li>
					<a href="procurar.php">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
					</a>
				</li>
				</div>
				<div class="nav-right">
				<button id="toggle-theme-btn"></button>
				<li class="user-info">
					<a href="/profile.php">
                  	<?php
                    	if (!empty($foto_perfil)) {
                        	$base64 = base64_encode($foto_perfil);
                        	echo "<img src='data:image/jpeg;base64,{$base64}' class='pfp-sm' alt='Foto de perfil'>";
                    	} else {
                        	echo "<img src='assets/standard-pfp.jpg' class='pfp-sm' alt='Foto de perfil'>";
                    	}
                    ?> </a>| <a class="logout-btn" href="logout.php">Sair</a>
                </li>
				</div>
    		</ul>
		</nav>
	</header>
        
	<main>
    	<h2>Postagens Recentes</h2>

<?php
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        list($nivel_texto, $tag) = corReputacao($row['reputacao']);
        ?>
        <div class="post-container">
            <section class="info">
                <?php
                    if (!empty($row['foto_perfil'])) {
                        $base64 = base64_encode($row['foto_perfil']);
                        echo "<img src='data:image/jpeg;base64,{$base64}' class='pfp-sm' alt='Foto de perfil'>";
                    } else {
                        echo "<img src='assets/standard-pfp.jpg' class='pfp-sm' alt='Foto de perfil'>";
                    }
                ?>
                <span class="tag <?= htmlspecialchars($tag) ?>">
                    <strong>
                        <?= htmlspecialchars($row['nome']) ?> <span class="uppercase">[<?= htmlspecialchars($nivel_texto) ?>]</span>
                    </strong>
                </span>
                <p class="date">&mdash; <?= htmlspecialchars($row['data_postagem']) ?></p>
            </section>
            
			<div>
            	<h3><?= htmlspecialchars($row['titulo']) ?></h3>
                <p class="conteudo"><?= nl2br(htmlspecialchars($row['conteudo'])) ?></p>
                
				<?php
                	exibirVotos($conn, $row['id']);
				?>                    

                <form action="responder.php" method="post" class="response-form">
                	<input type="hidden" name="id_topico" value="<?= htmlspecialchars($row['id']) ?>">
                    <input type="hidden" name="id_resposta" value="">

                    <textarea name="conteudo" rows="3" required placeholder="Comentar neste tópico"></textarea><br>
				
                    <section>
                        <button type="submit" class="btn-text">
                        	<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle-icon lucide-message-circle"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg>
                            <span>Comentar</span>
                        </button>
					</section>
				</form>

                <?php
                	$stmt_coment_count = $conn->prepare("SELECT COUNT(*) FROM comentarios WHERE id_topico = ?");
                    $stmt_coment_count->bind_param("i", $row['id']);
                    $stmt_coment_count->execute();
                    $stmt_coment_count->bind_result($comentario_total);
                    $stmt_coment_count->fetch();
                    $stmt_coment_count->close();

                    if ($comentario_total > 0):
                 ?>
                 	<details>
                 		<summary>Respostas (<?= $comentario_total ?>)</summary>
                    	<?php exibirComentarios($conn, $row['id']); ?>
                 	</details>
                 <?php endif; ?>
			</div>
<!-- Fim -->
                
			<div class="cta-post-container">
            	<?php if (isset($_SESSION['moderator']) && $_SESSION['moderator'] == 1): ?>
                	<form action="banir.php" method="post" style="margin-top:10px;">
                    	<input type="hidden" name="id_usuario" value="<?= htmlspecialchars($row['id_usuario']) ?>">
                    	<button type="submit" class="btn-text">
                        	<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-hammer-icon lucide-hammer"><path d="m15 12-8.373 8.373a1 1 0 1 1-3-3L12 9"/><path d="m18 15 4-4"/><path d="m21.5 11.5-1.914-1.914A2 2 0 0 1 19 8.172V7l-2.26-2.26a6 6 0 0 0-4.202-1.756L9 2.96l.92.82A6.18 6.18 0 0 1 12 8.4V10l2 2h1.172a2 2 0 0 1 1.414.586L18.5 14.5"/></svg>
                           	<span>Banir Usuário</span>
						</button>
                    </form>
                <?php endif; ?>

                <?php if ((isset($_SESSION['moderator']) && $_SESSION['moderator'] == 1) || (isset($usuario_id) && $usuario_id == $row['id_usuario'])): ?>
                	<form action="apagar_post.php" method="post" style="margin-top:10px;">
                    	<input type="hidden" name="id_topico" value="<?= htmlspecialchars($row['id']) ?>">
                    	<button type="submit" class="btn-text">
                        	<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-icon lucide-trash"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            <span>Apagar Post</span>
                        </button>
                    </form>
				<?php endif; ?>
			</div>
                
                
<!-- Rafael -->                

        </div>
        <?php
    }
} else {
    ?>
    <p>Não há postagens para exibir.</p>
    <?php
}
?>
	</main>

<script src="/scripts/change_theme.js"></script>
</body>
</html>

<?php $conn->close(); ?>
<!-- Fim -->                