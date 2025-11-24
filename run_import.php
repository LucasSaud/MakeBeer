<?php
/**
 * Script para executar a importação de receitas BeerXML
 * 
 * Uso: php run_import.php <caminho_do_arquivo_beerxml>
 */

// Verificar se o caminho do arquivo foi fornecido
if ($argc < 2) {
    echo "Uso: php run_import.php <caminho_do_arquivo_beerxml>\n";
    echo "Exemplo: php run_import.php beerxml/f_mIP1gcsGfXWmu9RU4Mp2BTmzlsI3_Brewfather_BeerXML_AtomosSession_20251123.xml\n";
    exit(1);
}

// Obter o caminho do arquivo
$xmlFilePath = $argv[1];

// Verificar se o arquivo existe
if (!file_exists($xmlFilePath)) {
    echo "Erro: Arquivo '$xmlFilePath' não encontrado.\n";
    exit(1);
}

// Incluir o importador
require_once 'import_beerxml.php';

try {
    echo "Iniciando importação do arquivo: $xmlFilePath\n";
    
    // Criar instância do importador
    $importer = new BeerXMLImporter();
    
    // Importar a receita
    $importer->importRecipe($xmlFilePath);
    
    echo "Importação concluída com sucesso!\n";
} catch (Exception $e) {
    echo "Erro durante a importação: " . $e->getMessage() . "\n";
    exit(1);
}
?>