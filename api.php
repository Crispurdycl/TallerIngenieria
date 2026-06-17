<?php
/**
 * Billetera Digital CRUD API
 * Handles GET and POST requests for expenses and budget.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/db/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    // 1. GET Requests: Read
    if ($method === 'GET') {
        if ($action === 'get_income') {
            $stmt = $db->prepare("SELECT value FROM settings WHERE key = 'monthly_income'");
            $stmt->execute();
            $income = $stmt->fetchColumn();
            echo json_encode(['success' => true, 'income' => floatval($income)]);
            exit;
        }

        if (isset($_GET['id'])) {
            // Read single expense
            $stmt = $db->prepare("SELECT * FROM expenses WHERE id = :id");
            $stmt->execute([':id' => intval($_GET['id'])]);
            $expense = $stmt->fetch();
            
            if ($expense) {
                echo json_encode(['success' => true, 'data' => $expense]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Gasto no encontrado.']);
            }
        } else {
            // Read all expenses and budget
            // Read income
            $stmtIncome = $db->prepare("SELECT value FROM settings WHERE key = 'monthly_income'");
            $stmtIncome->execute();
            $income = floatval($stmtIncome->fetchColumn() ?: 1000000);

            // Read savings goal
            $stmtSavings = $db->prepare("SELECT value FROM settings WHERE key = 'savings_goal'");
            $stmtSavings->execute();
            $savings_goal = floatval($stmtSavings->fetchColumn() ?: 200000);

            // Read expenses (ordered by date desc, then id desc)
            $stmtExpenses = $db->query("SELECT * FROM expenses ORDER BY date DESC, id DESC");
            $expenses = $stmtExpenses->fetchAll();

            echo json_encode([
                'success' => true,
                'income' => $income,
                'savings_goal' => $savings_goal,
                'expenses' => $expenses
            ]);
        }
        exit;
    }

    // 2. POST Requests: Create / Update / Delete
    if ($method === 'POST') {
        // Read raw input JSON
        $input_raw = file_get_contents('php://input');
        $input = json_decode($input_raw, true);
        
        if (!$input) {
            $input = $_POST;
        }

        // Action: Save Expense (Create or Update)
        if ($action === 'save_expense') {
            $description = isset($input['description']) ? trim($input['description']) : '';
            $amount = isset($input['amount']) ? floatval($input['amount']) : 0.0;
            $category = isset($input['category']) ? trim($input['category']) : '';
            $date = isset($input['date']) ? trim($input['date']) : '';
            $payment_method = isset($input['payment_method']) ? trim($input['payment_method']) : '';

            if (empty($description)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'La descripción es requerida.']);
                exit;
            }
            if ($amount <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El monto debe ser mayor a 0.']);
                exit;
            }
            if (empty($category)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'La categoría es requerida.']);
                exit;
            }
            if (empty($date)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'La fecha es requerida.']);
                exit;
            }
            if (empty($payment_method)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El método de pago es requerido.']);
                exit;
            }

            $id = isset($input['id']) ? intval($input['id']) : 0;

            if ($id > 0) {
                // UPDATE
                $stmt = $db->prepare("UPDATE expenses SET 
                    description = :description,
                    amount = :amount,
                    category = :category,
                    date = :date,
                    payment_method = :payment_method,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id");
                
                $stmt->execute([
                    ':description' => $description,
                    ':amount' => $amount,
                    ':category' => $category,
                    ':date' => $date,
                    ':payment_method' => $payment_method,
                    ':id' => $id
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Gasto actualizado exitosamente.', 'id' => $id]);
            } else {
                // CREATE
                $stmt = $db->prepare("INSERT INTO expenses 
                    (description, amount, category, date, payment_method) 
                    VALUES 
                    (:description, :amount, :category, :date, :payment_method)");
                
                $stmt->execute([
                    ':description' => $description,
                    ':amount' => $amount,
                    ':category' => $category,
                    ':date' => $date,
                    ':payment_method' => $payment_method
                ]);
                
                $new_id = $db->lastInsertId();
                echo json_encode(['success' => true, 'message' => 'Gasto registrado exitosamente.', 'id' => $new_id]);
            }
            exit;
        }

        // Action: Save Income / Budget
        if ($action === 'save_income') {
            $income = isset($input['income']) ? floatval($input['income']) : 0.0;
            if ($income < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El ingreso mensual no puede ser negativo.']);
                exit;
            }

            $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('monthly_income', :income)");
            $stmt->execute([':income' => strval($income)]);

            echo json_encode(['success' => true, 'message' => 'Ingreso mensual actualizado.', 'income' => $income]);
            exit;
        }

        // Action: Save Savings Goal
        if ($action === 'save_savings_goal') {
            $savings_goal = isset($input['savings_goal']) ? floatval($input['savings_goal']) : 0.0;
            if ($savings_goal < 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'La meta de ahorro no puede ser negativa.']);
                exit;
            }

            $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('savings_goal', :savings_goal)");
            $stmt->execute([':savings_goal' => strval($savings_goal)]);

            echo json_encode(['success' => true, 'message' => 'Meta de ahorro actualizada.', 'savings_goal' => $savings_goal]);
            exit;
        }

        // Action: Delete Expense
        if ($action === 'delete_expense') {
            $id = isset($input['id']) ? intval($input['id']) : 0;
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID de gasto inválido.']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM expenses WHERE id = :id");
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Gasto eliminado exitosamente.']);
            exit;
        }
    }

    // Default response for unhandled endpoints
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método o acción no permitida.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
