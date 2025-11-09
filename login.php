<?php
session_start();

// Conexão com o banco InfinityFree
$servername = "sql204.infinityfree.com";      // Servidor do banco
$username_db = "if0_40077550";                 // Usuário do banco
$password_db = "wB5aR40CgsfL";                // Senha do banco
$database = "if0_40077550_biblioteca";        // Nome do banco

$mysqli = new mysqli($servername, $username_db, $password_db, $database);

// Verifica conexão
if ($mysqli->connect_error) {
    die("Falha na conexão: " . $mysqli->connect_error);
}

$login_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Prepared statement para evitar SQL Injection
    $stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE nome=? AND senha=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['username'] = $username; // Salva usuário na sessão
        header("Location:index.php");
        exit();
    } else {
        $login_error = "Usuário ou senha incorretos.";
    }

    $stmt->close();
}

$mysqli->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Login - Biblioteca</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<link rel="stylesheet" href="login.css">
</head>
<body>
<div class="container mt-5">
    <h2>Login</h2>
    <?php if($login_error): ?>
        <div class="alert alert-danger"><?php echo $login_error; ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <div class="form-group">
            <label>Nome de usuário:</label>
            <input type="text" name="username" class="form-control" required placeholder="Usuário">
        </div>
        <div class="form-group">
            <label>Senha:</label>
            <input type="password" name="password" class="form-control" required placeholder="Senha">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Entrar</button>
    </form>
</div>
</body>
</html>
