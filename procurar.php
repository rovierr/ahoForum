<?php
// inicia a sessão para acessar variáveis de sessão do usuário logado
session_start();

// inclui as configurações de conexão com o banco de dados
include 'db_config.php';

// inicializa a variável de busca e o array de resultados
$termo = '';
$resultados = [];

// obtém o ID do usuário logado
$usuario_id = $_SESSION['id'];

// inicializa a variável da foto de perfil
$foto_perfil = null;

// consulta a foto de perfil do usuário logado (caso seja necessário exibir no frontend)
$query = "SELECT foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($foto_perfil);
$stmt->fetch();
$stmt->close();

// verifica se a requisição foi feita via método GET e se o parâmetro 'busca' foi fornecido
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['busca'])) {
    // remove espaços em branco da entrada
    $termo = trim($_GET['busca']);

    // se o termo de busca não estiver vazio
    if (!empty($termo)) {
        // prepara a query para buscar tópicos com título semelhante ao termo digitado
        $query = "SELECT t.id, t.titulo, t.conteudo, u.nome AS autor, u.foto_perfil, t.data_postagem 
                  FROM topicos t
                  JOIN usuarios u ON t.id_usuario = u.id
                  WHERE t.titulo LIKE ? AND t.visivel = 1";

        $stmt = $conn->prepare($query);

        // adiciona o curinga '%' antes e depois do termo para permitir busca parcial
        $like = '%' . $termo . '%';
        $stmt->bind_param("s", $like);

        // executa a consulta
        $stmt->execute();

        // obtém os resultados da consulta
        $result = $stmt->get_result();

        // armazena os resultados no array $resultados
        while ($linha = $result->fetch_assoc()) {
            $resultados[] = $linha;
        }

        // fecha o statement
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Pesquisar Tópicos</title>
	<link rel="icon" href="/assets/ahoforum.png" type="image/png">
	<link rel="stylesheet" href="style/global.css">
	<link rel="stylesheet" href="style/procurar.css">
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
        <div class="container">
			<h2>Pesquisar Tópicos</h2>
            <form method="get" action="procurar.php">
                <div class="input-wrapper">
            		<input type="text" name="busca" placeholder="Pesquisar..." value="<?php echo htmlspecialchars($termo); ?>" required>
					<span class="search-icon">
                    	<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-icon lucide-search"><path d="m21 21-4.34-4.34"/><circle cx="11" cy="11" r="8"/></svg>
                	</span>
                </div>

				<button type="submit" style="display: hidden;">Buscar</button>
			</form>

            <hr>

            <?php if (!empty($resultados)): ?>
            	<h3>Resultados da Pesquisa</h3>
                <hr>
                <?php foreach ($resultados as $topico): ?>
                <div class="resultado">
					<div class="post-user-info">
						<?php
                            if (!empty($topico['foto_perfil'])) {
                            	$base64 = base64_encode($topico['foto_perfil']);
                                echo "<img src='data:image/jpeg;base64,{$base64}' class='pfp-sm' alt='Foto de perfil'>";
                            } else {
                            	echo "<img src='assets/standard-pfp.jpg' class='pfp-sm' alt='Foto de perfil'>";
                        	}
						?>
                        
                        <span>
                            <?php echo htmlspecialchars($topico['autor']); ?>
                            <span>&mdash;</span>
							<?php 
                            	$dt = new DateTime($topico['data_postagem']);
                                $fmt = new IntlDateFormatter('pt_BR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
                                $fmt->setPattern('d MMM yyyy');
                                
                                $data_formatada = $fmt->format($dt);
                                $data_formatada = mb_convert_case($data_formatada, MB_CASE_TITLE, "UTF-8");

                                echo $data_formatada;
                            ?>
                        </span>
                    </div>
					<div class="content">
						<h3><?php echo htmlspecialchars($topico['titulo']); ?></h3>
                        <p>
                        	<?php 
                            	$conteudo = htmlspecialchars($topico['conteudo']);

								if (mb_strlen($conteudo) > 500) {
                                	echo nl2br(substr($conteudo, 0, 100)) . '...';
                                } else {
                                	echo nl2br($conteudo);
                                }
                            ?>
                        </p>
					</div>
                </div>
           		<?php endforeach; ?>
            <?php elseif (!empty($termo)): ?>
            	<p>Nenhum tópico encontrado com o termo "<?php echo htmlspecialchars($termo); ?>".</p>
            <?php endif; ?>
		</div>
    </main>
	
    <script src="/scripts/remove_search_icon.js"></script>
	<script src="/scripts/change_theme.js"></script>
</body>
</html>
