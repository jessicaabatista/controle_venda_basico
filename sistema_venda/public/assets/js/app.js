/**
 * Sistema de Feedback Visual Refatorado
 * Notificacoes, loading e alertas com melhor UX
 */

class FeedbackVisual {
    static mostrarNotificacao(tipo, mensagem, duracao = 4000) {
        const notificacao = document.createElement('div');
        notificacao.className = `notificacao notificacao-${tipo}`;
        
        const iconClass = {
            'sucesso': 'fa-check-circle',
            'erro': 'fa-exclamation-circle',
            'aviso': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        
        notificacao.innerHTML = `
            <div class="notificacao-conteudo">
                <i class="fas ${iconClass[tipo] || 'fa-info-circle'} notificacao-icone"></i>
                <span class="notificacao-texto">${mensagem}</span>
                <button class="notificacao-fechar" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(notificacao);
        
        setTimeout(() => notificacao.classList.add('show'), 10);
        
        setTimeout(() => {
            notificacao.classList.remove('show');
            setTimeout(() => notificacao.remove(), 300);
        }, duracao);
    }

    static mostrarLoading(elemento, mensagem = 'Processando...') {
        const textoOriginal = elemento.textContent;
        elemento.textContent = mensagem;
        elemento.disabled = true;
        elemento.setAttribute('data-texto-original', textoOriginal);
        elemento.classList.add('loading');
    }

    static restaurarElemento(elemento) {
        const textoOriginal = elemento.getAttribute('data-texto-original') || 'Enviar';
        elemento.textContent = textoOriginal;
        elemento.disabled = false;
        elemento.classList.remove('loading');
    }

    static mostrarConfirmacao(titulo, mensagem, callback) {
        const modal = document.createElement('div');
        modal.className = 'modal show';
        modal.innerHTML = `
            <div class="modal-content" style="max-width: 400px;">
                <div class="modal-header">
                    <h2>${titulo}</h2>
                    <button class="modal-close" onclick="this.closest('.modal').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <p>${mensagem}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">Cancelar</button>
                    <button class="btn btn-danger" onclick="
                        this.closest('.modal').remove();
                        ${callback.toString()}();
                    ">Confirmar</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }
}

window.FeedbackVisual = FeedbackVisual;

function formatarMoeda(valor) {
    if (!valor) return 'R$ 0,00';
    
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(valor);
}

function formatarData(data) {
    if (!data) return '';
    
    try {
        const date = new Date(data + 'T00:00:00');
        return date.toLocaleDateString('pt-BR');
    } catch (e) {
        return '';
    }
}

function formatarDataCompleta(data) {
    if (!data) return '';
    
    try {
        const date = new Date(data);
        return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR');
    } catch (e) {
        return '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.input-telefone').forEach(input => {
        if (window.Mascara) {
            window.Mascara.aplicarTelefone(input);
        }
    });

    document.querySelectorAll('.input-cpf-cnpj').forEach(input => {
        if (window.Mascara) {
            window.Mascara.aplicarCPFouCNPJ(input);
        }
    });
});