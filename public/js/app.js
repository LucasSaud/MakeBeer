// Arquivo JavaScript principal do sistema Atomos

/**
 * Utilitários gerais
 */
const Utils = {
    // Formatar moeda
    formatMoney: function(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(value);
    },

    // Formatar data
    formatDate: function(date, includeTime = false) {
        const options = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        };

        if (includeTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
        }

        return new Intl.DateTimeFormat('pt-BR', options).format(new Date(date));
    },

    // Formatar quantidade
    formatQuantity: function(value, unit = 'kg') {
        return new Intl.NumberFormat('pt-BR', {
            minimumFractionDigits: 3,
            maximumFractionDigits: 3
        }).format(value) + ' ' + unit;
    },

    // Debounce para pesquisas
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Validar email
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    // Validar CNPJ
    isValidCNPJ: function(cnpj) {
        cnpj = cnpj.replace(/[^\d]+/g, '');

        if (cnpj.length !== 14) return false;

        // Verificar se todos os dígitos são iguais
        if (/^(\d)\1{13}$/.test(cnpj)) return false;

        // Validar dígitos verificadores
        let length = cnpj.length - 2;
        let numbers = cnpj.substring(0, length);
        let digits = cnpj.substring(length);
        let sum = 0;
        let pos = length - 7;

        for (let i = length; i >= 1; i--) {
            sum += numbers.charAt(length - i) * pos--;
            if (pos < 2) pos = 9;
        }

        let result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        if (result != digits.charAt(0)) return false;

        length = length + 1;
        numbers = cnpj.substring(0, length);
        sum = 0;
        pos = length - 7;

        for (let i = length; i >= 1; i--) {
            sum += numbers.charAt(length - i) * pos--;
            if (pos < 2) pos = 9;
        }

        result = sum % 11 < 2 ? 0 : 11 - sum % 11;
        if (result != digits.charAt(1)) return false;

        return true;
    }
};

/**
 * Sistema de notificações
 */
