<?php
// inicia a sessão para acessar variáveis de sessão
session_start();

// ativa a exibição de erros para fins de debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// inclui o arquivo com a configuração de conexão com o banco de dados
include 'db_config.php';

// verifica se o usuário está logado; caso contrário, redireciona para a página de login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// armazena o ID do usuário logado na variável
$usuario_id = $_SESSION['id'];

// inicializa variáveis
$message = "";       // mensagem de retorno ao usuário
$foto_perfil = null; // variável que vai armazenar a foto do usuário

// recupera a foto de perfil do usuário (caso precise exibir no frontend)
$query = "SELECT foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($foto_perfil);
$stmt->fetch();
$stmt->close();

// se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // remove espaços em branco do início e fim dos campos
    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['conteudo']);

    // valida os campos obrigatórios
    if (empty($titulo) || empty($conteudo)) {
        $message = "Erro: Preencha todos os campos: título e conteúdo são obrigatórios.";
    
    // verifica se o conteúdo excede o limite de 1000 caracteres
    } elseif (strlen($conteudo) > 1000) {
        $message = "Erro: O conteúdo excede o limite de 1000 caracteres.";
    
    } else {
        // prepara a query para inserir um novo tópico
        $query = "INSERT INTO topicos (id_usuario, titulo, conteudo) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("iss", $usuario_id, $titulo, $conteudo);

            // tenta executar a query
            if ($stmt->execute()) {
                $message = "Postagem realizada com sucesso!";
            } else {
                $message = "Erro ao realizar a postagem.";
            }

            $stmt->close();
        } else {
            // caso a preparação da query falhe
            $message = "Erro interno ao preparar a operação.";
        }
    }
}

// fecha a conexão com o banco de dados
$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Novo Tópico</title>
	<link rel="icon" href="/assets/ahoforum.png" type="image/png">
    <link rel="stylesheet" href="style/global.css">
    <link rel="stylesheet" href="style/post.css">
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
		<section>
        	<h1>Criar Novo Tópico</h1>
                
            <?php if (!empty($message)): ?>
        		<?php 
        			$isError = str_starts_with($message, 'Erro');
    			?>
                
                <div class="flash-message <?= $isError ? 'error' : 'success' ?>" onclick="this.remove()">
                	<?php if ($isError): ?>
                    	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x-icon lucide-circle-x">
                        	<circle cx="12" cy="12" r="10"/>
                            <path d="m15 9-6 6"/>
                            <path d="m9 9 6 6"/>
                        </svg>
                    <?php else: ?>
                    	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info-icon lucide-info">
                        	<circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4"/>
                            <path d="M12 8h.01"/>
                        </svg>
                    <?php endif; ?>

                        <?= htmlspecialchars($message) ?>
				</div>
    		<?php endif; ?>
                
            <form action="post.php" method="post">
            	<label for="titulo">Título</label>
                <input type="text" name="titulo" id="titulo" placeholder="Insira o título do seu post" required><br><br>

                <label for="conteudo">Conteúdo</label>
                <textarea name="conteudo" id="conteudo" placeholder="Compartilhe as suas ideias..." rows="4" cols="50" required maxlength="2500"></textarea><br><br>

                <button type="submit">Postar</button>
			</form>
		</section>
	</main>
	
	<script src="/scripts/change_theme.js"></script>
</body>
</html>