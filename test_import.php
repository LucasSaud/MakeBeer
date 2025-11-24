<?php
/**
 * Script de teste para a funcionalidade de importação de receitas BeerXML
 * 
 * Este script pode ser executado via linha de comando para testar a importação
 * de receitas BeerXML sem a interface web.
 */

// Configurações
require_once 'app/config/database.php';
require_once 'app/controllers/ImportController.php';

echo "=== Teste de Importação de Receitas BeerXML ===\n\n";

try {
    // Criar instância do controlador de importação
    $importController = new ImportController();
    
    echo "✓ Controlador de importação criado com sucesso\n";
    
    // Verificar se o diretório de BeerXML existe
    $beerxmlDir = 'beerxml';
    if (is_dir($beerxmlDir)) {
        echo "✓ Diretório de BeerXML encontrado: $beerxmlDir\n";
        
        // Listar arquivos XML no diretório
        $xmlFiles = glob("$beerxmlDir/*.xml");
        echo "✓ Encontrados " . count($xmlFiles) . " arquivos XML\n";
        
        if (!empty($xmlFiles)) {
            echo "\nArquivos encontrados:\n";
            foreach ($xmlFiles as $file) {
                echo "  - " . basename($file) . "\n";
            }
        }
    } else {
        echo "⚠ Diretório de BeerXML não encontrado: $beerxmlDir\n";
    }
    
    echo "\n=== Teste concluído ===\n";
    
} catch (Exception $e) {
    echo "✗ Erro durante o teste: " . $e->getMessage() . "\n";
    exit(1);
}
?>