/**
 * Sistema de Máscaras de Entrada - Mascaras.js
 * Arquivo: public/assets/js/mascaras.js
 * Fornece máscaras para formatação de inputs em tempo real
 */

/**
 * Classe para gerenciar máscaras de entrada
 */
class Mascara {
    /**
     * Aplicar máscara de telefone
     * Formatos aceitos: (XX) 9XXXX-XXXX ou (XX) XXXX-XXXX
     * @param {HTMLInputElement} input
     */
    static aplicarTelefone(input) {
        input.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            
            // Limitar a 11 dígitos
            if (valor.length > 11) {
                valor = valor.substring(0, 11);
            }
            
            // Formatar
            if (valor.length === 0) {
                this.value = '';
            } else if (valor.length <= 2) {
                this.value = '(' + valor;
            } else if (valor.length <= 6) {
                this.value = '(' + valor.substring(0, 2) + ') ' + valor.substring(2);
            } else if (valor.length <= 10) {
                this.value = '(' + valor.substring(0, 2) + ') ' + valor.substring(2, 6) + '-' + valor.substring(6);
            } else {
                this.value = '(' + valor.substring(0, 2) + ') ' + valor.substring(2, 7) + '-' + valor.substring(7);
            }
        });

        // Validar ao sair do campo
        input.addEventListener('blur', function() {
            const valor = this.value.replace(/\D/g, '');
            if (valor.length > 0 && valor.length < 10) {
                this.classList.add('invalid');
                this.setAttribute('data-error', 'Telefone incompleto');
            } else {
                this.classList.remove('invalid');
                this.removeAttribute('data-error');
            }
        });
    }

    /**
     * Aplicar máscara de CPF
     * Formato: XXX.XXX.XXX-XX
     * @param {HTMLInputElement} input
     */
    static aplicarCPF(input) {
        input.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            
            // Limitar a 11 dígitos
            if (valor.length > 11) {
                valor = valor.substring(0, 11);
            }
            
            // Formatar
            if (valor.length === 0) {
                this.value = '';
            } else if (valor.length <= 3) {
                this.value = valor;
            } else if (valor.length <= 6) {
                this.value = valor.substring(0, 3) + '.' + valor.substring(3);
            } else if (valor.length <= 9) {
                this.value = valor.substring(0, 3) + '.' + valor.substring(3, 6) + '.' + valor.substring(6);
            } else {
                this.value = valor.substring(0, 3) + '.' + valor.substring(3, 6) + '.' + valor.substring(6, 9) + '-' + valor.substring(9);
            }
        });

        // Validar ao sair do campo
        input.addEventListener('blur', function() {
            const valor = this.value.replace(/\D/g, '');
            if (valor.length === 11) {
                if (Mascara.validarCPF(valor)) {
                    this.classList.remove('invalid');
                    this.removeAttribute('data-error');
                } else {
                    this.classList.add('invalid');
                    this.setAttribute('data-error', 'CPF inválido');
                }
            } else if (valor.length > 0) {
                this.classList.add('invalid');
                this.setAttribute('data-error', 'CPF deve ter 11 dígitos');
            }
        });
    }

    /**
     * Aplicar máscara de CNPJ
     * Formato: XX.XXX.XXX/XXXX-XX
     * @param {HTMLInputElement} input
     */
    static aplicarCNPJ(input) {
        input.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            
            // Limitar a 14 dígitos
            if (valor.length > 14) {
                valor = valor.substring(0, 14);
            }
            
            // Formatar
            if (valor.length === 0) {
                this.value = '';
            } else if (valor.length <= 2) {
                this.value = valor;
            } else if (valor.length <= 5) {
                this.value = valor.substring(0, 2) + '.' + valor.substring(2);
            } else if (valor.length <= 8) {
                this.value = valor.substring(0, 2) + '.' + valor.substring(2, 5) + '.' + valor.substring(5);
            } else if (valor.length <= 12) {
                this.value = valor.substring(0, 2) + '.' + valor.substring(2, 5) + '.' + valor.substring(5, 8) + '/' + valor.substring(8);
            } else {
                this.value = valor.substring(0, 2) + '.' + valor.substring(2, 5) + '.' + valor.substring(5, 8) + '/' + valor.substring(8, 12) + '-' + valor.substring(12);
            }
        });

        // Validar ao sair do campo
        input.addEventListener('blur', function() {
            const valor = this.value.replace(/\D/g, '');
            if (valor.length === 14) {
                if (Mascara.validarCNPJ(valor)) {
                    this.classList.remove('invalid');
                    this.removeAttribute('data-error');
                } else {
                    this.classList.add('invalid');
                    this.setAttribute('data-error', 'CNPJ inválido');
                }
            } else if (valor.length > 0) {
                this.classList.add('invalid');
                this.setAttribute('data-error', 'CNPJ deve ter 14 dígitos');
            }
        });
    }

    /**
     * Aplicar máscara de CPF ou CNPJ (automática)
     * @param {HTMLInputElement} input
     */
    static aplicarCPFouCNPJ(input) {
        input.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            
            if (valor.length <= 11) {
                this.value = Mascara.formatarCPF(valor);
            } else {
                this.value = Mascara.formatarCNPJ(valor.substring(0, 14));
            }
        });

        input.addEventListener('blur', function() {
            const valor = this.value.replace(/\D/g, '');
            
            if (valor.length === 11) {
                if (!Mascara.validarCPF(valor)) {
                    this.classList.add('invalid');
                    this.setAttribute('data-error', 'CPF inválido');
                } else {
                    this.classList.remove('invalid');
                    this.removeAttribute('data-error');
                }
            } else if (valor.length === 14) {
                if (!Mascara.validarCNPJ(valor)) {
                    this.classList.add('invalid');
                    this.setAttribute('data-error', 'CNPJ inválido');
                } else {
                    this.classList.remove('invalid');
                    this.removeAttribute('data-error');
                }
            } else if (valor.length > 0) {
                this.classList.add('invalid');
                this.setAttribute('data-error', 'CPF deve ter 11 dígitos ou CNPJ com 14');
            }
        });
    }

    /**
     * Aplicar máscara de CEP
     * Formato: XXXXX-XXX
     * @param {HTMLInputElement} input
     */
    static aplicarCEP(input) {
        input.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            
            if (valor.length > 8) {
                valor = valor.substring(0, 8);
            }
            
            if (valor.length === 0) {
                this.value = '';
            } else if (valor.length <= 5) {
                this.value = valor;
            } else {
                this.value = valor.substring(0, 5) + '-' + valor.substring(5);
            }
        });

        input.addEventListener('blur', function() {
            const valor = this.value.replace(/\D/g, '');
            if (valor.length === 8) {
                this.classList.remove('invalid');
                this.removeAttribute('data-error');
            } else if (valor.length > 0) {
                this.classList.add('invalid');
                this.setAttribute('data-error', 'CEP deve ter 8 dígitos');
            }
        });
    }

    /**
     * Aplicar máscara de valor monetário
     * @param {HTMLInputElement} input
     */
    static aplicarMoeda(input) {
        input.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            let inteiro = valor.slice(0, -2) || '0';
            let decimais = valor.slice(-2);

            inteiro = inteiro.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            this.value = 'R$ ' + inteiro + ',' + decimais;
        });
    }

    /**
     * Aplicar máscara de data
     * Formato: DD/MM/YYYY
     * @param {HTMLInputElement} input
     */
    static aplicarData(input) {
        input.addEventListener('input', function() {
            let valor = this.value.replace(/\D/g, '');
            
            if (valor.length > 8) {
                valor = valor.substring(0, 8);
            }
            
            if (valor.length === 0) {
                this.value = '';
            } else if (valor.length <= 2) {
                this.value = valor;
            } else if (valor.length <= 4) {
                this.value = valor.substring(0, 2) + '/' + valor.substring(2);
            } else {
                this.value = valor.substring(0, 2) + '/' + valor.substring(2, 4) + '/' + valor.substring(4);
            }
        });

        input.addEventListener('blur', function() {
            const valor = this.value.replace(/\D/g, '');
            if (valor.length === 8) {
                const dia = parseInt(valor.substring(0, 2));
                const mes = parseInt(valor.substring(2, 4));
                const ano = parseInt(valor.substring(4));

                if (dia >= 1 && dia <= 31 && mes >= 1 && mes <= 12 && ano >= 1900 && ano <= 2100) {
                    this.classList.remove('invalid');
                    this.removeAttribute('data-error');
                } else {
                    this.classList.add('invalid');
                    this.setAttribute('data-error', 'Data inválida');
                }
            } else if (valor.length > 0) {
                this.classList.add('invalid');
                this.setAttribute('data-error', 'Data deve estar no formato DD/MM/YYYY');
            }
        });
    }

    /**
     * Formatar CPF sem validação
     * @param {string} valor
     * @returns {string}
     */
    static formatarCPF(valor) {
        valor = valor.replace(/\D/g, '');
        if (valor.length > 11) valor = valor.substring(0, 11);
        
        if (valor.length === 0) return '';
        if (valor.length <= 3) return valor;
        if (valor.length <= 6) return valor.substring(0, 3) + '.' + valor.substring(3);
        if (valor.length <= 9) return valor.substring(0, 3) + '.' + valor.substring(3, 6) + '.' + valor.substring(6);
        return valor.substring(0, 3) + '.' + valor.substring(3, 6) + '.' + valor.substring(6, 9) + '-' + valor.substring(9);
    }

    /**
     * Formatar CNPJ sem validação
     * @param {string} valor
     * @returns {string}
     */
    static formatarCNPJ(valor) {
        valor = valor.replace(/\D/g, '');
        if (valor.length > 14) valor = valor.substring(0, 14);
        
        if (valor.length === 0) return '';
        if (valor.length <= 2) return valor;
        if (valor.length <= 5) return valor.substring(0, 2) + '.' + valor.substring(2);
        if (valor.length <= 8) return valor.substring(0, 2) + '.' + valor.substring(2, 5) + '.' + valor.substring(5);
        if (valor.length <= 12) return valor.substring(0, 2) + '.' + valor.substring(2, 5) + '.' + valor.substring(5, 8) + '/' + valor.substring(8);
        return valor.substring(0, 2) + '.' + valor.substring(2, 5) + '.' + valor.substring(5, 8) + '/' + valor.substring(8, 12) + '-' + valor.substring(12);
    }

    /**
     * Validar CPF
     * @param {string} cpf
     * @returns {boolean}
     */
    static validarCPF(cpf) {
        cpf = cpf.replace(/\D/g, '');

        if (cpf.length !== 11) return false;
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        let soma = 0;
        let resto;

        for (let i = 1; i <= 9; i++) {
            soma = soma + parseInt(cpf.substring(i - 1, i)) * (11 - i);
        }

        resto = (soma * 10) % 11;
        if ((resto === 10) || (resto === 11)) resto = 0;
        if (resto !== parseInt(cpf.substring(9, 10))) return false;

        soma = 0;

        for (let i = 1; i <= 10; i++) {
            soma = soma + parseInt(cpf.substring(i - 1, i)) * (12 - i);
        }

        resto = (soma * 10) % 11;
        if ((resto === 10) || (resto === 11)) resto = 0;
        if (resto !== parseInt(cpf.substring(10, 11))) return false;

        return true;
    }

    /**
     * Validar CNPJ
     * @param {string} cnpj
     * @returns {boolean}
     */
    static validarCNPJ(cnpj) {
        cnpj = cnpj.replace(/\D/g, '');

        if (cnpj.length !== 14) return false;
        if (/^(\d)\1{13}$/.test(cnpj)) return false;

        let tamanho = cnpj.length - 2;
        let numeros = cnpj.substring(0, tamanho);
        let digitos = cnpj.substring(tamanho);
        let soma = 0;
        let pos = tamanho - 7;

        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }

        let resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado !== parseInt(digitos.charAt(0))) return false;

        tamanho = tamanho + 1;
        numeros = cnpj.substring(0, tamanho);
        soma = 0;
        pos = tamanho - 7;

        for (let i = tamanho; i >= 1; i--) {
            soma += numeros.charAt(tamanho - i) * pos--;
            if (pos < 2) pos = 9;
        }

        resultado = soma % 11 < 2 ? 0 : 11 - (soma % 11);
        if (resultado !== parseInt(digitos.charAt(1))) return false;

        return true;
    }
}

