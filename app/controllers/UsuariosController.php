<?php
require_once 'BaseController.php';

class UsuariosController extends BaseController {

    private $model;

    public function __construct() {
        $this->model = new User();
    }

    /**
     * Lista usuários
     */
    public function index() {
        // Apenas administradores podem gerenciar usuários
        $user = getCurrentUser();
        if ($user['perfil'] !== 'administrador') {
            setFlashMessage('error', 'Acesso negado');
            redirect('/dashboard');
        }

        $filters = [
            'nome' => $this->getGet('nome'),
            'perfil' => $this->getGet('perfil'),
            'ativo' => $this->getGet('ativo')
        ];

        // Por padrão, mostrar apenas usuários ativos
        if (empty(array_filter($filters))) {
            $filters['ativo'] = '1';
        }

        $usuarios = $this->model->search($filters);

        $this->view('usuarios/index', ['usuarios' => $usuarios, 'filters' => $filters]);
    }

    /**
     * Detalhes do usuário
     */
    public function viewUsuario() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Usuário não encontrado');
            redirect('/usuarios');
        }

        $usuario = $this->model->find($id);

        if (!$usuario) {
            setFlashMessage('error', 'Usuário não encontrado');
            redirect('/usuarios');
        }

        unset($usuario['senha']);

        $this->view('usuarios/view', ['usuario' => $usuario]);
    }

    /**
     * Formulário novo usuário
     */
    public function create() {
        $user = getCurrentUser();
        if ($user['perfil'] !== 'administrador') {
            setFlashMessage('error', 'Acesso negado');
            redirect('/dashboard');
        }

        $this->view('usuarios/form', ['usuario' => null]);
    }

    /**
     * Salva novo usuário
     */
    public function store() {
        if (!$this->isPost()) {
            redirect('/usuarios');
        }

        $user = getCurrentUser();
        if ($user['perfil'] !== 'administrador') {
            setFlashMessage('error', 'Acesso negado');
            redirect('/dashboard');
        }

        $data = [
            'nome' => $this->getPost('nome'),
            'email' => $this->getPost('email'),
            'senha' => $this->getPost('senha'),
            'perfil' => $this->getPost('perfil'),
            'ativo' => 1
        ];

        try {
            $id = $this->model->createUser($data);
            logActivity('usuario_criado', "Usuário criado: {$data['nome']}");
            setFlashMessage('success', 'Usuário criado com sucesso');
            redirect('/usuarios/viewUsuario?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao criar usuário: ' . $e->getMessage());
            redirect('/usuarios/create');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit() {
        $id = $this->getGet('id');

        if (!$id) {
            setFlashMessage('error', 'Usuário não encontrado');
            redirect('/usuarios');
        }

        $user = getCurrentUser();
        // Pode editar próprio perfil ou ser administrador
        if ($user['perfil'] !== 'administrador' && $user['id'] != $id) {
            setFlashMessage('error', 'Acesso negado');
            redirect('/dashboard');
        }

        $usuario = $this->model->find($id);

        if (!$usuario) {
            setFlashMessage('error', 'Usuário não encontrado');
            redirect('/usuarios');
        }

        $this->view('usuarios/form', ['usuario' => $usuario]);
    }

    /**
     * Atualiza usuário
     */
    public function update() {
        if (!$this->isPost()) {
            redirect('/usuarios');
        }

        $id = $this->getPost('id');

        $user = getCurrentUser();
        if ($user['perfil'] !== 'administrador' && $user['id'] != $id) {
            setFlashMessage('error', 'Acesso negado');
            redirect('/dashboard');
        }

        $data = [
            'nome' => $this->getPost('nome'),
            'email' => $this->getPost('email')
        ];

        // Apenas admin pode alterar perfil
        if ($user['perfil'] === 'administrador') {
            $data['perfil'] = $this->getPost('perfil');
        }

        // Atualizar senha se fornecida
        $novaSenha = $this->getPost('senha');
        if (!empty($novaSenha)) {
            $data['senha'] = hashPassword($novaSenha);
        }

        try {
            $this->model->update($id, $data);
            logActivity('usuario_atualizado', "Usuário atualizado: {$data['nome']}");
            setFlashMessage('success', 'Usuário atualizado com sucesso');
            redirect('/usuarios/viewUsuario?id=' . $id);
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao atualizar usuário: ' . $e->getMessage());
            redirect('/usuarios/edit?id=' . $id);
        }
    }

    /**
     * Deleta usuário (soft delete)
     */
    public function delete() {
        if (!$this->isPost()) {
            redirect('/usuarios');
        }

        $user = getCurrentUser();
        if ($user['perfil'] !== 'administrador') {
            setFlashMessage('error', 'Acesso negado');
            redirect('/dashboard');
        }

        $id = $this->getPost('id');

        // Não pode deletar a si mesmo
        if ($user['id'] == $id) {
            setFlashMessage('error', 'Você não pode inativar seu próprio usuário');
            redirect('/usuarios');
        }

        try {
            $usuario = $this->model->find($id);
            $this->model->update($id, ['ativo' => 0]);
            logActivity('usuario_deletado', "Usuário inativado: {$usuario['nome']}");
            setFlashMessage('success', 'Usuário inativado com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao inativar usuário: ' . $e->getMessage());
        }

        redirect('/usuarios');
    }

    /**
     * Perfil do usuário logado
     */
    public function perfil() {
        $user = getCurrentUser();
        $usuario = $this->model->find($user['id']);

        unset($usuario['senha']);

        $this->view('usuarios/perfil', ['usuario' => $usuario]);
    }

    /**
     * Atualizar senha
     */
    public function alterarSenha() {
        if (!$this->isPost()) {
            redirect('/usuarios/perfil');
        }

        $user = getCurrentUser();
        $senhaAtual = $this->getPost('senha_atual');
        $novaSenha = $this->getPost('nova_senha');
        $confirmarSenha = $this->getPost('confirmar_senha');

        // Validar senha atual
        $usuario = $this->model->find($user['id']);
        if (!verifyPassword($senhaAtual, $usuario['senha'])) {
            setFlashMessage('error', 'Senha atual incorreta');
            redirect('/usuarios/perfil');
        }

        // Validar nova senha
        if ($novaSenha !== $confirmarSenha) {
            setFlashMessage('error', 'Senhas não conferem');
            redirect('/usuarios/perfil');
        }

        if (strlen($novaSenha) < 6) {
            setFlashMessage('error', 'Senha deve ter pelo menos 6 caracteres');
            redirect('/usuarios/perfil');
        }

        try {
            $this->model->updatePassword($user['id'], $novaSenha);
            logActivity('senha_alterada', "Senha alterada pelo usuário");
            setFlashMessage('success', 'Senha alterada com sucesso');
        } catch (Exception $e) {
            setFlashMessage('error', 'Erro ao alterar senha: ' . $e->getMessage());
        }

        redirect('/usuarios/perfil');
    }
}
?>
