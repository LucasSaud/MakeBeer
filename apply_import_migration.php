<?php
/**
 * Script para aplicar a migração de importação de receitas BeerXML
 * 
 * Este script aplica as alterações necessárias no banco de dados
 * para suportar a funcionalidade de importação de receitas BeerXML.
 */

// Configurações
require_once 'app/config/database.php';

echo "=== Aplicando Migração para Importação de Receitas BeerXML ===\n\n";

try {
    // Obter conexão com o banco de dados
    $db = Database::getInstance()->getConnection();
    
    echo "✓ Conexão com o banco de dados estabelecida\n";
    
    // Verificar se o banco de dados existe
    $stmt = $db->query("SHOW DATABASES LIKE 'atomos_cervejaria'");
    if ($stmt->rowCount() == 0) {
        echo "⚠ Banco de dados 'atomos_cervejaria' não encontrado\n";
        echo "  Execute primeiro o script de atualização do sistema\n";
        exit(1);
    }
    
    echo "✓ Banco de dados 'atomos_cervejaria' encontrado\n";
    
    // Selecionar o banco de dados
    $db->query("USE atomos_cervejaria");
    
    // Verificar se as tabelas principais existem
    $requiredTables = ['insumos', 'receitas', 'receita_ingredientes'];
    foreach ($requiredTables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() == 0) {
            echo "✗ Tabela '$table' não encontrada\n";
            exit(1);
        }
    }
    
    echo "✓ Tabelas principais verificadas\n";
    
    // Ler o arquivo de migração
    $migrationFile = 'database/migrations/002_add_import_columns.sql';
    if (!file_exists($migrationFile)) {
        echo "✗ Arquivo de migração não encontrado: $migrationFile\n";
        exit(1);
    }
    
    echo "✓ Arquivo de migração encontrado: $migrationFile\n";
    
    // Ler o conteúdo do arquivo
    $sql = file_get_contents($migrationFile);
    
    // Remover comentários e linhas vazias
    $sql = preg_replace('/--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $sql = preg_replace('/^\s*[\r\n]/m', '', $sql);
    
    // Dividir em comandos individuais
    $commands = explode(';', $sql);
    
    $successCount = 0;
    $errorCount = 0;
    
    echo "\n=== Executando Comandos de Migração ===\n";
    
    foreach ($commands as $command) {
        $command = trim($command);
        if (empty($command)) {
            continue;
        }
        
        try {
            $db->exec($command);
            $successCount++;
            echo "✓ Comando executado com sucesso\n";
        } catch (PDOException $e) {
            // Ignorar erros de colunas já existentes
            if (strpos($e->getMessage(), 'Duplicate column name') !== false ||
                strpos($e->getMessage(), 'already exists') !== false) {
                echo "ℹ️  Coluna ou índice já existe (ignorado)\n";
                $successCount++;
            } else {
                echo "✗ Erro ao executar comando: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
    echo "\n=== Resultado da Migração ===\n";
    echo "✓ Comandos executados com sucesso: $successCount\n";
    echo "✗ Comandos com erro: $errorCount\n";
    
    if ($errorCount == 0) {
        echo "\n🎉 Migração aplicada com sucesso!\n";
        echo "O sistema agora está pronto para importar receitas BeerXML.\n";
    } else {
        echo "\n⚠️  Migração concluída com alguns erros.\n";
        echo "Verifique as mensagens acima para mais detalhes.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Erro durante a migração: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Fim da Migração ===\n";
?>