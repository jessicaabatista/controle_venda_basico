<?php
/**
 * Componente Navbar - Barra de Navegacao do Sistema
 * Arquivo: public/includes/navbar.php
 */

if (!defined('APP_NAME')) {
    require_once dirname(dirname(__DIR__)) . '/config/config.php';
}

$usuarioAutenticado = isset($_SESSION['id_usuario']) ? [
    'id' => $_SESSION['id_usuario'],
    'nome' => $_SESSION['nome_usuario'] ?? 'Usuario',
    'email' => $_SESSION['email_usuario'] ?? ''
] : null;

function isActive($page)
{
    $current = basename($_SERVER['REQUEST_URI']);
    if (strpos($current, '?') !== false) {
        $current = substr($current, 0, strpos($current, '?'));
    }
    return $current === $page ? 'active' : '';
}
?>

<nav class="navbar">
    <div class="navbar-brand">
        <h2>
            <i class="fas fa-chart-line"></i>
            <?php echo htmlspecialchars(APP_NAME); ?>
        </h2>
    </div>

    <div class="navbar-menu">
        <a href="<?php echo PUBLIC_URL_RELATIVE; ?>dashboard.php"
            class="nav-link <?php echo isActive('dashboard.php'); ?>"
            title="Dashboard">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>

        <a href="<?php echo PUBLIC_URL_RELATIVE; ?>vendas.php"
            class="nav-link <?php echo isActive('vendas.php'); ?>"
            title="Gestao de Vendas">
            <i class="fas fa-shopping-cart"></i>
            <span>Vendas</span>
        </a>

        <a href="<?php echo PUBLIC_URL_RELATIVE; ?>nova_venda.php"
            class="nav-link <?php echo isActive('nova_venda.php'); ?>"
            title="Criar Nova Venda">
            <i class="fas fa-plus"></i>
            <span>Nova Venda</span>
        </a>

        <a href="<?php echo PUBLIC_URL_RELATIVE; ?>clientes.php"
            class="nav-link <?php echo isActive('clientes.php'); ?>"
            title="Gestao de Clientes">
            <i class="fas fa-users"></i>
            <span>Clientes</span>
        </a>

        <a href="<?php echo PUBLIC_URL_RELATIVE; ?>relatorios.php"
            class="nav-link <?php echo isActive('relatorios.php'); ?>"
            title="Relatorios">
            <i class="fas fa-file-alt"></i>
            <span>Relatorios</span>
        </a>

        <a href="<?php echo PUBLIC_URL_RELATIVE; ?>configuracoes.php"
            class="nav-link <?php echo isActive('configuracoes.php'); ?>"
            title="Configuracoes">
            <i class="fas fa-cog"></i>
            <span>Configuracoes</span>
        </a>

        <div class="nav-separator"></div>

        <div class="nav-user">
            <?php if ($usuarioAutenticado): ?>
                <div class="nav-user-info">
                    <span class="nav-user-name">
                        <?php echo htmlspecialchars($usuarioAutenticado['nome']); ?>
                    </span>
                    <span class="nav-user-email">
                        <?php echo htmlspecialchars($usuarioAutenticado['email']); ?>
                    </span>
                </div>
                <button class="nav-user-menu-toggle" onclick="toggleUserMenu(event)">
                    <span class="user-avatar">
                        <?php echo strtoupper(substr($usuarioAutenticado['nome'], 0, 1)); ?>
                    </span>
                </button>
                <div class="nav-user-menu" id="userMenu">
                    <a href="<?php echo PUBLIC_URL_RELATIVE; ?>logout.php" class="nav-user-menu-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        Sair
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    function toggleUserMenu(event) {
        event.stopPropagation();
        const menu = document.getElementById('userMenu');
        menu.classList.toggle('show');
    }

    document.addEventListener('click', function(event) {
        const menu = document.getElementById('userMenu');
        const toggle = document.querySelector('.nav-user-menu-toggle');

        if (menu && toggle && !menu.contains(event.target) && !toggle.contains(event.target)) {
            menu.classList.remove('show');
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const menu = document.getElementById('userMenu');
            if (menu) {
                menu.classList.remove('show');
            }
        }
    });
</script>