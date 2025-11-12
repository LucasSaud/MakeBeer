<?php
/**
 * Traduções PT-BR
 * Sistema Atomos - Controle de Estoque de Cervejaria
 */

return [
    // Menu
    'menu' => [
        'dashboard' => 'Dashboard',
        'insumos' => 'Insumos',
        'entradas' => 'Entradas',
        'fornecedores' => 'Fornecedores',
        'receitas' => 'Receitas',
        'producao' => 'Produção',
        'envase' => 'Envase',
        'saida_barril' => 'Saída de Barril',
        'estoque' => 'Estoque',
        'produtos' => 'Produtos',
        'camarafria' => 'Câmara Fria',
        'relatorios' => 'Relatórios',
        'usuarios' => 'Usuários',
        'configuracoes' => 'Configurações',
    ],

    // Envase
    'envase' => [
        'titulo' => 'Envase de Barris',
        'novo' => 'Novo Envase',
        'editar' => 'Editar Envase',
        'detalhes' => 'Detalhes do Envase',
        'codigo' => 'Código',
        'lote' => 'Lote',
        'data_envase' => 'Data do Envase',
        'total_barris' => 'Total de Barris',
        'total_litros' => 'Total de Litros',
        'status' => 'Status',
        'responsavel' => 'Responsável',
        'observacoes' => 'Observações',
        'adicionar_barril' => 'Adicionar Barril',
        'numero_barril' => 'Número do Barril',
        'quantidade_litros' => 'Quantidade (Litros)',
        'finalizar' => 'Finalizar Envase',
        'cancelar' => 'Cancelar Envase',
    ],

    // Barril
    'barril' => [
        'codigo' => 'Código do Barril',
        'numero' => 'Número',
        'quantidade' => 'Quantidade',
        'data_envase' => 'Data do Envase',
        'status' => 'Status',
        'status_cheio' => 'Cheio',
        'status_vazio' => 'Vazio',
        'status_baixado' => 'Baixado',
    ],

    // Saída de Barril
    'saida_barril' => [
        'titulo' => 'Saída de Barril',
        'nova' => 'Nova Saída',
        'detalhes' => 'Detalhes da Saída',
        'data_saida' => 'Data da Saída',
        'destino' => 'Destino',
        'registrar' => 'Registrar Saída',
        'selecionar_barril' => 'Selecione o Barril',
    ],

    // Estoque
    'estoque' => [
        'titulo' => 'Estoque de Barris',
        'detalhes' => 'Detalhes do Estoque',
        'localizacao' => 'Localização',
        'temperatura' => 'Temperatura',
        'status_disponivel' => 'Disponível',
        'status_reservado' => 'Reservado',
        'status_em_processo' => 'Em Processo',
        'dar_baixa' => 'Dar Baixa',
        'atualizar_localizacao' => 'Atualizar Localização',
    ],

    // Status Gerais
    'status' => [
        'planejado' => 'Planejado',
        'em_processo' => 'Em Processo',
        'em_producao' => 'Em Produção',
        'fermentando' => 'Fermentando',
        'maturando' => 'Maturando',
        'finalizado' => 'Finalizado',
        'cancelado' => 'Cancelado',
    ],

    // Ações
    'acoes' => [
        'visualizar' => 'Visualizar',
        'editar' => 'Editar',
        'excluir' => 'Excluir',
        'salvar' => 'Salvar',
        'voltar' => 'Voltar',
        'cancelar' => 'Cancelar',
        'filtrar' => 'Filtrar',
        'buscar' => 'Buscar',
        'adicionar' => 'Adicionar',
        'remover' => 'Remover',
        'atualizar' => 'Atualizar',
        'finalizar' => 'Finalizar',
    ],

    // Mensagens
    'mensagens' => [
        'sucesso' => [
            'envase_criado' => 'Envase criado com sucesso',
            'envase_finalizado' => 'Envase finalizado com sucesso',
            'barril_adicionado' => 'Barril adicionado com sucesso',
            'saida_registrada' => 'Saída de barril registrada com sucesso',
            'localizacao_atualizada' => 'Localização atualizada com sucesso',
        ],
        'erro' => [
            'envase_nao_encontrado' => 'Envase não encontrado',
            'barril_nao_encontrado' => 'Barril não encontrado',
            'barril_ja_baixado' => 'Barril já teve saída registrada',
            'erro_criar_envase' => 'Erro ao criar envase',
            'erro_adicionar_barril' => 'Erro ao adicionar barril',
            'erro_registrar_saida' => 'Erro ao registrar saída',
        ],
    ],

    // Geral
    'geral' => [
        'nenhum_registro' => 'Nenhum registro encontrado',
        'selecione' => 'Selecione...',
        'todos' => 'Todos',
        'sim' => 'Sim',
        'nao' => 'Não',
    ],
];
?>