/**
 * Inicializar máscaras ao carregar a página
 */
document.addEventListener('DOMContentLoaded', function() {
    // Aplicar máscara de telefone a todos os inputs com classe .input-telefone
    document.querySelectorAll('.input-telefone').forEach(input => {
        Mascara.aplicarTelefone(input);
    });

    // Aplicar máscara de CPF a todos os inputs com classe .input-cpf
    document.querySelectorAll('.input-cpf').forEach(input => {
        Mascara.aplicarCPF(input);
    });

    // Aplicar máscara de CNPJ a todos os inputs com classe .input-cnpj
    document.querySelectorAll('.input-cnpj').forEach(input => {
        Mascara.aplicarCNPJ(input);
    });

    // Aplicar máscara de CPF ou CNPJ a todos os inputs com classe .input-cpf-cnpj
    document.querySelectorAll('.input-cpf-cnpj').forEach(input => {
        Mascara.aplicarCPFouCNPJ(input);
    });

    // Aplicar máscara de CEP a todos os inputs com classe .input-cep
    document.querySelectorAll('.input-cep').forEach(input => {
        Mascara.aplicarCEP(input);
    });

    // Aplicar máscara de moeda a todos os inputs com classe .input-moeda
    document.querySelectorAll('.input-moeda').forEach(input => {
        Mascara.aplicarMoeda(input);
    });

    // Aplicar máscara de data a todos os inputs com classe .input-data
    document.querySelectorAll('.input-data').forEach(input => {
        Mascara.aplicarData(input);
    });
});

// Exportar para uso global
window.Mascara = Mascara;