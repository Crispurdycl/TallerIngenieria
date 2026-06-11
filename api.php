<?php
/**
 * CalcuNota CRUD API
 * Handles GET, POST, and DELETE requests for subjects.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/db/db.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    $db = getDB();

    // 1. GET Requests: Read
    if ($method === 'GET') {
        if (isset($_GET['id'])) {
            // Read single subject
            $stmt = $db->prepare("SELECT * FROM subjects WHERE id = :id");
            $stmt->execute([':id' => intval($_GET['id'])]);
            $subject = $stmt->fetch();
            
            if ($subject) {
                echo json_encode(['success' => true, 'data' => $subject]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Asignatura no encontrada.']);
            }
        } else {
            // Read all subjects (ordered by last updated)
            $stmt = $db->query("SELECT id, name, scale, updated_at FROM subjects ORDER BY updated_at DESC");
            $subjects = $stmt->fetchAll();
            echo json_encode(['success' => true, 'data' => $subjects]);
        }
        exit;
    }

    // 2. POST Requests: Create / Update / Delete
    if ($method === 'POST') {
        // Read raw input JSON
        $input_raw = file_get_contents('php://input');
        $input = json_decode($input_raw, true);
        
        if (!$input) {
            // Fallback to post form fields if JSON decode fails
            $input = $_POST;
        }

        // Action: Save (Create or Update)
        if ($action === 'save') {
            $name = isset($input['name']) ? trim($input['name']) : '';
            if (empty($name)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El nombre de la asignatura es requerido.']);
                exit;
            }

            $scale = isset($input['scale']) ? $input['scale'] : 'chile';
            $passing_grade = isset($input['passing_grade']) ? floatval($input['passing_grade']) : 4.0;
            $exam_enabled = !empty($input['exam_enabled']) ? 1 : 0;
            $exam_weight = isset($input['exam_weight']) ? floatval($input['exam_weight']) : 30.0;
            $equal_weights = !empty($input['equal_weights']) ? 1 : 0;
            $grades_json = isset($input['grades_json']) ? $input['grades_json'] : '[]';

            // Validate JSON format
            if (!is_array(json_decode($grades_json, true))) {
                $grades_json = '[]';
            }

            $id = isset($input['id']) ? intval($input['id']) : 0;

            if ($id > 0) {
                // UPDATE
                $stmt = $db->prepare("UPDATE subjects SET 
                    name = :name,
                    scale = :scale,
                    passing_grade = :passing_grade,
                    exam_enabled = :exam_enabled,
                    exam_weight = :exam_weight,
                    equal_weights = :equal_weights,
                    grades_json = :grades_json,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id");
                
                $stmt->execute([
                    ':name' => $name,
                    ':scale' => $scale,
                    ':passing_grade' => $passing_grade,
                    ':exam_enabled' => $exam_enabled,
                    ':exam_weight' => $exam_weight,
                    ':equal_weights' => $equal_weights,
                    ':grades_json' => $grades_json,
                    ':id' => $id
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Asignatura actualizada.', 'id' => $id]);
            } else {
                // CREATE
                $stmt = $db->prepare("INSERT INTO subjects 
                    (name, scale, passing_grade, exam_enabled, exam_weight, equal_weights, grades_json) 
                    VALUES 
                    (:name, :scale, :passing_grade, :exam_enabled, :exam_weight, :equal_weights, :grades_json)");
                
                $stmt->execute([
                    ':name' => $name,
                    ':scale' => $scale,
                    ':passing_grade' => $passing_grade,
                    ':exam_enabled' => $exam_enabled,
                    ':exam_weight' => $exam_weight,
                    ':equal_weights' => $equal_weights,
                    ':grades_json' => $grades_json
                ]);
                
                $new_id = $db->lastInsertId();
                echo json_encode(['success' => true, 'message' => 'Asignatura guardada.', 'id' => $new_id]);
            }
            exit;
        }

        // Action: Delete
        if ($action === 'delete') {
            $id = isset($input['id']) ? intval($input['id']) : 0;
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'ID de asignatura inválido.']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM subjects WHERE id = :id");
            $stmt->execute([':id' => $id]);

            echo json_encode(['success' => true, 'message' => 'Asignatura eliminada exitosamente.']);
            exit;
        }
    }

    // Default response for unhandled endpoints
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error del servidor: ' . $e->getMessage()]);
}
