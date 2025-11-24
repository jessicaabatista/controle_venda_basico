/**
 * Sistema de Validações - Frontend
 * Validações robustas para formulários do sistema
 */

class Validador {
    // Validação de email
    static isValidEmail(email) {
        const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return regex.test(email);
    }

    // Validação de telefone brasileiro
    static isValidTelefone(telefone) {
        const regex = /^\(?(\d{2})\)?[\s-]?(\d{4,5})[\s-]?(\d{4})$/;
        return regex.test(telefone);
    }

    // Validação de CPF
    static isValidCPF(cpf) {
        if (!cpf) return false;

        // Remove caracteres não númericos
        cpf = cpf.replace(/[^\d]/g, '');

        // Verifica se tem 11 digitos
        if (cpf.length !== 11) return false;

        // Verifica se todos os digitos são iguais
        if (/^(\d)\1{10}$/.test(cpf)) return false;

        // Validação do CPF
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

    // Validação de CNPJ
    static isValidCNPJ(cnpj) {
        if (!cnpj) return false;

        // Remove caracteres não númericos
        cnpj = cnpj.replace(/[^\d]/g, '');

        // Verifica se tem 14 digitos
        if (cnpj.length !== 14) return false;

        // Verifica se todos os digitos são iguais
        if (/^(\d)\1{13}$/.test(cnpj)) return false;

        // Validação do CNPJ
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

    // Validação de CEP
    static isValidCEP(cep) {
        const regex = /^\d{5}-?\d{3}$/;
        return regex.test(cep);
    }

    // Validação de valor monetário
    static isValidValor(valor) {
        // Remove formatação e verifica se é um número válido
        const valorLimpo = valor.replace(/[R$\s.,]/g, '').replace(',', '.');
        const numero = parseFloat(valorLimpo);

        return !isNaN(numero) && numero > 0 && numero <= 999999.99;
    }

    // Validação de data
    static isValidData(data) {
        const regex = /^\d{2}\/\d{2}\/\d{4}$/;
        if (!regex.test(data)) return false;

        const partes = data.split('/');
        const dia = parseInt(partes[0]);
        const mes = parseInt(partes[1]);
        const ano = parseInt(partes[2]);

        // Verifica se a data é válida
        const dataObj = new Date(ano, mes - 1, dia);

        return dataObj.getDate() === dia &&
            dataObj.getMonth() === mes - 1 &&
            dataObj.getFullYear() === ano &&
            ano >= 1900 && ano <= 2100;
    }

    // Validação de nome (mínimo 3 caracteres, apenas letras e espaços)
    static isValidNome(nome) {
        if (!nome || nome.length < 3) return false;

        // Permite letras sem acento, acentuadas, espaços e apóstrofos
        const regex = /^[a-zA-ZÀ-ÖØ-öø-ÿ\s']+$/;
        return regex.test(nome.trim());
    }

    // Validação de código de produto
    static isValidCodigoProduto(codigo) {
        if (!codigo || codigo.length < 3) return false;

        // Permite letras, números, hífens e underscores
        const regex = /^[a-zA-Z0-9_-]+$/;
        return regex.test(codigo.trim());
    }

    // Validação de quantidade (número inteiro positivo)
    static isValidQuantidade(quantidade) {
        const num = parseInt(quantidade);
        return !isNaN(num) && num > 0 && num <= 9999;
    }

    // Validação de número de parcelas
    static isValidParcelas(parcelas) {
        const num = parseInt(parcelas);
        return !isNaN(num) && num >= 1 && num <= 24;
    }

    // Validação de campo obrigatório
    static isRequired(valor) {
        return valor !== null && valor !== undefined && valor.toString().trim() !== '';
    }

    // Validação de comprimento mínimo
    static hasMinLength(valor, minimo) {
        return valor && valor.toString().trim().length >= minimo;
    }

    // Validação de comprimento máximo
    static hasMaxLength(valor, maximo) {
        return !valor || valor.toString().trim().length <= maximo;
    }

    // Validação de número inteiro
    static isInteiro(valor) {
        const num = parseInt(valor);
        return !isNaN(num) && Number.isInteger(num);
    }

    // Validação de número decimal
    static isDecimal(valor) {
        const num = parseFloat(valor.replace(',', '.'));
        return !isNaN(num);
    }

    // Validação de URL
    static isValidURL(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }

    // Validação de senha forte
    static isValidSenha(senha) {
        if (!senha || senha.length < 8) return false;

        // Pelo menos uma letra maiúscula, uma minúscula, um número e um caractere especial
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
        return regex.test(senha);
    }

    // Validação de confirmação de senha
    static isSenhaConfirmada(senha, confirmacao) {
        return senha === confirmacao;
    }

    // Limpar e formatar valor monetário
    static formatarValor(valor) {
        if (!valor) return '0,00';

        // Remove tudo que não é número
        const numeros = valor.replace(/[^\d]/g, '');

        if (numeros.length === 0) return '0,00';

        // Converte para decimal
        const centavos = parseInt(numeros);
        const reais = centavos / 100;

        return reais.toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Aplicar máscara de telefone
    static mascararTelefone(telefone) {
        const numeros = telefone.replace(/\D/g, '');

        if (numeros.length <= 10) {
            return numeros.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        } else {
            return numeros.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        }
    }

    // Aplicar máscara de CPF
    static mascararCPF(cpf) {
        const numeros = cpf.replace(/\D/g, '');
        return numeros.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }

    // Aplicar máscara de CNPJ
    static mascararCNPJ(cnpj) {
        const numeros = cnpj.replace(/\D/g, '');
        return numeros.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/, '$1.$2.$3/$4-$5');
    }

    // Aplicar máscara de CEP
    static mascararCEP(cep) {
        const numeros = cep.replace(/\D/g, '');
        return numeros.replace(/(\d{5})(\d{3})/, '$1-$2');
    }

    // Aplicar máscara de data
    static mascararData(data) {
        const numeros = data.replace(/\D/g, '');

        if (numeros.length <= 2) {
            return numeros;
        } else if (numeros.length <= 4) {
            return numeros.replace(/(\d{2})(\d{2})/, '$1/$2');
        } else {
            return numeros.replace(/(\d{2})(\d{2})(\d{4})/, '$1/$2/$3');
        }
    }

    // Validar formulário completo
    static validarFormulario(formulario, regras) {
        const erros = {};
        let valido = true;

        for (const campo in regras) {
            const elemento = formulario.querySelector(`[name="${campo}"]`);
            if (!elemento) continue;

            const valor = elemento.value;
            const regrasCampo = regras[campo];

            for (const regra of regrasCampo) {
                const resultado = this.validarCampo(valor, regra);

                if (!resultado.valido) {
                    erros[campo] = resultado.mensagem;
                    valido = false;
                    break;
                }
            }
        }

        return { valido, erros };
    }

    // Validar campo individual
    static validarCampo(valor, regra) {
        const { tipo, mensagem, parametro } = regra;

        switch (tipo) {
            case 'required':
                return {
                    valido: this.isRequired(valor),
                    mensagem: mensagem || 'Este campo é obrigatório'
                };

            case 'email':
                return {
                    valido: !valor || this.isValidEmail(valor),
                    mensagem: mensagem || 'Email inválido'
                };

            case 'telefone':
                return {
                    valido: !valor || this.isValidTelefone(valor),
                    mensagem: mensagem || 'Telefone inválido'
                };

            case 'cpf':
                return {
                    valido: !valor || this.isValidCPF(valor),
                    mensagem: mensagem || 'CPF inválido'
                };

            case 'cnpj':
                return {
                    valido: !valor || this.isValidCNPJ(valor),
                    mensagem: mensagem || 'CNPJ inválido'
                };

            case 'cep':
                return {
                    valido: !valor || this.isValidCEP(valor),
                    mensagem: mensagem || 'CEP inválido'
                };

            case 'valor':
                return {
                    valido: !valor || this.isValidValor(valor),
                    mensagem: mensagem || 'Valor inválido'
                };

            case 'data':
                return {
                    valido: !valor || this.isValidData(valor),
                    mensagem: mensagem || 'Data inválida'
                };

            case 'nome':
                return {
                    valido: !valor || this.isValidNome(valor),
                    mensagem: mensagem || 'Nome inválido'
                };

            case 'codigo_produto':
                return {
                    valido: !valor || this.isValidCodigoProduto(valor),
                    mensagem: mensagem || 'Código do produto inválido'
                };

            case 'quantidade':
                return {
                    valido: !valor || this.isValidQuantidade(valor),
                    mensagem: mensagem || 'Quantidade inválida'
                };

            case 'parcelas':
                return {
                    valido: !valor || this.isValidParcelas(valor),
                    mensagem: mensagem || 'Número de parcelas inválido'
                };

            case 'minLength':
                return {
                    valido: !valor || this.hasMinLength(valor, parametro),
                    mensagem: mensagem || `Mínimo de ${parametro} caracteres`
                };

            case 'maxLength':
                return {
                    valido: !valor || this.hasMaxLength(valor, parametro),
                    mensagem: mensagem || `Máximo de ${parametro} caracteres`
                };

            case 'inteiro':
                return {
                    valido: !valor || this.isInteiro(valor),
                    mensagem: mensagem || 'Valor deve ser um número inteiro'
                };

            case 'decimal':
                return {
                    valido: !valor || this.isDecimal(valor),
                    mensagem: mensagem || 'Valor deve ser um número decimal'
                };

            case 'senha':
                return {
                    valido: !valor || this.isValidSenha(valor),
                    mensagem: mensagem || 'Senha deve ter pelo menos 8 caracteres, incluindo letras maiúsculas, minúsculas, números e caracteres especiais'
                };

            default:
                return { valido: true, mensagem: '' };
        }
    }

    // Exibir erros no formulário
    static exibirErros(formulario, erros) {
        // Limpar erros anteriores
        formulario.querySelectorAll('.form-error').forEach(el => el.remove());
        formulario.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

        for (const campo in erros) {
            const elemento = formulario.querySelector(`[name="${campo}"]`);
            if (!elemento) continue;

            // Adicionar classe de erro
            elemento.classList.add('error');

            // Criar mensagem de erro
            const mensagemErro = document.createElement('small');
            mensagemErro.className = 'form-error';
            mensagemErro.textContent = erros[campo];
            mensagemErro.style.color = '#ff6b6b';
            mensagemErro.style.fontSize = '12px';
            mensagemErro.style.marginTop = '5px';
            mensagemErro.style.display = 'block';

            // Inserir mensagem após o campo
            elemento.parentNode.insertBefore(mensagemErro, elemento.nextSibling);
        }
    }

    // Limpar erros do formulário
    static limparErros(formulario) {
        formulario.querySelectorAll('.form-error').forEach(el => el.remove());
        formulario.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
    }
}

// Exportar para uso global
window.Validador = Validador;
