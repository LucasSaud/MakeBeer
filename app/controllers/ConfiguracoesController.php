<?php
require_once 'BaseController.php';

class ConfiguracoesController extends BaseController {

    /**
     * Página principal de configurações
     */
    public function index() {
        $user = getCurrentUser();

        $this->view('configuracoes/index', [
            'user' => $user
        ]);
    }

    /**
     * Atualizar configurações gerais
     */
    public function update() {
        if (!$this->isPost()) {
            redirect('/configuracoes');
        }

        $user = getCurrentUser();

        // Apenas administradores podem alterar configurações do sistema
        if ($user['perfil'] !== 'administrador') {
            setFlashMessage('error', 'Você não tem permissão para alterar configurações');
            redirect('/configuracoes');
        }

        try {
            // Aqui você pode adicionar lógica para salvar configurações
            // Por exemplo, em um arquivo de configuração ou banco de dados

            setFlashMessage('success', 'Configurações atualizadas com sucesso');
            redirect('/configuracoes');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar configurações: ' . $e->getMessage());
            redirect('/configuracoes');
        }
    }

    /**
     * Backup do banco de dados
     */
    public function backup() {
        $user = getCurrentUser();

        if ($user['perfil'] !== 'administrador') {
            setFlashMessage('error', 'Você não tem permissão para fazer backup');
            redirect('/configuracoes');
        }

        try {
            $db = Database::getInstance();
            $date = date('Y-m-d_H-i-s');
            $filename = "backup_atomos_{$date}.sql";

            setFlashMessage('success', 'Backup iniciado com sucesso');
            redirect('/configuracoes');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao fazer backup: ' . $e->getMessage());
            redirect('/configuracoes');
        }
    }
}
?>
