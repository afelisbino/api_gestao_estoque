<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.


$routes->group('api', static function ($routes) {
    $routes->group('empresa', static function ($routes) {
        $routes->post('novo', 'EmpresaController::cadastrarNovaEmpresa');
        $routes->group('usuario', static function ($routes) {
            $routes->post('primeiro', 'UsuarioController::cadastrarPrimeiroUsuarioEmpresa');
        });
    });

    $routes->group('usuario', static function ($routes) {
        $routes->group('autenticacao', static function ($routes) {
            $routes->post('login', 'AutenticacaoController::autenticarUsuario');
            $routes->patch('logoff', 'AutenticacaoController::desautenticarUsuario', ['filter' => 'auth']);
        });
    });

    $routes->group('categoria', static function ($routes) {
        $routes->post('cadastrar', 'CategoriaController::cadastrarCategoria', ['filter' => 'auth']);
        $routes->patch('editar', 'CategoriaController::editarCategoria', ['filter' => 'auth']);
        $routes->delete('excluir', 'CategoriaController::deletarCategoria', ['filter' => 'auth']);
        $routes->get('listar', 'CategoriaController::listarCategoria', ['filter' => 'auth']);
    });

    $routes->group('fornecedor', static function ($routes) {
        $routes->post('cadastrar', 'FornecedorController::cadastrarFornecedor', ['filter' => 'auth']);
        $routes->put('editar', 'FornecedorController::editarFornecedor', ['filter' => 'auth']);
        $routes->delete('excluir', 'FornecedorController::deletarFornecedor', ['filter' => 'auth']);
        $routes->get('listar', 'FornecedorController::listarFornecedores', ['filter' => 'auth']);
        $routes->get('buscar', 'FornecedorController::buscarFornecedor', ['filter' => 'auth']);
    });

    $routes->group('cardapio', static function ($routes) {
        $routes->post('cadastrar', 'CardapioController::cadastrarProdutoCardapio', ['filter' => 'auth']);
        $routes->put('alterar', 'CardapioController::alterarProdutoCardapio', ['filter' => 'auth']);
        $routes->patch('ativar', 'CardapioController::ativarProdutoCardapio', ['filter' => 'auth']);
        $routes->patch('desativar', 'CardapioController::desativarProdutoCardapio', ['filter' => 'auth']);
        $routes->group('listar', static function ($routes) {
            $routes->get('todos', 'CardapioController::listarTodosProdutosCardapioEmpresa', ['filter' => 'auth']);
            $routes->get('ativos', 'CardapioController::listarProdutosAtivosCardapioEmpresa', ['filter' => 'auth']);
        });
    });

    $routes->group('produto', static function ($routes) {
        $routes->post('cadastrar', 'ProdutoController::cadastrarProduto', ['filter' => 'auth']);
        $routes->put('alterar', 'ProdutoController::alterarDadosProduto', ['filter' => 'auth']);
        $routes->patch('ativar', 'ProdutoController::ativarProduto', ['filter' => 'auth']);
        $routes->patch('desativar', 'ProdutoController::desativarProduto', ['filter' => 'auth']);
        $routes->get('buscar', 'ProdutoController::buscarDadosProduto', ['filter' => 'auth']);
        $routes->group('listar', static function ($routes) {
            $routes->get('todos', 'ProdutoController::listarTodosProdutosEstoque', ['filter' => 'auth']);
            $routes->get('ativos', 'ProdutoController::listarProdutosAtivosEmpresa', ['filter' => 'auth']);
        });
        $routes->group('codigo_barras', static function ($routes) {
            $routes->post('adicionar', 'ProdutoController::adicionarCodigoBarrasProduto', ['filter' => 'auth']);
            $routes->delete('remover', 'ProdutoController::deletarCodigoBarras', ['filter' => 'auth']);
            $routes->get('listar', 'ProdutoController::listarTodosCodigosBarrasProduto', ['filter' => 'auth']);
        });
    });

    $routes->group('estoque', static function ($routes) {
        $routes->group('historico', static function ($routes) {
            $routes->get('todos', 'ProdutoController::listarHistoricoEstoqueEmpresa', ['filter' => 'auth']);
            $routes->get('produto', 'ProdutoController::listarHistoricoEstoqueProduto', ['filter' => 'auth']);
        });

        $routes->patch('entrada', 'ProdutoController::adicionarEstoqueProduto', ['filter' => 'auth']);
        $routes->patch('saida', 'ProdutoController::retirarEstoqueProduto', ['filter' => 'auth']);
    });

    $routes->group('venda', static function ($routes) {
        $routes->group('listar', static function ($routes) {
            $routes->get('fiado', "VendaController::listarVendaFiadoAberto", ['filter' => 'auth']);
            $routes->get('itens', "VendaController::listarItensVenda", ['filter' => 'auth']);
        });
        $routes->group('fiado', static function ($routes) {
            $routes->patch('pagar', 'VendaController::pagaVendaFiado', ['filter' => 'auth']);
        });
        $routes->group('registrar', static function ($routes) {
            $routes->group('local', static function ($routes) {
                $routes->post('normal', "VendaController::cadastrarVendaLocalNormal", ['filter' => 'auth']);
                $routes->post('fiado', "VendaController::cadastrarVendaLocalFiado", ['filter' => 'auth']);
            });
        });
    });

    $routes->group("caixa", static function ($routes) {
        $routes->group('registrar', static function ($routes) {
            $routes->post('movimentacao', "MovimentacaoCaixaController::registraMovimentacao", ['filter' => 'auth']);
        });
    });

    $routes->group('relatorio', static function ($routes) {
        $routes->group('venda', static function ($routes) {
            $routes->group('local', static function ($routes) {
                $routes->get('info', 'RelatorioController::buscaQuantidadeVendasLocal', ['filter' => 'auth']);
                $routes->get('lista', 'RelatorioController::listaVendasLocalFinalizadaPeriodo', ['filter' => 'auth']);
                $routes->get('estatisticas/geral', 'RelatorioController::buscaEstatisticasVendasUltimosSeteDias', ['filter' => 'auth']);
            });
        });

        $routes->group('estoque', static function ($routes) {
            $routes->get('listar', "RelatorioController::buscaListaEstoqueEmpresa", ['filter' => 'auth']);
            $routes->get('estatisticas', "RelatorioController::buscaEstatisticasEstoqueEmpresa", ['filter' => 'auth']);
        });

        $routes->group('caixa', static function ($routes) {
            $routes->get('periodo', "RelatorioController::buscaEstatisticasCaixaEmpresaPeriodo", ['filter' => 'auth']);
            $routes->get('mensal', "RelatorioController::buscaEstatisticasCaixaEmpresaMensal", ['filter' => 'auth']);
            $routes->get('anual', "RelatorioController::buscaEstatisticasCaixaEmpresaAnual", ['filter' => 'auth']);
        });
    });

    $routes->group('importacao', static function ($routes) {
        $routes->post('categoria', 'ImportacaoDadosController::importaCategorias', ['filter' => 'auth']);
        $routes->post('fornecedor', 'ImportacaoDadosController::importaFornecedores', ['filter' => 'auth']);
        $routes->post('produto', 'ImportacaoDadosController::importaProdutos', ['filter' => 'auth']);
        $routes->post('venda', 'ImportacaoDadosController::importaVendas', ['filter' => 'auth']);
        $routes->post('movimentacao', 'ImportacaoDadosController::importaMovimentacoesManuais', ['filter' => 'auth']);
    });
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
