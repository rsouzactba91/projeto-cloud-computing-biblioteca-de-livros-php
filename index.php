<?php
// Conexão com banco (InfinityFree)
try {
    $pdo = new PDO(
        "mysql:host=sql204.infinityfree.com;dbname=if0_40077550_biblioteca;charset=utf8",
        "if0_40077550",
        "wB5aR40CgsfL"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco: " . $e->getMessage());
}

// Parâmetros de filtro
$categoriaSelecionada = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;
$pesquisa = isset($_GET['pesquisa']) ? trim($_GET['pesquisa']) : '';
$pesquisaLike = "%$pesquisa%";

// Buscar todas as categorias
$categorias = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// Query dinâmica para livros
$sql = "SELECT l.isbn, l.titulo, l.autor, l.resumo, l.arquivo, l.capa, l.categoria_id, c.nome AS nome_categoria
        FROM livros l
        JOIN categorias c ON l.categoria_id = c.id
        WHERE 1=1";

$params = [];

if($categoriaSelecionada) {
    $sql .= " AND l.categoria_id = :categoria";
    $params[':categoria'] = $categoriaSelecionada;
}

if($pesquisa !== '') {
    $sql .= " AND (l.titulo LIKE :pesquisa OR l.autor LIKE :pesquisa)";
    $params[':pesquisa'] = $pesquisaLike;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$livros = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Biblioteca - Livros</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="./style.css">
<script src="script.js" defer></script>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
  <div>📚 Biblioteca</div>
  <div class="nav-actions">
    <form method="get" class="search-form">
      <input type="text" name="pesquisa" placeholder="Pesquisar livros..." value="<?= htmlspecialchars($pesquisa) ?>">
      <?php if($categoriaSelecionada): ?>
        <input type="hidden" name="categoria" value="<?= $categoriaSelecionada ?>">
      <?php endif; ?>
      <button type="submit">🔍</button>
    </form>
    <a href="logout.php" class="logout">Sair</a>
  </div>
    <div class="mobile-categorias">
  <?php foreach($categorias as $cat): ?>
    <a href="?categoria=<?= $cat['id'] ?>" <?= ($categoriaSelecionada==$cat['id']) ? 'class="active"' : '' ?>>
      <?= htmlspecialchars(ucfirst($cat['nome'])) ?>
    </a>
  <?php endforeach; ?>
</div>

</div>

<div class="container">
  <!-- Sidebar de categorias -->
  <aside class="sidebar">
    <h3>Categorias</h3>
    <ul>
      <?php foreach($categorias as $cat): ?>
        <li>
          <a href="?categoria=<?= $cat['id'] ?>" <?= ($categoriaSelecionada==$cat['id']) ? 'class="active"' : '' ?>>
            <?= htmlspecialchars(ucfirst($cat['nome'])) ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li><a href="../minha_conta.php">Minha Conta</a></li>
      <li><a href="../checkout.php">Carrinho</a></li>
    </ul>
  </aside>

  <!-- Conteúdo principal -->
  <main class="content">
    <?php if($categoriaSelecionada || $pesquisa !== ''): ?>
      <h2>
        <?= $categoriaSelecionada ? strtoupper(htmlspecialchars(array_column($categorias,'nome','id')[$categoriaSelecionada] ?? '')) : 'Resultados da pesquisa' ?>
      </h2>

      <?php if(!empty($livros)): ?>
        <div class="cards-grid">
         <?php foreach($livros as $livro): ?>
            <?php 
                // Caminho físico da capa
                $capaFisica = __DIR__ . '/' . $livro['capa'];
                $capaURL = file_exists($capaFisica) ? $livro['capa'] : 'uploads/capas/placeholder.jpg';

                // Link de download usando ISBN
                $arquivoURL = "download.php?isbn=" . urlencode($livro['isbn']);
            ?>

            <article class="card" tabindex="0" role="button" aria-pressed="false">
                <!-- Face frontal -->
                <div class="face front">
                    <img src="<?= $capaURL ?>" alt="<?= htmlspecialchars($livro['titulo']) ?>" style="width:100%; height:auto; border-radius:8px; margin-bottom:8px;">
                    <h3><?= htmlspecialchars($livro['titulo']) ?></h3>
                    <p class="autor"><?= htmlspecialchars($livro['autor']) ?></p>
                </div>

                <!-- Face traseira -->
                <div class="face back">
                    <img src="<?= $capaURL ?>" alt="<?= htmlspecialchars($livro['titulo']) ?>" style="width:100%; height:auto; border-radius:8px; margin-bottom:8px;">
                    <h3><?= htmlspecialchars($livro['titulo']) ?></h3>
                    <p class="resumo"><?= htmlspecialchars($livro['resumo']) ?></p>
                    <a class="btn-add" href="<?= $arquivoURL ?>" target="_blank">
                        Abrir Livro 📖
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="no-results">Nenhum livro encontrado.</p>
      <?php endif; ?>
    <?php else: ?>
<h2 style="
    font-family: 'Century Gothic', sans-serif; 
    text-align: center; 
    color: #fff; 
    text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    background-color: rgba(0,0,0,0.2);
    padding: 20px;
    border-radius: 8px;
    display: inline-block;
">
  📖 Para ler os livros, baixe o 
  <a href="https://www.adobe.com/solutions/ebook/digital-editions.html" 
     target="_blank" 
     style="color: #FFD700; text-decoration: underline;">
    Adobe Digital Editions
  </a>
  . Selecione uma categoria e um livro para leitura.
</h2>

      <?php endif; ?>
  </main>
</div>

<footer class="footer">
  <div class="footer-content">
    <p>
      Biblioteca inspirada no 
      <a href="https://www.gutenberg.org/" target="_blank" rel="noopener">Projeto Gutenberg</a>,
            e 
      <a href="https://bibliotecamundial.com.br" target="_blank" rel="noopener">Biblioteca Mundial</a>.
    </p>

    <p>Agradecimentos a todas as iniciativas que promovem o acesso gratuito à leitura e ao conhecimento.</p>

    <p><strong>Desenvolvido por:</strong> Renato de Souza, Thalia Vieira, Leonardo Cruz Ribeiro, Lucas Trecziak e Paulo Dabwya.</p>
  </div>
</footer>



</body>
</html>
