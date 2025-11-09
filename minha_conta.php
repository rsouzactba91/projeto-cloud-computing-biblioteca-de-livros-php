<?php
session_start();

// Verifica se o usuário está logado
if(!isset($_SESSION['username'])){
    header("Location: login.php");
    exit();
}

// Conexão com o banco
$servername = "localhost:3306";
$dbUsername = "root";
$dbPassword = "";
$database = "biblioteca";

$mysqli = new mysqli($servername, $dbUsername, $dbPassword, $database);

if($mysqli->connect_error){
    die("Falha na conexão: " . $mysqli->connect_error);
}

// Obter dados do usuário logado
$nomeUsuario = $_SESSION['username'];
$stmt = $mysqli->prepare("SELECT * FROM usuarios WHERE nome = ?");
$stmt->bind_param("s", $nomeUsuario);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Define se é admin
$isAdmin = isset($user['tipo']) && $user['tipo'] === 'admin';

// Alterar senha
$mensagem = '';
if(isset($_POST['nova_senha'])){
    $nova_senha = $_POST['nova_senha'];
    $stmt = $mysqli->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    $stmt->bind_param("si", $nova_senha, $user['id']);
    if($stmt->execute()){
        $mensagem = 'Senha alterada com sucesso!';
    } else {
        $mensagem = 'Erro ao alterar a senha.';
    }
    $stmt->close();
}

// Obter pedidos/aluguel do usuário
$usuario_id = $user['id'];
$stmt = $mysqli->prepare("SELECT * FROM pedidos WHERE usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$pedidos = $stmt->get_result();
$stmt->close();

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Minha Conta</title>
<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f9f9f9; }
h2 { margin-top: 0; }
form { margin-bottom: 20px; }
input { padding: 5px; margin-right: 5px; }
button { padding: 5px 10px; }
table { border-collapse: collapse; width: 100%; margin-top: 10px; background: #fff; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
th { background-color: #f4f4f4; }
a { text-decoration: none; color: blue; }
a.logout { float: right; color: red; }
.container { max-width: 800px; margin: auto; }
.admin-link { display: inline-block; margin-top: 10px; padding: 6px 12px; background: #007BFF; color: #fff; border-radius: 6px; }
.admin-link:hover { background: #0056b3; }
</style>
</head>
<body>

<div class="container">
<h2>Minha Conta</h2>
<p>Bem-vindo, <b><?php echo htmlspecialchars($user['nome']); ?></b>! 
<a href="../index/login.php" class="logout">Sair</a></p>

<?php if($isAdmin): ?>
    <a href="extrair_capas.php" class="admin-link">Extrair Capas de Livros</a>
<?php endif; ?>

<h3>Alterar Senha</h3>
<form method="post">
    <input type="password" name="nova_senha" placeholder="Nova senha" required>
    <button type="submit">Alterar</button>
</form>
<p><?php echo $mensagem; ?></p>

<h3>Meus Pedidos/Aluguel de Livros</h3>
<?php if($pedidos->num_rows > 0): ?>
<table>
    <tr>
        <th>ID Pedido</th>
        <th>Livro</th>
        <th>Data do Pedido</th>
        <th>Status</th>
    </tr>
    <?php while($pedido = $pedidos->fetch_assoc()): ?>
    <tr>
        <td><?php echo htmlspecialchars($pedido['id']); ?></td>
        <td><?php echo htmlspecialchars($pedido['livro']); ?></td>
        <td><?php echo htmlspecialchars($pedido['data_pedido']); ?></td>
        <td><?php echo htmlspecialchars($pedido['status']); ?></td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
<p>Você ainda não possui pedidos/alugueis.</p>
<?php endif; ?>
</div>

</body>
</html>
