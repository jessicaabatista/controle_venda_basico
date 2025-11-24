// Funções auxiliares globais

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function formatarData(data) {
    if (!data) return '';
    const date = new Date(data + 'T00:00:00');
    return date.toLocaleDateString('pt-BR');
}

function formatarDataCompleta(data) {
    if (!data) return '';
    const date = new Date(data);
    return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR');
}

// Máscaras de entrada
function aplicarMascaraTelefone(input) {
    input.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, '');
        
        if (valor.length > 11) {
            valor = valor.substring(0, 11);
        }
        
        if (valor.length > 7) {
            valor = valor.substring(0, 7) + '-' + valor.substring(7);
        }
        
        if (valor.length > 2) {
            valor = '(' + valor.substring(0, 2) + ') ' + valor.substring(2);
        }
        
        this.value = valor;
    });
}

function aplicarMascaraCpfCnpj(input) {
    input.addEventListener('input', function() {
        let valor = this.value.replace(/\D/g, '');
        
        if (valor.length <= 11) {
            // CPF
            if (valor.length > 8) {
                valor = valor.substring(0, 3) + '.' + valor.substring(3, 6) + '.' + valor.substring(6, 9) + '-' + valor.substring(9);
            } else if (valor.length > 5) {
                valor = valor.substring(0, 3) + '.' + valor.substring(3, 6) + '.' + valor.substring(6);
            } else if (valor.length > 2) {
                valor = valor.substring(0, 3) + '.' + valor.substring(3);
            }
        } else {
            // CNPJ
            if (valor.length > 12) {
                valor = valor.substring(0, 2) + '.' + valor.substring(2, 5) + '.' + valor.substring(5, 8) + '/' + valor.substring(8, 12) + '-' + valor.substring(12);
            } else if (valor.length > 8) {
                valor = valor.substring(0, 2) + '.' + valor.substring(2, 5) + '.' + valor.substring(5, 8) + '/' + valor.substring(8);
            } else if (valor.length > 5) {
                valor = valor.substring(0, 2) + '.' + valor.substring(2, 5) + '.' + valor.substring(5);
            } else if (valor.length > 2) {
                valor = valor.substring(0, 2) + '.' + valor.substring(2);
            }
        }
        
        this.value = valor;
    });
}

// Validação de email
function validarEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

// Confirmação com SweetAlert (opcional)
function confirmar(mensagem, callback) {
    if (confirm(mensagem)) {
        callback();
    }
}

// Toast de notificação
function mostrarNotificacao(mensagem, tipo = 'info') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 5px;
        color: white;
        z-index: 9999;
        animation: slideIn 0.3s;
    `;

    if (tipo === 'sucesso') {
        toast.style.background = '#4caf50';
    } else if (tipo === 'erro') {
        toast.style.background = '#f44336';
    } else if (tipo === 'aviso') {
        toast.style.background = '#ff9800';
    } else {
        toast.style.background = '#2196f3';
    }

    toast.textContent = mensagem;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Inicializar aplicação
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar máscaras em elementos com classes específicas
    document.querySelectorAll('.input-telefone').forEach(input => {
        aplicarMascaraTelefone(input);
    });

    document.querySelectorAll('.input-cpf-cnpj').forEach(input => {
        aplicarMascaraCpfCnpj(input);
    });
});