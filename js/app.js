    // FinanzApp - JavaScript Frontend Logic

// State variables
let state = {
    income: 1000000,
    savingsGoal: 200000,
    expenses: [],
    currentFilter: 'all',
    searchQuery: ''
};

// DOM Elements cache
const body = document.body;
const themeToggle = document.getElementById("themeToggle");

const monthlyIncomeInput = document.getElementById("monthlyIncomeInput");
const btnSaveIncome = document.getElementById("btnSaveIncome");

const savingsGoalInput = document.getElementById("savingsGoalInput");
const btnSaveSavingsGoal = document.getElementById("btnSaveSavingsGoal");

const displayIncome = document.getElementById("displayIncome");
const displayExpenses = document.getElementById("displayExpenses");
const displayBalance = document.getElementById("displayBalance");
const displaySavingsGoal = document.getElementById("displaySavingsGoal");

const circleBar = document.getElementById("circleBar");
const percentageText = document.getElementById("percentageText");
const budgetWarning = document.getElementById("budgetWarning");

const savingsPercentageText = document.getElementById("savingsPercentageText");
const savingsBar = document.getElementById("savingsBar");
const savingsStatusText = document.getElementById("savingsStatusText");

const searchInput = document.getElementById("searchInput");
const categoryFilters = document.getElementById("categoryFilters");
const expensesList = document.getElementById("expensesList");
const btnOpenAddModal = document.getElementById("btnOpenAddModal");

const expenseModal = document.getElementById("expenseModal");
const modalTitle = document.getElementById("modalTitle");
const expenseId = document.getElementById("expenseId");
const expenseDescription = document.getElementById("expenseDescription");
const expenseAmount = document.getElementById("expenseAmount");
const expenseCategory = document.getElementById("expenseCategory");
const expenseDate = document.getElementById("expenseDate");
const expensePaymentMethod = document.getElementById("expensePaymentMethod");
const btnCancelExpense = document.getElementById("btnCancelExpense");
const btnSaveExpense = document.getElementById("btnSaveExpense");

const categoryDistribution = document.getElementById("categoryDistribution");
const toast = document.getElementById("toast");
const toastText = document.getElementById("toastText");

// ----------------------------------------------------
// Theme Management
// ----------------------------------------------------
const savedTheme = localStorage.getItem("theme") || "theme-dark";
body.className = savedTheme;

themeToggle.addEventListener("click", () => {
    if (body.classList.contains("theme-light")) {
        body.className = "theme-dark";
        localStorage.setItem("theme", "theme-dark");
    } else {
        body.className = "theme-light";
        localStorage.setItem("theme", "theme-light");
    }
});



// ----------------------------------------------------
// Toast Notification Helper
// ----------------------------------------------------
function showToast(message) {
    toastText.textContent = message;
    toast.classList.add("show");
    setTimeout(() => {
        toast.classList.remove("show");
    }, 3000);
}

// Format number to currency
function formatCurrency(val) {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        minimumFractionDigits: 0
    }).format(val);
}

