<?php
require_once 'BaseController.php';
require_once 'app/models/Receita.php';
require_once 'app/models/Insumo.php';

class ImportController extends BaseController {
    
    private $receitaModel;
    private $insumoModel;
    
    public function __construct() {
        $this->receitaModel = new Receita();
        $this->insumoModel = new Insumo();
    }
    
    /**
     * Exibe a página de importação de receitas BeerXML
     */
    public function index() {
        // Verificar permissões (apenas administradores podem importar)
        $user = getCurrentUser();
        if ($user['perfil'] !== 'administrador') {
            setFlashMessage('error', 'Acesso negado. Apenas administradores podem importar receitas.');
            redirect('/receitas');
        }
        
        $this->view('import/index', [
            'pageTitle' => 'Importar Receitas - ' . APP_NAME,
            'activeMenu' => 'import'
        ]);
    }
    
    /**
     * Processa o upload e importação de arquivos BeerXML
     */
    public function upload() {
        // Verificar permissões
        $user = getCurrentUser();
        if ($user['perfil'] !== 'administrador') {
            setFlashMessage('error', 'Acesso negado. Apenas administradores podem importar receitas.');
            redirect('/receitas');
        }
        
        if (!$this->isPost()) {
            redirect('/import');
        }
        
        $results = [];
        
        try {
            // Verificar se foi enviado um diretório
            $directoryPath = $this->getPost('directory_path');
            if (!empty($directoryPath) && is_dir($directoryPath)) {
                $results = $this->importFromDirectory($directoryPath);
            } 
            // Verificar se foram enviados arquivos
            elseif (isset($_FILES['xml_files']) && !empty($_FILES['xml_files']['name'][0])) {
                $results = $this->importFromFiles($_FILES['xml_files']);
            } 
            else {
                throw new Exception('Nenhum arquivo ou diretório foi especificado para importação.');
            }
            
            $this->view('import/results', [
                'pageTitle' => 'Resultado da Importação - ' . APP_NAME,
                'activeMenu' => 'import',
                'results' => $results
            ]);
            
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro durante a importação: ' . $e->getMessage());
            redirect('/import');
        }
    }
    
