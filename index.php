<?php
/**
 * FinanzApp - Billetera Digital & Control de Gastos
 * Desarrollado para el Taller de Ingeniería Web
 */
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinanzApp - Billetera Digital & Control de Gastos</title>
    <meta name="description"
        content="Controla tus gastos mensuales dependiendo de tus ingresos. Una aplicación web moderna con base de datos SQLite para la gestión financiera.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Script -->
    <script src="js/app.js" defer></script>
</head>

<body class="theme-dark">
    <!-- SVG gradients declarations for circular progress -->
    <svg style="width:0;height:0;position:absolute;" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="accent-grad-svg" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#10b981" />
                <stop offset="100%" stop-color="#3b82f6" />
            </linearGradient>
        </defs>
    </svg>

    <header>
        <a href="." class="logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="4" width="20" height="16" rx="2" ry="2" />
                <line x1="12" y1="18" x2="12" y2="18" />
                <line x1="2" y1="10" x2="22" y2="10" />
            </svg>
            FinanzApp
        </a>
        <button class="theme-toggle-btn" id="themeToggle" aria-label="Cambiar tema">
            <!-- Sun Icon (shown in dark mode) -->
            <svg class="light-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="4" />
                <path
                    d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41" />
            </svg>
            <!-- Moon Icon (shown in light mode) -->
            <svg class="dark-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
            </svg>
        </button>
    </header>

    <main>
        <!-- Column 1: Info del Grupo, Documentación y Mockup -->
        <section class="card sidebar-info">
            <div class="sidebar-header">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                        <circle cx="9" cy="7" r="4" />
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                        <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                    Integrantes
                </h2>
            </div>
            <ul class="team-list">
                <li><span>1</span> Sergio Meza</li>
                <li><span>2</span> Constanza Silva</li>
                <li><span>3</span> Jorge Abarzua</li>
                <li><span>4</span> Cristian Cárdenas</li>
            </ul>

            <div class="sidebar-header" style="margin-top: 1.5rem;">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                        <polyline points="10 9 9 9 8 9" />
                    </svg>
                    Sobre la App
                </h2>
            </div>
            <div class="app-desc">
                <p><strong>FinanzApp</strong> es un gestor financiero personal que ayuda a los usuarios a registrar,
                    organizar y analizar sus gastos mensuales frente a sus ingresos. Ideal para mantener una salud
                    financiera óptima.</p>

                <h4
                    style="margin-top: 0.8rem; font-size: 0.85rem; text-transform: uppercase; color: var(--primary-color);">
                    Operaciones CRUD:</h4>
                <ul class="crud-desc">
                    <li><strong>Crear (Create):</strong> Registro de nuevos gastos especificando descripción, monto,
                        categoría, fecha y método de pago.</li>
                    <li><strong>Leer (Read):</strong> Visualización dinámica del historial, filtrado por categorías y
                        balance de saldo restante en tiempo real.</li>
                    <li><strong>Actualizar (Update):</strong> Modificación del ingreso mensual o edición de cualquier
                        gasto guardado previamente.</li>
                    <li><strong>Eliminar (Delete):</strong> Remoción permanente de gastos de la base de datos con
                        recálculo automático del saldo.</li>
                </ul>
                <p style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--text-muted);">Persistencia local
                    impulsada por <strong>SQLite</strong> a través de PHP PDO.</p>
            </div>


        </section>

        <!-- Column 2: Dashboard de Control y Listado de Gastos -->
        <section class="card main-dashboard">
            <div class="dashboard-header">
                <div>
                    <h1>Billetera Digital</h1>
                    <p class="subtitle">Administra tus finanzas de manera simple e inteligente. Controla tus gastos
                        según lo que ganas.</p>
                </div>
            </div>

            <!-- Configuración de Presupuesto y Ahorro -->
            <div class="budget-setup-box">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="monthlyIncomeInput">Tu Ingreso Mensual ($)</label>
                        <div class="income-input-wrapper">
                            <input type="number" id="monthlyIncomeInput" placeholder="Ej: 1000000" min="0" step="1000">
                            <button class="btn btn-primary" id="btnSaveIncome" title="Guardar Ingreso">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                </svg>
                                Guardar
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="savingsGoalInput">Tu Meta de Ahorro ($)</label>
                        <div class="income-input-wrapper">
                            <input type="number" id="savingsGoalInput" placeholder="Ej: 200000" min="0" step="1000">
                            <button class="btn btn-primary" id="btnSaveSavingsGoal" title="Guardar Meta de Ahorro">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                </svg>
                                Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                Transacciones del Mes
            </div>

            <!-- Filtros de categoría y Búsqueda -->
            <div class="filter-search-row">
                <div class="search-box">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" y1="21" x2="16.65" y2="16.65" />
                    </svg>
                    <input type="text" id="searchInput" placeholder="Buscar gasto por descripción...">
                </div>
                <div class="category-filters" id="categoryFilters">
                    <button class="filter-btn active" data-category="all">Todos</button>
                    <button class="filter-btn" data-category="Comida">Comida</button>
                    <button class="filter-btn" data-category="Transporte">Transp.</button>
                    <button class="filter-btn" data-category="Servicios">Servicios</button>
                    <button class="filter-btn" data-category="Entretenimiento">Entret.</button>
                    <button class="filter-btn" data-category="Otros">Otros</button>
                </div>
            </div>

            <!-- Listado de Gastos -->
            <div class="expenses-list-container">
                <div class="expenses-list-header">
                    <span>Gasto / Detalle</span>
                    <span>Categoría / Pago</span>
                    <span>Monto</span>
                    <span>Acciones</span>
                </div>
                <div class="expenses-list" id="expensesList">
                    <div class="empty-list">Cargando transacciones...</div>
                </div>
            </div>

            <!-- Botón flotante o fijo para registrar gasto -->
            <div class="add-expense-container">
                <button class="btn btn-primary" id="btnOpenAddModal">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.5">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Registrar Nuevo Gasto
                </button>
            </div>
        </section>

        <!-- Column 3: Resumen de Saldos y Gráficos -->
        <aside class="sticky-aside">
            <div class="card result-box">
                <h2>Resumen Financiero</h2>

                <!-- Progreso Circular del Presupuesto Gastado -->
                <div class="circle-progress-container">
                    <svg class="circle-svg">
                        <circle class="circle-bg" cx="90" cy="90" r="80" />
                        <circle class="circle-bar" id="circleBar" cx="90" cy="90" r="80" />
                    </svg>
                    <div class="circle-value">
                        <span class="circle-number" id="percentageText">0%</span>
                        <span class="circle-label">Consumido</span>
                    </div>
                </div>

                <!-- Tarjetas de Saldos -->
                <div class="balance-card">
                    <div class="balance-item">
                        <div class="balance-label">Ingreso Total</div>
                        <div class="balance-val income" id="displayIncome">$0</div>
                    </div>
                    <div class="balance-item">
                        <div class="balance-label">Gasto Total</div>
                        <div class="balance-val expense" id="displayExpenses">$0</div>
                    </div>
                    <hr class="balance-divider">
                    <div class="balance-item">
                        <div class="balance-label">Saldo Disponible (Ahorro Actual)</div>
                        <div class="balance-val balance-available" id="displayBalance">$0</div>
                    </div>
                    <div class="balance-item">
                        <div class="balance-label">Meta de Ahorro</div>
                        <div class="balance-val" style="color: var(--primary-color);" id="displaySavingsGoal">$0</div>
                    </div>
                </div>

                <!-- Savings Goal Progress bar -->
                <div class="savings-progress-box">
                    <div class="savings-labels">
                        <span class="savings-label-title">Progreso de Ahorro</span>
                        <span class="savings-percentage" id="savingsPercentageText">0%</span>
                    </div>
                    <div class="savings-track">
                        <div class="savings-bar" id="savingsBar"></div>
                    </div>
                    <div class="savings-status" id="savingsStatusText">Calculando progreso...</div>
                </div>

                <!-- Alerta de presupuesto -->
                <div class="warning-msg" id="budgetWarning" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z" />
                        <line x1="12" y1="9" x2="12" y2="13" />
                        <line x1="12" y1="17" x2="12.01" y2="17" />
                    </svg>
                    <span>¡Cuidado! Has superado tu presupuesto mensual.</span>
                </div>
            </div>

            <!-- Distribución por Categorías -->
            <div class="card categories-box">
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10" />
                        <line x1="12" y1="20" x2="12" y2="4" />
                        <line x1="6" y1="20" x2="6" y2="14" />
                    </svg>
                    Distribución de Gastos
                </h3>
                <div class="category-distribution-list" id="categoryDistribution">
                    <!-- Dynamic rendering of categories spent -->
                    <div class="empty-list" style="padding: 0;">Sin datos registrados.</div>
                </div>
            </div>
        </aside>
    </main>

    <!-- Modal Form: Agregar/Editar Gasto -->
    <div class="modal-overlay" id="expenseModal">
        <div class="modal-card">
            <h3 id="modalTitle">Registrar Gasto</h3>
            <p id="modalSub">Ingresa los detalles de la transacción para actualizar tu billetera digital.</p>

            <input type="hidden" id="expenseId" value="">

            <div class="form-group">
                <label for="expenseDescription">Descripción</label>
                <input type="text" id="expenseDescription" placeholder="Ej: Arriendo, Supermercado..."
                    autocomplete="off">
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="expenseAmount">Monto ($)</label>
                    <input type="number" id="expenseAmount" placeholder="Ej: 5000" min="1">
                </div>
                <div class="form-group">
                    <label for="expenseCategory">Categoría</label>
                    <select id="expenseCategory">
                        <option value="Comida">Comida</option>
                        <option value="Transporte">Transporte</option>
                        <option value="Servicios">Servicios</option>
                        <option value="Entretenimiento">Entretenimiento</option>
                        <option value="Otros">Otros</option>
                    </select>
                </div>
            </div>

            <div class="form-grid">
                <div class="form-group">
                    <label for="expenseDate">Fecha</label>
                    <input type="date" id="expenseDate">
                </div>
                <div class="form-group">
                    <label for="expensePaymentMethod">Método de Pago</label>
                    <select id="expensePaymentMethod">
                        <option value="Tarjeta">Tarjeta de Débito/Crédito</option>
                        <option value="Transferencia">Transferencia Electrónica</option>
                        <option value="Efectivo">Efectivo</option>
                    </select>
                </div>
            </div>

            <div class="modal-buttons">
                <button class="btn btn-secondary" id="btnCancelExpense">Cancelar</button>
                <button class="btn btn-primary" id="btnSaveExpense">Guardar Transacción</button>
            </div>
        </div>
    </div>



    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.5">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3" />
        </svg>
        <span class="toast-text" id="toastText">Operación exitosa</span>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> FinanzApp - Taller de Ingeniería Web. Desarrollado por el Grupo.</p>
    </footer>
</body>

</html>