// ----------------------------------------------------
// Core Calculation & Render Engine
// ----------------------------------------------------
function render() {
    // 1. Calculate Totals
    const totalExpenses = state.expenses.reduce((sum, item) => sum + parseFloat(item.amount), 0);
    const balance = state.income - totalExpenses;
    
    // Update summary labels
    displayIncome.textContent = formatCurrency(state.income);
    displayExpenses.textContent = formatCurrency(totalExpenses);
    displayBalance.textContent = formatCurrency(balance);
    
    // Balance color indicators
    if (balance < 0) {
        displayBalance.className = "balance-val balance-available deficit";
        budgetWarning.style.display = "flex";
    } else {
        displayBalance.className = "balance-val balance-available";
        budgetWarning.style.display = "none";
    }

    // Update circular progress bar
    const percentage = state.income > 0 ? (totalExpenses / state.income) * 100 : 0;
    percentageText.textContent = `${Math.round(percentage)}%`;

    // Circumference = 2 * PI * r (r=80) = ~502
    const offset = 502 - (502 * Math.min(percentage, 100) / 100);
    circleBar.style.strokeDashoffset = offset;
    
    // Adjust colors of circular bar based on percentage
    if (percentage > 100) {
        circleBar.style.stroke = "var(--accent-danger)";
    } else if (percentage > 85) {
        circleBar.style.stroke = "var(--accent-warning)";
    } else {
        circleBar.style.stroke = "url(#accent-grad-svg)";
    }

    // Update savings goal UI and progress
    displaySavingsGoal.textContent = formatCurrency(state.savingsGoal);
    
    let savingsPercentage = 0;
    if (state.savingsGoal > 0) {
        if (balance > 0) {
            savingsPercentage = (balance / state.savingsGoal) * 100;
        }
    }
    
    const roundedSavingsPct = Math.round(savingsPercentage);
    savingsPercentageText.textContent = `${roundedSavingsPct}%`;
    savingsBar.style.width = `${Math.min(savingsPercentage, 100)}%`;

    const savingsProgressBox = document.querySelector(".savings-progress-box");
    if (savingsPercentage >= 100) {
        savingsProgressBox.classList.add("completed");
        savingsStatusText.textContent = "¡Felicidades! Has alcanzado tu meta de ahorro mensual.";
    } else {
        savingsProgressBox.classList.remove("completed");
        if (balance <= 0) {
            savingsStatusText.textContent = "No tienes ahorros disponibles este mes debido al déficit.";
        } else {
            const missingAmount = state.savingsGoal - balance;
            savingsStatusText.textContent = `Te faltan ${formatCurrency(missingAmount)} para alcanzar tu meta de ahorro.`;
        }
    }

    // 2. Render Categories Distribution
    const categoriesList = ['Comida', 'Transporte', 'Servicios', 'Entretenimiento', 'Otros'];
    const catTotals = {};
    categoriesList.forEach(c => catTotals[c] = 0);
    state.expenses.forEach(item => {
        if (catTotals[item.category] !== undefined) {
            catTotals[item.category] += parseFloat(item.amount);
        } else {
            catTotals['Otros'] += parseFloat(item.amount);
        }
    });

    categoryDistribution.innerHTML = "";
    categoriesList.forEach(cat => {
        const amt = catTotals[cat];
        const pct = state.income > 0 ? (amt / state.income) * 100 : 0;
        
        const catRow = document.createElement("div");
        catRow.className = "category-progress-row";
        catRow.innerHTML = `
            <div class="cat-progress-labels">
                <span class="cat-name">${cat}</span>
                <span class="cat-values">${formatCurrency(amt)} (${Math.round(pct)}%)</span>
            </div>
            <div class="cat-progress-track">
                <div class="cat-progress-bar category-${cat.toLowerCase()}" style="width: ${Math.min(pct, 100)}%;"></div>
            </div>
        `;
        categoryDistribution.appendChild(catRow);
    });

    // 3. Render Transactions List with Filters & Search
    expensesList.innerHTML = "";

    const filteredExpenses = state.expenses.filter(item => {
        const matchesCategory = state.currentFilter === 'all' || item.category === state.currentFilter;
        const matchesSearch = item.description.toLowerCase().includes(state.searchQuery.toLowerCase()) ||
                              item.category.toLowerCase().includes(state.searchQuery.toLowerCase());
        return matchesCategory && matchesSearch;
    });

    if (filteredExpenses.length === 0) {
        expensesList.innerHTML = `<div class="empty-list">No se encontraron transacciones.</div>`;
        return;
    }

    filteredExpenses.forEach(item => {
        const row = document.createElement("div");
        row.className = "expense-item-row";
        row.dataset.id = item.id;
        
        // Format date beautifully
        const dateParts = item.date.split('-');
        let formattedDate = item.date;
        if (dateParts.length === 3) {
            formattedDate = `${dateParts[2]}/${dateParts[1]}/${dateParts[0]}`;
        }

        row.innerHTML = `
            <div class="expense-desc-block">
                <span class="expense-desc-title">${escapeHTML(item.description)}</span>
                <span class="expense-desc-date">${formattedDate}</span>
            </div>
            <div class="expense-cat-block">
                <span class="badge badge-cat category-${item.category.toLowerCase()}">${item.category}</span>
                <span class="expense-pay-method">${item.payment_method}</span>
            </div>
            <div class="expense-amount-block">
                ${formatCurrency(item.amount)}
            </div>
            <div class="expense-actions-block">
                <button class="btn-item-action edit" title="Editar gasto">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                </button>
                <button class="btn-item-action delete" title="Eliminar gasto">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </div>
        `;

        // Register edit click
        row.querySelector(".edit").addEventListener("click", () => {
            openEditModal(item);
        });

        // Register delete click
        row.querySelector(".delete").addEventListener("click", () => {
            if (confirm(`¿Estás seguro de eliminar el gasto "${item.description}"?`)) {
                deleteExpense(item.id);
            }
        });

        expensesList.appendChild(row);
    });
}

// ----------------------------------------------------
// API Client / Backend Communication
// ----------------------------------------------------
function loadData() {
    fetch('api.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                state.income = parseFloat(data.income);
                state.savingsGoal = parseFloat(data.savings_goal || 200000);
                state.expenses = data.expenses || [];
                monthlyIncomeInput.value = state.income;
                savingsGoalInput.value = state.savingsGoal;
                render();
            } else {
                showToast("Error al cargar los datos de la base de datos.");
            }
        })
        .catch(err => {
            console.error("Error fetching data:", err);
            showToast("Error de conexión con la base de datos.");
        });
}

