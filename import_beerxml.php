<?php
/**
 * Script para importação de receitas BeerXML para o sistema Atomos
 * 
 * Este script faz o parsing de arquivos BeerXML e importa as receitas
 * para o banco de dados do sistema de gestão cervejeira Atomos.
 */

// Incluir configurações e modelos
require_once 'app/config/database.php';
require_once 'app/models/Receita.php';
require_once 'app/models/Insumo.php';

class BeerXMLImporter {
    private $db;
    private $receitaModel;
    private $insumoModel;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->receitaModel = new Receita();
        $this->insumoModel = new Insumo();
    }
    
    /**
     * Importa uma receita BeerXML para o banco de dados
     */
    public function importRecipe($xmlFilePath) {
        // Carregar e parsear o arquivo XML
        $xml = simplexml_load_file($xmlFilePath);
        
        if (!$xml) {
            throw new Exception("Falha ao carregar o arquivo XML: $xmlFilePath");
        }
        
        // Verificar se é um arquivo BeerXML válido
        if (!isset($xml->RECIPE)) {
            throw new Exception("Arquivo XML não contém uma receita BeerXML válida");
        }
        
        // Iniciar transação
        $this->db->beginTransaction();
        
        try {
            // Processar cada receita no arquivo
            foreach ($xml->RECIPE as $recipe) {
                $this->processRecipe($recipe);
            }
            
            // Commit da transação
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Rollback em caso de erro
            $this->db->rollback();
            throw $e;
        }
    }
    
    /**
     * Processa uma receita individual
     */
    private function processRecipe($recipe) {
        // Extrair informações básicas da receita
        $recipeData = [
            'nome' => (string)$recipe->NAME,
            'estilo' => isset($recipe->STYLE->NAME) ? (string)$recipe->STYLE->NAME : 'Não especificado',
            'descricao' => (string)$recipe->NOTES,
            'volume_batch' => (float)$recipe->BATCH_SIZE,
            'densidade_inicial' => (float)$recipe->OG,
            'densidade_final' => (float)$recipe->FG,
            'ibu' => (float)$recipe->IBU,
            'srm' => isset($recipe->EST_COLOR) ? $this->extractSRM((string)$recipe->EST_COLOR) : null,
            'abv' => isset($recipe->ABV) ? $this->extractABV((string)$recipe->ABV) : null,
            'tempo_fermentacao' => isset($recipe->PRIMARY_AGE) ? (int)$recipe->PRIMARY_AGE : null,
            'temperatura_fermentacao' => isset($recipe->PRIMARY_TEMP) ? (float)$recipe->PRIMARY_TEMP : null,
            'ativo' => 1
        ];
        
        // Criar ou atualizar a receita
        $recipeId = $this->createOrUpdateRecipe($recipeData);
        
        // Processar ingredientes
        $this->processFermentables($recipeId, $recipe);
        $this->processHops($recipeId, $recipe);
        $this->processYeasts($recipeId, $recipe);
        $this->processMiscs($recipeId, $recipe);
        
        echo "Receita '{$recipeData['nome']}' importada com sucesso (ID: $recipeId)\n";
    }
    
    /**
     * Cria ou atualiza uma receita no banco de dados
     */
    private function createOrUpdateRecipe($recipeData) {
        // Verificar se a receita já existe
        $stmt = $this->db->prepare("SELECT id FROM receitas WHERE nome = ?");
        $stmt->execute([$recipeData['nome']]);
        $existingRecipe = $stmt->fetch();
        
        if ($existingRecipe) {
            // Atualizar receita existente
            $recipeData['updated_at'] = date('Y-m-d H:i:s');
            $this->receitaModel->update($existingRecipe['id'], $recipeData);
            return $existingRecipe['id'];
        } else {
            // Criar nova receita
            $recipeData['criado_por'] = 1; // Usuário admin por padrão
            return $this->receitaModel->create($recipeData);
        }
    }
    
    /**
     * Processa maltes e fermentáveis
     */
    private function processFermentables($recipeId, $recipe) {
        if (!isset($recipe->FERMENTABLES->FERMENTABLE)) {
            return;
        }
        
        foreach ($recipe->FERMENTABLES->FERMENTABLE as $fermentable) {
            // Criar ou atualizar insumo
            $insumoId = $this->createOrUpdateInput([
                'nome' => (string)$fermentable->NAME,
                'descricao' => $this->buildFermentableDescription($fermentable),
                'tipo' => 'malte',
                'unidade_medida' => 'kg',
                'ativo' => 1
            ]);
            
            // Adicionar ingrediente à receita
            $this->addIngredientToRecipe($recipeId, $insumoId, [
                'quantidade' => (float)$fermentable->AMOUNT,
                'unidade' => 'kg',
                'fase' => 'mostura',
                'tempo_adicao' => 0
            ]);
        }
    }
    
    /**
     * Processa lúpulos
     */
    private function processHops($recipeId, $recipe) {
        if (!isset($recipe->HOPS->HOP)) {
            return;
        }
        
        foreach ($recipe->HOPS->HOP as $hop) {
            // Criar ou atualizar insumo
            $insumoId = $this->createOrUpdateInput([
                'nome' => (string)$hop->NAME,
                'descricao' => $this->buildHopDescription($hop),
                'tipo' => 'lupulo',
                'unidade_medida' => 'kg',
                'ativo' => 1
            ]);
            
            // Determinar fase e tempo com base no uso
            $fase = $this->determineHopPhase((string)$hop->USE);
            $tempo = (int)$hop->TIME;
            
            // Adicionar ingrediente à receita
            $this->addIngredientToRecipe($recipeId, $insumoId, [
                'quantidade' => (float)$hop->AMOUNT,
                'unidade' => 'kg',
                'fase' => $fase,
                'tempo_adicao' => $tempo
            ]);
        }
    }
    
    /**
     * Processa leveduras
     */
    private function processYeasts($recipeId, $recipe) {
        if (!isset($recipe->YEASTS->YEAST)) {
            return;
        }
        
        foreach ($recipe->YEASTS->YEAST as $yeast) {
            // Criar ou atualizar insumo
            $insumoId = $this->createOrUpdateInput([
                'nome' => (string)$yeast->NAME,
                'descricao' => $this->buildYeastDescription($yeast),
                'tipo' => 'levedura',
                'unidade_medida' => 'un',
                'ativo' => 1
            ]);
            
            // Adicionar ingrediente à receita
            $this->addIngredientToRecipe($recipeId, $insumoId, [
                'quantidade' => (float)$yeast->AMOUNT,
                'unidade' => $this->determineYeastUnit($yeast),
                'fase' => 'fermentacao',
                'tempo_adicao' => 0
            ]);
        }
    }
    
    /**
     * Processa ingredientes diversos
     */
    private function processMiscs($recipeId, $recipe) {
        if (!isset($recipe->MISCS->MISC)) {
            return;
        }
        
        foreach ($recipe->MISCS->MISC as $misc) {
            // Criar ou atualizar insumo
            $insumoId = $this->createOrUpdateInput([
                'nome' => (string)$misc->NAME,
                'descricao' => (string)$misc->NOTES,
                'tipo' => $this->determineMiscType($misc),
                'unidade_medida' => $this->determineMiscUnit($misc),
                'ativo' => 1
            ]);
            
            // Determinar fase com base no uso
            $fase = $this->determineMiscPhase((string)$misc->USE);
            
            // Adicionar ingrediente à receita
            $this->addIngredientToRecipe($recipeId, $insumoId, [
                'quantidade' => (float)$misc->AMOUNT,
                'unidade' => $this->determineMiscUnit($misc),
                'fase' => $fase,
                'tempo_adicao' => (int)$misc->TIME
            ]);
        }
    }
    
    /**
     * Cria ou atualiza um insumo no banco de dados
     */
    private function createOrUpdateInput($inputData) {
        // Verificar se o insumo já existe
        $stmt = $this->db->prepare("SELECT id FROM insumos WHERE nome = ?");
        $stmt->execute([$inputData['nome']]);
        $existingInput = $stmt->fetch();
        
        if ($existingInput) {
            // Atualizar insumo existente
            $this->insumoModel->update($existingInput['id'], $inputData);
            return $existingInput['id'];
        } else {
            // Criar novo insumo
            return $this->insumoModel->create($inputData);
        }
    }
    
    /**
     * Adiciona um ingrediente a uma receita
     */
    private function addIngredientToRecipe($recipeId, $insumoId, $ingredientData) {
        // Verificar se o ingrediente já está associado à receita
        $stmt = $this->db->prepare(
            "SELECT id FROM receita_ingredientes 
             WHERE receita_id = ? AND insumo_id = ? AND fase = ? AND tempo_adicao = ?"
        );
        $stmt->execute([
            $recipeId, 
            $insumoId, 
            $ingredientData['fase'], 
            $ingredientData['tempo_adicao']
        ]);
        $existingIngredient = $stmt->fetch();
        
        if ($existingIngredient) {
            // Atualizar quantidade do ingrediente
            $sql = "UPDATE receita_ingredientes 
                    SET quantidade = ?, unidade = ?, updated_at = NOW() 
                    WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $ingredientData['quantidade'],
                $ingredientData['unidade'],
                $existingIngredient['id']
            ]);
        } else {
            // Adicionar novo ingrediente à receita
            $ingredientData['receita_id'] = $recipeId;
            $ingredientData['insumo_id'] = $insumoId;
            
            $sql = "INSERT INTO receita_ingredientes 
                    (receita_id, insumo_id, quantidade, unidade, fase, tempo_adicao) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $ingredientData['receita_id'],
                $ingredientData['insumo_id'],
                $ingredientData['quantidade'],
                $ingredientData['unidade'],
                $ingredientData['fase'],
                $ingredientData['tempo_adicao']
            ]);
        }
    }
    
    /**
     * Constrói descrição para fermentável
     */
    private function buildFermentableDescription($fermentable) {
        $description = "";
        
        if (isset($fermentable->SUPPLIER)) {
            $description .= "Fornecedor: " . (string)$fermentable->SUPPLIER . ". ";
        }
        
        if (isset($fermentable->ORIGIN)) {
            $description .= "Origem: " . (string)$fermentable->ORIGIN . ". ";
        }
        
        if (isset($fermentable->YIELD)) {
            $description .= "Rendimento: " . (string)$fermentable->YIELD . "%. ";
        }
        
        if (isset($fermentable->COLOR)) {
            $description .= "Cor: " . (string)$fermentable->COLOR . " SRM. ";
        }
        
        return trim($description);
    }
    
    /**
     * Constrói descrição para lúpulo
     */
    private function buildHopDescription($hop) {
        $description = "";
        
        if (isset($hop->ALPHA)) {
            $description .= "Alfa-ácidos: " . (string)$hop->ALPHA . "%. ";
        }
        
        if (isset($hop->FORM)) {
            $description .= "Forma: " . (string)$hop->FORM . ". ";
        }
        
        return trim($description);
    }
    
    /**
     * Constrói descrição para levedura
     */
    private function buildYeastDescription($yeast) {
        $description = "";
        
        if (isset($yeast->LABORATORY)) {
            $description .= "Laboratório: " . (string)$yeast->LABORATORY . ". ";
        }
        
        if (isset($yeast->PRODUCT_ID)) {
            $description .= "Produto: " . (string)$yeast->PRODUCT_ID . ". ";
        }
        
        if (isset($yeast->ATTENUATION)) {
            $description .= "Atenuação: " . (string)$yeast->ATTENUATION . "%. ";
        }
        
        if (isset($yeast->MIN_TEMPERATURE) && isset($yeast->MAX_TEMPERATURE)) {
            $description .= "Temperatura: " . (string)$yeast->MIN_TEMPERATURE . 
                           "°C - " . (string)$yeast->MAX_TEMPERATURE . "°C. ";
        }
        
        return trim($description);
    }
    
    /**
     * Determina a fase de adição do lúpulo
     */
    private function determineHopPhase($use) {
        switch (strtolower($use)) {
            case 'first wort':
            case 'boil':
                return 'fervura';
            case 'aroma':
            case 'flameout':
                return 'fervura';
            case 'dry hop':
                return 'fermentacao';
            case 'mash':
                return 'mostura';
            default:
                return 'fervura';
        }
    }
    
    /**
     * Determina a unidade de medida para levedura
     */
    private function determineYeastUnit($yeast) {
        if (isset($yeast->DISPLAY_AMOUNT)) {
            $display = (string)$yeast->DISPLAY_AMOUNT;
            if (strpos($display, 'ml') !== false) {
                return 'ml';
            } elseif (strpos($display, 'pkg') !== false) {
                return 'un';
            } elseif (strpos($display, 'g') !== false) {
                return 'g';
            }
        }
        return 'un';
    }
    
    /**
     * Determina o tipo de ingrediente diverso
     */
    private function determineMiscType($misc) {
        if (isset($misc->TYPE)) {
            $type = strtolower((string)$misc->TYPE);
            switch ($type) {
                case 'spice':
                    return 'aditivo';
                case 'fining':
                    return 'aditivo';
                case 'water agent':
                    return 'aditivo';
                case 'herb':
                    return 'aditivo';
                case 'flavor':
                    return 'adjunto';
                case 'other':
                    return 'outros';
                default:
                    return 'aditivo';
            }
        }
        return 'aditivo';
    }
    
    /**
     * Determina a unidade de medida para ingrediente diverso
     */
    private function determineMiscUnit($misc) {
        if (isset($misc->AMOUNT_IS_WEIGHT)) {
            if ((string)$misc->AMOUNT_IS_WEIGHT === 'true') {
                return 'kg';
            }
        }
        
        if (isset($misc->DISPLAY_AMOUNT)) {
            $display = (string)$misc->DISPLAY_AMOUNT;
            if (strpos($display, 'kg') !== false) {
                return 'kg';
            } elseif (strpos($display, 'g') !== false) {
                return 'g';
            } elseif (strpos($display, 'ml') !== false) {
                return 'ml';
            } elseif (strpos($display, 'l') !== false) {
                return 'l';
            }
        }
        
        return 'kg';
    }
    
    /**
     * Determina a fase de adição para ingrediente diverso
     */
    private function determineMiscPhase($use) {
        switch (strtolower($use)) {
            case 'mash':
                return 'mostura';
            case 'boil':
                return 'fervura';
            case 'primary':
            case 'secondary':
            case 'tertiary':
                return 'fermentacao';
            case 'bottling':
            case 'keg':
            case 'sparge':
                return 'envase';
            default:
                return 'fermentacao';
        }
    }
    
    /**
     * Extrai valor SRM de string
     */
    private function extractSRM($srmString) {
        // Remover unidade "SRM" se presente
        $srm = preg_replace('/[^0-9.]/', '', $srmString);
        return is_numeric($srm) ? (float)$srm : null;
    }
    
    /**
     * Extrai valor ABV de string
     */
    private function extractABV($abvString) {
        // Remover unidade "%" se presente
        $abv = preg_replace('/[^0-9.]/', '', $abvString);
        return is_numeric($abv) ? (float)$abv : null;
    }
}

// Exemplo de uso
/*
try {
    $importer = new BeerXMLImporter();
    
    // Importar uma receita específica
    $importer->importRecipe('beerxml/f_mIP1gcsGfXWmu9RU4Mp2BTmzlsI3_Brewfather_BeerXML_AtomosSession_20251123.xml');
    
    echo "Importação concluída com sucesso!\n";
} catch (Exception $e) {
    echo "Erro durante a importação: " . $e->getMessage() . "\n";
}
*/
?>