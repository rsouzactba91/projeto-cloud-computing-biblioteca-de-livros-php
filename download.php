<?php
if(!isset($_GET['isbn'])) {
    die("Livro não especificado.");
}

$isbn = $_GET['isbn'];

try {
    $pdo = new PDO(
        "mysql:host=sql204.infinityfree.com;dbname=if0_40077550_biblioteca;charset=utf8",
        "if0_40077550",
        "wB5aR40CgsfL"
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta usando ISBN
    $stmt = $pdo->prepare("SELECT arquivo, titulo FROM livros WHERE isbn = :isbn");
    $stmt->execute([':isbn' => $isbn]);
    $livro = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$livro) {
        die("Livro não encontrado.");
    }

    $file = __DIR__ . '/' . $livro['arquivo'];

    if(!file_exists($file)) {
        die("Arquivo não encontrado.");
    }

    // Headers de download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($livro['arquivo']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;

} catch(PDOException $e) {
    die("Erro: " . $e->getMessage());
}