function saveIncome() {
    const incomeVal = parseFloat(monthlyIncomeInput.value);
    if (isNaN(incomeVal) || incomeVal < 0) {
        alert("Por favor ingresa un ingreso válido no negativo.");
        return;
    }

    fetch('api.php?action=save_income', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ income: incomeVal })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            state.income = data.income;
            render();
            showToast(data.message);
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => console.error("Error saving income:", err));
}

function saveSavingsGoal() {
    const savingsGoalVal = parseFloat(savingsGoalInput.value);
    if (isNaN(savingsGoalVal) || savingsGoalVal < 0) {
        alert("Por favor ingresa una meta de ahorro válida no negativa.");
        return;
    }

    fetch('api.php?action=save_savings_goal', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ savings_goal: savingsGoalVal })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            state.savingsGoal = data.savings_goal;
            render();
            showToast(data.message);
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => console.error("Error saving savings goal:", err));
}

function saveExpense() {
    const desc = expenseDescription.value.trim();
    const amt = parseFloat(expenseAmount.value);
    const cat = expenseCategory.value;
    const dt = expenseDate.value;
    const pm = expensePaymentMethod.value;
    const id = expenseId.value;

    if (!desc) {
        alert("La descripción es obligatoria.");
        return;
    }
    if (isNaN(amt) || amt <= 0) {
        alert("El monto debe ser un número mayor a cero.");
        return;
    }
    if (!dt) {
        alert("La fecha es obligatoria.");
        return;
    }

    const payload = {
        description: desc,
        amount: amt,
        category: cat,
        date: dt,
        payment_method: pm
    };

    if (id) {
        payload.id = parseInt(id);
    }

    fetch('api.php?action=save_expense', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            closeExpenseModal();
            loadData();
            showToast(data.message);
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => console.error("Error saving expense:", err));
}

function deleteExpense(id) {
    fetch('api.php?action=delete_expense', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            loadData();
            showToast(data.message);
        } else {
            alert("Error: " + data.error);
        }
    })
    .catch(err => console.error("Error deleting expense:", err));
}

// ----------------------------------------------------
// Modal Handlers
// ----------------------------------------------------
function openAddModal() {
    modalTitle.textContent = "Registrar Gasto";
    modalSub.textContent = "Ingresa los detalles de la transacción para actualizar tu billetera digital.";
    expenseId.value = "";
    expenseDescription.value = "";
    expenseAmount.value = "";
    expenseCategory.selectedIndex = 0;
    // Default date to today
    expenseDate.value = new Date().toISOString().split('T')[0];
    expensePaymentMethod.selectedIndex = 0;
    
    expenseModal.classList.add("show");
    expenseDescription.focus();
}

function openEditModal(expense) {
    modalTitle.textContent = "Editar Gasto";
    modalSub.textContent = "Modifica los detalles del registro seleccionado.";
    expenseId.value = expense.id;
    expenseDescription.value = expense.description;
    expenseAmount.value = expense.amount;
    expenseCategory.value = expense.category;
    expenseDate.value = expense.date;
    expensePaymentMethod.value = expense.payment_method;

    expenseModal.classList.add("show");
    expenseDescription.focus();
}

function closeExpenseModal() {
    expenseModal.classList.remove("show");
}

// ----------------------------------------------------
// Filters and Search Events
// ----------------------------------------------------

// Category Filters click
categoryFilters.addEventListener("click", (e) => {
    const btn = e.target.closest(".filter-btn");
    if (!btn) return;

    document.querySelectorAll(".filter-btn").forEach(el => el.classList.remove("active"));
    btn.classList.add("active");
    
    state.currentFilter = btn.dataset.category;
    render();
});

// Search input keypress
searchInput.addEventListener("input", (e) => {
    state.searchQuery = e.target.value;
    render();
});

// Income Save click
btnSaveIncome.addEventListener("click", saveIncome);

// Savings Goal Save click
btnSaveSavingsGoal.addEventListener("click", saveSavingsGoal);

// Modal trigger buttons
btnOpenAddModal.addEventListener("click", openAddModal);
btnCancelExpense.addEventListener("click", closeExpenseModal);
btnSaveExpense.addEventListener("click", saveExpense);

// Close modal when clicking outside
expenseModal.addEventListener("click", (e) => {
    if (e.target === expenseModal) {
        closeExpenseModal();
    }
});

// Helper to escape HTML characters
function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>'"]/g, 
        tag => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            "'": '&#39;',
            '"': '&quot;'
        }[tag] || tag)
    );
}

// Initialize
loadData();
