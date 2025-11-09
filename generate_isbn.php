<?php
// generate_isbn.php
// Executar uma vez para popular a coluna isbn nos livros sem isbn

ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = 'sql204.infinityfree.com';
$db   = 'if0_40077550_biblioteca';
$user = 'if0_40077550';
$pass = 'wB5aR40CgsfL';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erro conexão: " . $e->getMessage());
}

// Gera um ISBN-13 válido a partir de 12 dígitos (calcula dígito verificador)
function gerarIsbn13() {
    // prefixo fixo 978 (padrão ISBN-13) + 9 dígitos aleatórios = 12 dígitos
    // 3 + 9 = 12. Depois calculamos o 13º dígito (verificador).
    $prefix = '978';
    $rest = str_pad(mt_rand(0, 999999999), 9, '0', STR_PAD_LEFT); // 9 dígitos
    $doze = $prefix . $rest; // 12 dígitos

    // calcular dígito verificador
    $soma = 0;
    for ($i = 0; $i < 12; $i++) {
        $d = intval($doze[$i]);
        $peso = ($i % 2 === 0) ? 1 : 3;
        $soma += $d * $peso;
    }
    $check = (10 - ($soma % 10)) % 10;
    return $doze . (string)$check; // 13 dígitos
}

// Função que garante ISBN único (gera até achar não-existente)
function gerarIsbnUnico($pdo) {
    $tentativas = 0;
    do {
        $isbn = gerarIsbn13();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM livros WHERE isbn = :isbn");
        $stmt->execute([':isbn' => $isbn]);
        $count = $stmt->fetchColumn();
        $tentativas++;
        if ($tentativas > 50) {
            throw new Exception("Muitas tentativas para gerar ISBN único.");
        }
    } while ($count > 0);
    return $isbn;
}

try {
    // Seleciona livros sem isbn
    $rows = $pdo->query("SELECT id, titulo FROM livros WHERE isbn IS NULL OR isbn = ''")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "Todos os livros já possuem ISBN.\n";
        exit;
    }

    $update = $pdo->prepare("UPDATE livros SET isbn = :isbn WHERE id = :id");

    foreach ($rows as $r) {
        $isbn = gerarIsbnUnico($pdo);
        $update->execute([':isbn' => $isbn, ':id' => $r['id']]);
        echo "Livro ID {$r['id']} ('{$r['titulo']}') atualizado com ISBN: $isbn\n";
    }
    echo "Processo concluído.\n";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