const Notifications = {
    show: function(message, type = 'info', duration = 5000) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;
        alertDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideInRight 0.3s ease;
        `;

        document.body.appendChild(alertDiv);

        setTimeout(() => {
            alertDiv.style.opacity = '0';
            alertDiv.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.parentNode.removeChild(alertDiv);
                }
            }, 300);
        }, duration);
    },

    success: function(message) {
        this.show(message, 'success');
    },

    error: function(message) {
        this.show(message, 'error');
    },

    warning: function(message) {
        this.show(message, 'warning');
    },

    info: function(message) {
        this.show(message, 'info');
    }
};

/**
 * Sistema de modais
 */
const Modal = {
    show: function(content, title = '', size = 'normal') {
        const overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = `
            <div class="modal ${size === 'large' ? 'modal-lg' : ''}">
                <div class="modal-header">
                    <h3 class="modal-title">${title}</h3>
                    <button type="button" class="modal-close" onclick="Modal.close()">&times;</button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        `;

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        // Fechar ao clicar no overlay
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                Modal.close();
            }
        });

        // Fechar com ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                Modal.close();
            }
        });
    },

    close: function() {
        const overlay = document.querySelector('.modal-overlay');
        if (overlay) {
            overlay.remove();
            document.body.style.overflow = '';
        }
    },

    confirm: function(message, callback) {
        const content = `
            <p>${message}</p>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="Modal.close()">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="Modal.confirmCallback()">Confirmar</button>
            </div>
        `;

        this.show(content, 'Confirmação');
        this.confirmCallback = function() {
            callback();
            Modal.close();
        };
    }
};

/**
 * Sistema de AJAX
 */
const Ajax = {
    request: function(url, options = {}) {
        const defaults = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': window.App.csrfToken
            }
        };

        const config = Object.assign({}, defaults, options);

        return fetch(url, config)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .catch(error => {
                console.error('Ajax error:', error);
                Notifications.error('Erro na comunicação com o servidor');
                throw error;
            });
    },

    get: function(url, params = {}) {
        const urlParams = new URLSearchParams(params).toString();
        const fullUrl = urlParams ? `${url}?${urlParams}` : url;
        return this.request(fullUrl);
    },

    post: function(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    put: function(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    delete: function(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    }
};

/**
 * Máscaras de input
 */
const Masks = {
    init: function() {
        // Máscara para CNPJ
        document.querySelectorAll('[data-mask="cnpj"]').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                value = value.replace(/^(\d{2})(\d)/, '$1.$2');
                value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
                value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
                value = value.replace(/(\d{4})(\d)/, '$1-$2');
                this.value = value;
            });
        });

        // Máscara para telefone
        document.querySelectorAll('[data-mask="phone"]').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d{4})(\d)/, '($1) $2-$3');
                } else {
                    value = value.replace(/^(\d{2})(\d{5})(\d)/, '($1) $2-$3');
                }
                this.value = value;
            });
        });

        // Máscara para CEP
        document.querySelectorAll('[data-mask="cep"]').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                value = value.replace(/^(\d{5})(\d)/, '$1-$2');
                this.value = value;
            });
        });

        // Máscara para moeda
        document.querySelectorAll('[data-mask="money"]').forEach(input => {
            input.addEventListener('input', function() {
                let value = this.value.replace(/\D/g, '');
                value = (value / 100).toFixed(2) + '';
                value = value.replace(".", ",");
                value = value.replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1.');
                this.value = 'R$ ' + value;
            });
        });
    }
};

/**
 * Tabelas dinâmicas
 */
const DataTable = {
    init: function(tableId, options = {}) {
        const table = document.getElementById(tableId);
        if (!table) return;

        const defaults = {
            sortable: true,
            searchable: true,
            pagination: true,
            pageSize: 20
        };

        const config = Object.assign({}, defaults, options);

        if (config.searchable) {
            this.addSearch(table);
        }

        if (config.sortable) {
            this.addSorting(table);
        }

        if (config.pagination) {
            this.addPagination(table, config.pageSize);
        }
    },

    addSearch: function(table) {
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Pesquisar...';
        searchInput.className = 'form-control mb-3';

        table.parentNode.insertBefore(searchInput, table);

        searchInput.addEventListener('input', Utils.debounce(function() {
            const searchTerm = this.value.toLowerCase();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        }, 300));
    },

    addSorting: function(table) {
        const headers = table.querySelectorAll('th[data-sortable]');

        headers.forEach(header => {
            header.style.cursor = 'pointer';
            header.innerHTML += ' <span class="sort-indicator">↕</span>';

            header.addEventListener('click', function() {
                const column = this.dataset.sortable;
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));

                const isAscending = !this.classList.contains('sort-asc');

                // Remover classes de ordenação de todos os headers
                headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));

                // Adicionar classe ao header atual
                this.classList.add(isAscending ? 'sort-asc' : 'sort-desc');

                // Ordenar linhas
                rows.sort((a, b) => {
                    const aVal = a.children[column].textContent.trim();
                    const bVal = b.children[column].textContent.trim();

                    if (isNumeric(aVal) && isNumeric(bVal)) {
                        return isAscending ? aVal - bVal : bVal - aVal;
                    } else {
                        return isAscending ?
                            aVal.localeCompare(bVal) :
                            bVal.localeCompare(aVal);
                    }
                });

                // Recriar tbody com linhas ordenadas
                tbody.innerHTML = '';
                rows.forEach(row => tbody.appendChild(row));
            });
        });

        function isNumeric(str) {
            return !isNaN(str) && !isNaN(parseFloat(str));
        }
    },

    addPagination: function(table, pageSize) {
        // Implementação básica de paginação
        // TODO: Implementar paginação completa
    }
};

/**
 * Inicialização quando DOM estiver pronto
 */
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar máscaras
    Masks.init();

    // Inicializar tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.title = element.dataset.tooltip;
    });

    // Confirmar ações de exclusão
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-delete') || e.target.closest('.btn-delete')) {
            e.preventDefault();
            const button = e.target.classList.contains('btn-delete') ? e.target : e.target.closest('.btn-delete');
            const message = button.dataset.confirmMessage || 'Tem certeza que deseja excluir este item?';

            Modal.confirm(message, function() {
                if (button.href) {
                    window.location.href = button.href;
                } else if (button.dataset.action) {
                    // Realizar ação AJAX
                    Ajax.delete(button.dataset.action)
                        .then(response => {
                            if (response.success) {
                                Notifications.success(response.message || 'Item excluído com sucesso');
                                // Recarregar página ou remover elemento
                                if (button.dataset.reload) {
                                    location.reload();
                                } else {
                                    const row = button.closest('tr');
                                    if (row) row.remove();
                                }
                            } else {
                                Notifications.error(response.message || 'Erro ao excluir item');
                            }
                        })
                        .catch(error => {
                            Notifications.error('Erro ao processar solicitação');
                        });
                }
            });
        }
    });

    // Auto-save para formulários (se habilitado)
    const autoSaveForms = document.querySelectorAll('[data-autosave]');
    autoSaveForms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.addEventListener('change', Utils.debounce(function() {
                // Implementar auto-save
                console.log('Auto-saving form data...');
            }, 1000));
        });
    });
});

// Adicionar estilos para animações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .sort-indicator {
        font-size: 0.8em;
        opacity: 0.5;
    }

    .sort-asc .sort-indicator::after {
        content: ' ↑';
        opacity: 1;
    }

    .sort-desc .sort-indicator::after {
        content: ' ↓';
        opacity: 1;
    }
`;
document.head.appendChild(style);