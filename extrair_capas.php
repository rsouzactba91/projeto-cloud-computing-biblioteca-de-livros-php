<?php
// Conexão com banco (MySQL)
$pdo = new PDO("mysql:host=localhost;dbname=biblioteca;charset=utf8", "root", "");

// Cria a pasta de capas se não existir
if (!is_dir('uploads/capas')) {
    mkdir('uploads/capas', 0777, true);
}

// Busca todos os livros com arquivo EPUB
$livros = $pdo->query("SELECT id, titulo, arquivo, categoria_id FROM livros WHERE arquivo LIKE '%.epub'")->fetchAll(PDO::FETCH_ASSOC);

$capasExtraidas = 0;

foreach ($livros as $livro) {
    $epubPath = $livro['arquivo'];
    $categoriaId = $livro['categoria_id'];
    
    // Pega o nome da categoria
    $categoria = $pdo->prepare("SELECT nome FROM categorias WHERE id = ?");
    $categoria->execute([$categoriaId]);
    $categoriaNome = $categoria->fetchColumn();
    $categoriaDir = 'uploads/capas/' . strtolower($categoriaNome);
    
    // Cria a pasta da categoria se não existir
    if (!is_dir($categoriaDir)) {
        mkdir($categoriaDir, 0777, true);
    }

    // Nome do arquivo de capa
    $tituloFormatado = strtolower(str_replace(' ', '_', $livro['titulo']));
    $capaDestino = $categoriaDir . '/' . $tituloFormatado . '.jpg';

    // Se a capa já existe, pula
    if (file_exists($capaDestino)) continue;

    $zip = new ZipArchive;
    if ($zip->open($epubPath) === TRUE) {
        $encontrouCapa = false;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if (preg_match('/cover.*\.(jpg|jpeg|png)$/i', $name)) {
                $conteudo = $zip->getFromName($name);
                if ($conteudo !== false) {
                    file_put_contents($capaDestino, $conteudo);
                    $capasExtraidas++;
                    $encontrouCapa = true;
                    break;
                }
            }
        }
        $zip->close();
        if (!$encontrouCapa) echo "Nenhuma capa encontrada em: $epubPath<br>";
    } else {
        echo "Erro ao abrir: $epubPath<br>";
    }
}

echo "<h2>Capas extraídas: $capasExtraidas</h2>";
echo "<p>Verifique a pasta <b>uploads/capas/</b> para os arquivos salvos.</p>";
?>