    /**
     * Importa receitas de um diretório
     */
    private function importFromDirectory($directoryPath) {
        $results = [];
        
        // Verificar se o diretório existe
        if (!is_dir($directoryPath)) {
            throw new Exception("O diretório especificado não existe: $directoryPath");
        }
        
        // Buscar arquivos XML no diretório
        $files = glob($directoryPath . '/*.xml');
        
        if (empty($files)) {
            throw new Exception("Nenhum arquivo XML encontrado no diretório: $directoryPath");
        }
        
        foreach ($files as $file) {
            try {
                $result = $this->importRecipeFromFile($file);
                $results[] = $result;
            } catch (Exception $e) {
                $results[] = [
                    'file' => basename($file),
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Importa receitas de arquivos enviados
     */
    private function importFromFiles($files) {
        $results = [];
        
        // Verificar se há arquivos
        if (empty($files['name'])) {
            throw new Exception('Nenhum arquivo foi enviado.');
        }
        
        // Processar cada arquivo
        for ($i = 0; $i < count($files['name']); $i++) {
            // Verificar se houve erro no upload
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $results[] = [
                    'file' => $files['name'][$i],
                    'status' => 'error',
                    'message' => 'Erro no upload do arquivo: ' . $this->getUploadErrorMessage($files['error'][$i])
                ];
                continue;
            }
            
            // Verificar extensão do arquivo
            $extension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if ($extension !== 'xml') {
                $results[] = [
                    'file' => $files['name'][$i],
                    'status' => 'error',
                    'message' => 'Arquivo inválido. Apenas arquivos XML são permitidos.'
                ];
                continue;
            }
            
            try {
                // Criar um arquivo temporário para processar
                $tempFile = tempnam(sys_get_temp_dir(), 'beerxml_');
                move_uploaded_file($files['tmp_name'][$i], $tempFile);
                
                $result = $this->importRecipeFromFile($tempFile, $files['name'][$i]);
                $results[] = $result;
                
                // Remover arquivo temporário
                unlink($tempFile);
            } catch (Exception $e) {
                $results[] = [
                    'file' => $files['name'][$i],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Importa uma receita de um arquivo BeerXML
     */
    private function importRecipeFromFile($filePath, $fileName = null) {
        // Se não foi fornecido um nome de arquivo, usar o basename do caminho
        if ($fileName === null) {
            $fileName = basename($filePath);
        }
        
        // Carregar e parsear o arquivo XML
        $xml = simplexml_load_file($filePath);
        
        if (!$xml) {
            throw new Exception("Falha ao carregar o arquivo XML: $fileName");
        }
        
        // Verificar se é um arquivo BeerXML válido
        if (!isset($xml->RECIPE)) {
            throw new Exception("Arquivo XML não contém uma receita BeerXML válida: $fileName");
        }
        
        $importedRecipes = [];
        
        // Processar cada receita no arquivo
        foreach ($xml->RECIPE as $recipe) {
            try {
                $recipeData = $this->processRecipe($recipe);
                $importedRecipes[] = $recipeData;
            } catch (Exception $e) {
                throw new Exception("Erro ao importar receita do arquivo $fileName: " . $e->getMessage());
            }
        }
        
        return [
            'file' => $fileName,
            'status' => 'success',
            'message' => 'Importação concluída com sucesso',
            'recipes' => $importedRecipes
        ];
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
            'ativo' => 1,
            'criado_por' => getCurrentUser()['id']
        ];
        
        // Criar ou atualizar a receita
        $recipeId = $this->createOrUpdateRecipe($recipeData);
        
        // Processar ingredientes
        $ingredients = [];
        $ingredients = array_merge($ingredients, $this->processFermentables($recipeId, $recipe));
        $ingredients = array_merge($ingredients, $this->processHops($recipeId, $recipe));
        $ingredients = array_merge($ingredients, $this->processYeasts($recipeId, $recipe));
        $ingredients = array_merge($ingredients, $this->processMiscs($recipeId, $recipe));
        
        return [
            'id' => $recipeId,
            'nome' => $recipeData['nome'],
            'estilo' => $recipeData['estilo'],
            'ingredientes' => count($ingredients)
        ];
    }
    
    /**
     * Cria ou atualiza uma receita no banco de dados
     */
    private function createOrUpdateRecipe($recipeData) {
        // Verificar se a receita já existe
        $existingRecipe = $this->receitaModel->whereFirst('nome', $recipeData['nome']);
        
        if ($existingRecipe) {
            // Atualizar receita existente
            $this->receitaModel->update($existingRecipe['id'], $recipeData);
            return $existingRecipe['id'];
        } else {
            // Criar nova receita
            return $this->receitaModel->create($recipeData);
        }
    }
    
    /**
     * Processa maltes e fermentáveis
     */
    private function processFermentables($recipeId, $recipe) {
        $ingredients = [];
        
        if (!isset($recipe->FERMENTABLES->FERMENTABLE)) {
            return $ingredients;
        }
        
        foreach ($recipe->FERMENTABLES->FERMENTABLE as $fermentable) {
            // Criar ou atualizar insumo
            $insumoData = [
                'nome' => (string)$fermentable->NAME,
                'descricao' => $this->buildFermentableDescription($fermentable),
                'tipo' => 'malte',
                'unidade_medida' => 'kg',
                'ativo' => 1,
                'origem' => isset($fermentable->ORIGIN) ? (string)$fermentable->ORIGIN : null,
                'fornecedor_principal_id' => null,
                'cor' => isset($fermentable->COLOR) ? (float)$fermentable->COLOR : null,
                'rendimento' => isset($fermentable->YIELD) ? (float)$fermentable->YIELD : null
            ];
            
            // Adicionar fornecedor se especificado
            if (isset($fermentable->SUPPLIER)) {
                $supplierName = (string)$fermentable->SUPPLIER;
                $supplier = $this->getOrCreateSupplier($supplierName);
                if ($supplier) {
                    $insumoData['fornecedor_principal_id'] = $supplier['id'];
                }
            }
            
            $insumoId = $this->createOrUpdateInput($insumoData);
            
            // Adicionar ingrediente à receita
            $ingredientId = $this->addIngredientToRecipe($recipeId, $insumoId, [
                'quantidade' => (float)$fermentable->AMOUNT,
                'unidade' => 'kg',
                'fase' => 'mostura',
                'tempo_adicao' => 0
            ]);
            
            $ingredients[] = [
                'id' => $ingredientId,
                'nome' => (string)$fermentable->NAME,
                'tipo' => 'malte',
                'quantidade' => (float)$fermentable->AMOUNT
            ];
        }
        
        return $ingredients;
    }
    
    /**
     * Processa lúpulos
     */
    private function processHops($recipeId, $recipe) {
        $ingredients = [];
        
        if (!isset($recipe->HOPS->HOP)) {
            return $ingredients;
        }
        
        foreach ($recipe->HOPS->HOP as $hop) {
            // Criar ou atualizar insumo
            $insumoData = [
                'nome' => (string)$hop->NAME,
                'descricao' => $this->buildHopDescription($hop),
                'tipo' => 'lupulo',
                'unidade_medida' => 'kg',
                'ativo' => 1,
                'alfa_acidos' => isset($hop->ALPHA) ? (float)$hop->ALPHA : null,
                'forma' => isset($hop->FORM) ? $this->mapHopForm((string)$hop->FORM) : null
            ];
            
            $insumoId = $this->createOrUpdateInput($insumoData);
            
            // Determinar fase e tempo com base no uso
            $fase = $this->determineHopPhase((string)$hop->USE);
            $tempo = (int)$hop->TIME;
            
            // Adicionar ingrediente à receita
            $ingredientId = $this->addIngredientToRecipe($recipeId, $insumoId, [
                'quantidade' => (float)$hop->AMOUNT,
                'unidade' => 'kg',
                'fase' => $fase,
                'tempo_adicao' => $tempo
            ]);
            
            $ingredients[] = [
                'id' => $ingredientId,
                'nome' => (string)$hop->NAME,
                'tipo' => 'lupulo',
                'quantidade' => (float)$hop->AMOUNT
            ];
        }
        
        return $ingredients;
    }
    
    /**
     * Processa leveduras
     */
    private function processYeasts($recipeId, $recipe) {
        $ingredients = [];
        
        if (!isset($recipe->YEASTS->YEAST)) {
            return $ingredients;
        }
        
        foreach ($recipe->YEASTS->YEAST as $yeast) {
            // Criar ou atualizar insumo
            $insumoData = [
                'nome' => (string)$yeast->NAME,
                'descricao' => $this->buildYeastDescription($yeast),
                'tipo' => 'levedura',
                'unidade_medida' => 'un',
                'ativo' => 1,
                'laboratorio' => isset($yeast->LABORATORY) ? (string)$yeast->LABORATORY : null,
                'produto_id' => isset($yeast->PRODUCT_ID) ? (string)$yeast->PRODUCT_ID : null,
                'atenuacao' => isset($yeast->ATTENUATION) ? (float)$yeast->ATTENUATION : null,
                'temperatura_minima' => isset($yeast->MIN_TEMPERATURE) ? (float)$yeast->MIN_TEMPERATURE : null,
                'temperatura_maxima' => isset($yeast->MAX_TEMPERATURE) ? (float)$yeast->MAX_TEMPERATURE : null
            ];
            
            $insumoId = $this->createOrUpdateInput($insumoData);
            
            // Adicionar ingrediente à receita
            $ingredientId = $this->addIngredientToRecipe($recipeId, $insumoId, [
                'quantidade' => (float)$yeast->AMOUNT,
                'unidade' => $this->determineYeastUnit($yeast),
                'fase' => 'fermentacao',
                'tempo_adicao' => 0
            ]);
            
            $ingredients[] = [
                'id' => $ingredientId,
                'nome' => (string)$yeast->NAME,
                'tipo' => 'levedura',
                'quantidade' => (float)$yeast->AMOUNT
            ];
        }
        
        return $ingredients;
    }
    
    /**
     * Processa ingredientes diversos
     */
    private function processMiscs($recipeId, $recipe) {
        $ingredients = [];
        
        if (!isset($recipe->MISCS->MISC)) {
            return $ingredients;
        }
        
        foreach ($recipe->MISCS->MISC as $misc) {
            // Criar ou atualizar insumo
            $insumoData = [
                'nome' => (string)$misc->NAME,
                'descricao' => (string)$misc->NOTES,
                'tipo' => $this->determineMiscType($misc),
                'unidade_medida' => $this->determineMiscUnit($misc),
                'ativo' => 1
            ];
            
            $insumoId = $this->createOrUpdateInput($insumoData);
            
            // Determinar fase com base no uso
            $fase = $this->determineMiscPhase((string)$misc->USE);
            
            // Adicionar ingrediente à receita
            $ingredientId = $this->addIngredientToRecipe($recipeId, $insumoId, [
                'quantidade' => (float)$misc->AMOUNT,
                'unidade' => $this->determineMiscUnit($misc),
                'fase' => $fase,
                'tempo_adicao' => (int)$misc->TIME
            ]);
            
            $ingredients[] = [
                'id' => $ingredientId,
                'nome' => (string)$misc->NAME,
                'tipo' => $this->determineMiscType($misc),
                'quantidade' => (float)$misc->AMOUNT
            ];
        }
        
        return $ingredients;
    }
    
    /**
     * Cria ou atualiza um insumo no banco de dados
     */
    private function createOrUpdateInput($inputData) {
        // Verificar se o insumo já existe
        $existingInput = $this->insumoModel->whereFirst('nome', $inputData['nome']);
        
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
        $existingIngredient = null;
        try {
            $db = Database::getInstance();
            $stmt = $db->query(
                "SELECT id FROM receita_ingredientes 
                 WHERE receita_id = ? AND insumo_id = ? AND fase = ? AND tempo_adicao = ?",
                [$recipeId, $insumoId, $ingredientData['fase'], $ingredientData['tempo_adicao']]
            );
            $existingIngredient = $stmt->fetch();
        } catch (Exception $e) {
            // Se houver erro na consulta, continuar normalmente
        }
        
        if ($existingIngredient) {
            // Atualizar quantidade do ingrediente
            $db = Database::getInstance();
            $db->query(
                "UPDATE receita_ingredientes 
                 SET quantidade = ?, unidade = ?, updated_at = NOW() 
                 WHERE id = ?",
                [$ingredientData['quantidade'], $ingredientData['unidade'], $existingIngredient['id']]
            );
            return $existingIngredient['id'];
        } else {
            // Adicionar novo ingrediente à receita
            $ingredientData['receita_id'] = $recipeId;
            $ingredientData['insumo_id'] = $insumoId;
            
            return $this->receitaModel->adicionarIngrediente($recipeId, $ingredientData);
        }
    }
    
    /**
     * Obtém ou cria um fornecedor
     */
    private function getOrCreateSupplier($supplierName) {
        // Verificar se o fornecedor já existe
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM fornecedores WHERE nome = ?", [$supplierName]);
        $supplier = $stmt->fetch();
        
        if ($supplier) {
            return $supplier;
        }
        
        // Criar novo fornecedor
        $supplierData = [
            'nome' => $supplierName,
            'ativo' => 1
        ];
        
        $supplierId = $db->insert(
            "INSERT INTO fornecedores (nome, ativo, created_at, updated_at) VALUES (?, ?, NOW(), NOW())",
            [$supplierData['nome'], $supplierData['ativo']]
        );
        
        $supplierData['id'] = $supplierId;
        return $supplierData;
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
     * Mapeia a forma do lúpulo
     */
    private function mapHopForm($form) {
        $form = strtolower($form);
        switch ($form) {
            case 'pellet':
                return 'pellet';
            case 'plug':
                return 'plug';
            case 'leaf':
                return 'leaf';
            case 'extract':
                return 'extract';
            default:
                return null;
        }
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
    
    /**
     * Obtém mensagem de erro de upload
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return 'O arquivo excede o tamanho máximo permitido pelo servidor.';
            case UPLOAD_ERR_FORM_SIZE:
                return 'O arquivo excede o tamanho máximo permitido pelo formulário.';
            case UPLOAD_ERR_PARTIAL:
                return 'O arquivo foi enviado apenas parcialmente.';
            case UPLOAD_ERR_NO_FILE:
                return 'Nenhum arquivo foi enviado.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Diretório temporário ausente.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Falha ao escrever o arquivo no disco.';
            case UPLOAD_ERR_EXTENSION:
                return 'Uma extensão PHP impediu o envio do arquivo.';
            default:
                return 'Erro desconhecido no upload.';
        }
    }
}
?>