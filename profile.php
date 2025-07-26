<?php
session_start();

function updateEmail($conn, $usuario_id, &$error_email) {
    if (!isset($_POST['novo_email'])) {
    	return;
    }
        
	$novo_email = trim($_POST['novo_email']);

    if (filter_var($novo_email, FILTER_VALIDATE_EMAIL)) {
        $update = $conn->prepare("UPDATE usuarios SET email = ? WHERE id = ?");
        $update->bind_param("si", $novo_email, $usuario_id);
        $update->execute();
        $update->close();
    } else {
        $error_email = "E-mail inválido!";
    }
}

function updateAvatar($conn, $usuario_id, &$error_foto) {
    if (isset($_FILES["foto_perfil"]) && $_FILES["foto_perfil"]["error"] === UPLOAD_ERR_OK) {
        $arquivoTmp = $_FILES["foto_perfil"]["tmp_name"];
        $nomeArquivo = basename($_FILES["foto_perfil"]["name"]);
        $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));

        if ($extensao == "webp") {
            $error_foto = "Extensão não permitida.";
            return;
        }

        $extensoes_permitidas = ["jpg", "jpeg", "png", "gif", "jfif"];
            
            
		if (getimagesize($arquivoTmp) === false) {
            $error_foto = "Arquivo enviado não é uma imagem válida.";
            return;
        }

        if (in_array($extensao, $extensoes_permitidas)) {
            // Lê o conteúdo binário do arquivo
            $dadosImagem = file_get_contents($arquivoTmp);
            if ($dadosImagem === false) {
                $error_foto = "Falha ao ler a imagem.";
                return;
            }

                
            $null = NULL;
            // Atualiza o campo foto_perfil com o binário
            $updateFoto = $conn->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
            $updateFoto->bind_param("bi", $null, $usuario_id);

            // Essa forma usa "send_long_data" para lidar com BLOB's
            $updateFoto->send_long_data(0, $dadosImagem);

            $updateFoto->execute();
                
            if ($updateFoto->error) {
            	$error_foto = "Erro na execução da query: " . $updateFoto->error;
        	}
                
            $updateFoto->close();
        } else {
            $error_foto = "Tipo de arquivo não permitido.";
        }
    }
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db_config.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

function obterNameTag($reputacao) {
    if ($reputacao >= 100) {
        return "<span class='tag tag-veterano'>[veterano]</span>";
    } elseif ($reputacao >= 50) {
        return "<span class='tag tag-advanced'>[avançado]</span>";
    } elseif ($reputacao >= 25) {
        return "<span class='tag tag-intermediario'>[intermediário]</span>";
    } elseif ($reputacao >= 10) {
        return "<span class='tag tag-amador'>[amador]</span>";
    } else {
        return "<span class='tag tag-novato'>[novato]</span>";
    }
}

$usuario_id = $_SESSION['id'];
$error_email = null;
$error_foto = null;

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    updateEmail($conn, $usuario_id, $error_email);
    updateAvatar($conn, $usuario_id, $error_foto);
}

$query = "SELECT id, nome, email, data_registro, reputacao, moderator, foto_perfil FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();
} else {
    echo "erro: usuário não encontrado.";
    exit();
}

$stmt->close();
$conn->close();

$moderador_texto = $usuario['moderator'] == 1 ? "<span style='color: #ff4500;'>[MODERATOR]</span>" : "";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuário</title>
	<link rel="icon" href="/assets/ahoforum.png" type="image/png">
    <link rel="stylesheet" href="style/global.css">
    <link rel="stylesheet" href="style/profile.css">
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
                    	if (!empty($usuario['foto_perfil'])) {
                        	$base64 = base64_encode($usuario['foto_perfil']);
                        	echo "<img src='data:image/jpeg;base64,{$base64}' class='pfp-sm' alt='Foto de perfil'>";
                    	} else {
                        	echo "<img src='assets/standard-pfp.jpg' class='pfp-sm' alt='Foto de perfil'>";
                        	echo $usuario["foto_perfil"];
                    	}
                    ?> </a>| <a class="logout-btn" href="logout.php">Sair</a>
                </li>
				</div>
    		</ul>
		</nav>
	</header>

    <main>        
        <div class="profile">
			<h2>Perfil de Usuário</h2>

            <article>
                <section class="pfp-container">
					<div class="img-container">
                    <?php
                    if (!empty($usuario['foto_perfil'])) {
                        $base64 = base64_encode($usuario['foto_perfil']);
                        echo "<img src='data:image/jpeg;base64,{$base64}' class='pfp' alt='Foto de perfil'>";
                    } else {
                        echo "<img src='assets/standard-pfp.jpg' class='pfp' alt='Foto de perfil'>";
                        echo $usuario["foto_perfil"];
                    }
                    ?>
					</div>

                    <form method="POST" action="profile.php" enctype="multipart/form-data">
    					<label for="fotoInput" class="custom-file-upload">
        					Selecionar Imagem
    					</label>
    					<input id="fotoInput" type="file" name="foto_perfil" accept="image/*">
    					<button type="submit" class="change-avatar">Alterar Avatar</button>
					</form>
                    <?php if ($error_foto) echo "<p style='color:red;'>$error_foto</p>"; ?>
                </section>

                <section class="info">
                    <p><strong>ID:</strong> <?php echo htmlspecialchars($usuario['id']); ?></p>
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($usuario['nome']) . " " . obterNameTag($usuario['reputacao']) . " " . $moderador_texto; ?></p>

                    <form method="POST" action="profile.php">
                        <p>
                            <strong>Email:</strong>
                            <input type="email" name="novo_email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                            <button type="submit" class="btn-update-email">Atualizar</button>
                        </p>
                        <?php if (isset($error_email)) echo "<p style='color:red;'>$error_email</p>"; ?>
                    </form>

                    <p><strong>Data de Entrada:</strong> <?php echo htmlspecialchars($usuario['data_registro']); ?></p>
                    <p><strong>Reputação:</strong> <?php echo htmlspecialchars($usuario['reputacao']); ?></p>
                </section>
            </article>
		</div>
    </main>
	
	<script src="/scripts/change_theme.js"></script>
</body>
</html>
