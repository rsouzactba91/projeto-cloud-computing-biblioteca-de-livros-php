<?php
session_start();

// Login simples (para protótipo)
if(!isset($_SESSION['usuario'])){
    $_SESSION['usuario'] = 'visitante'; // usuário padrão
}

// Logout
if(isset($_GET['logout'])){
    session_destroy();
    header("Location: index.php");
    exit();
}

// Array de livros por categoria com quantidade
$livros = [
    "suspense" => [
        ["titulo" => "O Silêncio dos Inocentes", "autor" => "Thomas Harris", "resumo" => "Suspense psicológico clássico.", "quantidade" => 5],
        ["titulo" => "Garota Exemplar", "autor" => "Gillian Flynn", "resumo" => "Mistério e reviravoltas impressionantes.", "quantidade" => 3]
    ],
    "terror" => [
        ["titulo" => "It: A Coisa", "autor" => "Stephen King", "resumo" => "Terror em sua forma mais assustadora.", "quantidade" => 4],
        ["titulo" => "O Exorcista", "autor" => "William Peter Blatty", "resumo" => "Clássico de possessão demoníaca.", "quantidade" => 2]
    ]
];

// Categoria selecionada via GET
$categoriaSelecionada = isset($_GET['categoria']) ? $_GET['categoria'] : null;

// Adicionar livro ao carrinho
if(isset($_GET['add'])) {
    $livroAdicionado = $_GET['add'];
    if(!isset($_SESSION['carrinho'])) $_SESSION['carrinho'] = [];
    $_SESSION['carrinho'][] = $livroAdicionado;
    header("Location: index.php?categoria=" . ($categoriaSelecionada ?? ""));
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Protótipo Biblioteca</title>
<style>
body { margin:0; font-family: Arial, sans-serif; }
.navbar { background-color: #333; color: white; padding: 10px; display: flex; justify-content: space-between; align-items: center; }
.navbar a { color:white; text-decoration:none; margin-left:10px; }
.container { display:flex; height:90vh; }
.sidebar { width:200px; background-color:#f4f4f4; padding:15px; }
.sidebar ul { list-style:none; padding:0; }
.sidebar li { padding:8px; }
.sidebar li:hover { background-color:#ddd; cursor:pointer; }
.content { flex:1; padding:20px; overflow-y:auto; }
.card { border:1px solid #ccc; border-radius:8px; padding:10px; margin-bottom:10px; }
button { padding:5px 10px; cursor:pointer; }
</style>
</head>
<body>

<div class="navbar">
    <div>📚 Biblioteca | Usuário: <?php echo $_SESSION['usuario']; ?></div>
    <div>
        <a href="checkout.php">🛒 Carrinho (<?php echo isset($_SESSION['carrinho']) ? count($_SESSION['carrinho']) : 0; ?>)</a>
        <a href="?logout=true">Sair</a>
    </div>
</div>

<div class="container">
    <div class="sidebar">
        <h3>Categorias</h3>
        <ul>
            <?php foreach($livros as $cat => $lista): ?>
                <li><a href="?categoria=<?php echo $cat; ?>"><?php echo ucfirst($cat); ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="content">
        <?php
        if($categoriaSelecionada && isset($livros[$categoriaSelecionada])) {
            echo "<h3>" . strtoupper($categoriaSelecionada) . "</h3>";
            foreach($livros[$categoriaSelecionada] as $livro) {
                echo "<div class='card'>";
                echo "<h4>" . $livro['titulo'] . "</h4>";
                echo "<p><b>Autor:</b> " . $livro['autor'] . "</p>";
                echo "<p>" . $livro['resumo'] . "</p>";
                echo "<p><b>Disponível:</b> " . $livro['quantidade'] . "</p>";
                if($livro['quantidade'] > 0){
                    echo "<a href='?categoria=$categoriaSelecionada&add=" . urlencode($livro['titulo']) . "'><button>Adicionar ao carrinho</button></a>";
                } else {
                    echo "<button disabled>Indisponível</button>";
                }
                echo "</div>";
            }
        } else {
            echo "<h3>Escolha uma categoria</h3>";
        }
        ?>
    </div>
</div>

</body>
</html>
