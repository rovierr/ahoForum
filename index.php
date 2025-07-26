<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_config.php';

$query = "SELECT t.id_usuario, u.nome, u.foto_perfil, t.titulo, t.conteudo, t.data_postagem 
		FROM topicos t
		JOIN usuarios u ON t.id_usuario = u.id
		WHERE t.visivel = 1 AND u.banido = 0
		ORDER BY t.data_postagem DESC
		LIMIT 5";

$stmt = $conn->prepare($query);
$stmt->execute();
$stmt->bind_result($id_usuario, $nome_usuario, $foto_perfil, $titulo, $conteudo, $data_postagem);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ahoForum</title>
    <link rel="icon" href="/assets/ahoforum.png" type="image/png">
	<link rel="stylesheet" href="style/global.css">
    <link rel="stylesheet" href="style/landing.css">
</head>
<body class="intro-page">
    <div class="snap-container">
    <header>
		<button id="toggle-theme-btn"></button>
        <div class="cta-btn">
			<a href="login.php">Log in</a>
			<a href="signup.php">Sign Up</a>
        </div>
        <div class="header-content">
            <img src="/assets/ahoforum.png" class="logo" />
            <h1 id="title">ahoForum</h1>
            <p>Descubra, compartilhe e socialize com pessoas de todo mundo.</p>
        </div>
        <a href="#main" class="down-arrow">
        	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-down-icon lucide-arrow-down"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>
		</a>
    </header>

    <main id="main">
        <section class="recent-posts-container">
            <h2>Posts Recentes</h2>
            <div>
                <?php
				while ($stmt->fetch()) {
                        $titulo_html = htmlspecialchars($titulo);
                        $conteudo_html = htmlspecialchars($conteudo);
                        $nome_html = htmlspecialchars($nome_usuario);
                        $data_formatada = date("d/m/Y H:i", strtotime($data_postagem));   
                        
						echo "<article class='recent-post'>";
                        if (!empty($foto_perfil)) {
                            $base64 = base64_encode($foto_perfil);
                            echo "<img src='data:image/jpeg;base64,{$base64}' class='pfp' alt='Foto de perfil'>";
                        } else {
                            echo "<img src='assets/standard-pfp.jpg' class='pfp' alt='Foto de perfil'>";
                        }
                        
                        echo "<div>";
                        echo "<h3 class='post-author'>$nome_html</h3>";
                        echo "<h4 class='post-title'>$titulo_html</h4>";
                        echo "<p class='post-preview'>$conteudo_html</p>";
                        echo "<p class='post-meta'>Postado em $data_formatada</p>";
                        echo "</div>";
                        echo "</article>";
                }

                $stmt->close();
                ?>
            </div>
        </section>

        <section class="how-to-participate">
            <h2>Como Participar?</h2>
            <p>Para postar mensagens e interagir com a comunidade, é necessário fazer login.</p>
            <p>Se ainda não tem conta, <a href="signup.php">crie uma agora</a> e junte-se a nós!</p>
        </section>
        
        <footer>
        	<p>&copy; <?= date("Y") ?> ahoForum. All rights reserved.</p>
    	</footer>
    </main>
    </div>

	<script src="/scripts/change_theme.js"></script>
</body>
</html>