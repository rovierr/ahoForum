<?php
// inicia a sessão para poder destruí-la
session_start();

// destroi todas as variáveis de sessão, encerrando a sessão do usuário
session_destroy();

// redireciona o usuário para a página inicial (index.php)
header("Location: index.php");

// garante que o script seja finalizado imediatamente após o redirecionamento
exit();
?>