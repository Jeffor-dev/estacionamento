<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MotoristaController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ControleEstacionamentoController;
use App\Http\Controllers\CupomController;

use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('welcome');

//dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

//motorista
Route::get('/motorista', [MotoristaController::class, 'index'])->middleware(['auth', 'verified'])->name('motorista.index');
// Route::get('/motorista/{id}', [MotoristaController::class, 'visualizar'])->middleware(['auth', 'verified'])->name('motorista.visualizar');
Route::get('/motorista/cadastro', [MotoristaController::class, 'cadastroMotorista'])->middleware(['auth', 'verified'])->name('motorista.cadastro');
Route::post('/motorista', [MotoristaController::class, 'cadastrar'])->middleware(['auth', 'verified'])->name('motorista.cadastrar');

Route::get('/relatorio', [RelatorioController::class, 'index'])->middleware(['auth', 'verified'])->name('relatorio.index');
Route::get('/estacionamento', [ControleEstacionamentoController::class, 'index'])->middleware(['auth', 'verified'])->name('estacionamento.index');
Route::get('/estacionamento/cadastro', [ControleEstacionamentoController::class, 'cadastroEstacionamento'])->middleware(['auth', 'verified'])->name('estacionamento.cadastro');
Route::post('/estacionamento', [ControleEstacionamentoController::class, 'cadastrar'])->middleware(['auth', 'verified'])->name('estacionamento.cadastrar');
Route::put('/estacionamento/saida/{id}', [ControleEstacionamentoController::class, 'registrarSaida'])->middleware(['auth', 'verified'])->name('estacionamento.saida');

// Rota para gerar cupom
Route::get('/cupom/{id}', [CupomController::class, 'gerarCupom'])->middleware(['auth', 'verified'])->name('cupom.gerar');

Route::get('/account', function () {
    return Inertia::render('Account', [
        'title' => 'My Account'
    ]);
})->middleware(['auth', 'verified'])->name('account.index');

Route::post('/notify/{type}', function ($type) {
    return back()->toast('This notification comes from the server side =)', $type);
});

Route::get('/dialog/{type}/{position?}', function ($type, $position = null) {
    $page = [
        'modal' => 'WelcomeModal',
        'slideover' => 'WelcomeSlideOver'
    ][$type];

    return Inertia::modal($page)
        ->with([
            'title' => 'One modal to rule them all!',
            'message' => 'That\'s right! I\'m a modal coming from the far, far away kingdom of the Server...',
            'position' => $position
        ])
        ->baseRoute('welcome');
});
