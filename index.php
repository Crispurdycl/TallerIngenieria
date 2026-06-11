<?php
/**
 * CalcuNota - Calculadora de Promedio Ponderado de Notas
 * Diseño minimalista con base de datos CRUD para la gestión de asignaturas.
 */

// Parse initial parameters from URL for sharing state
$scale = isset($_GET['scale']) ? $_GET['scale'] : 'chile';
$grades_param = isset($_GET['grades']) ? $_GET['grades'] : '';
$exam_enabled = isset($_GET['exam_enabled']) ? $_GET['exam_enabled'] === 'true' : false;
$exam_weight = isset($_GET['exam_weight']) ? floatval($_GET['exam_weight']) : 30;
$passing_grade = isset($_GET['pass']) ? floatval($_GET['pass']) : null;
$equal_weights = isset($_GET['equal']) ? $_GET['equal'] === 'true' : false;

// Decode grades if present (format: grade:weight,grade:weight)
$initial_grades = [];
if (!empty($grades_param)) {
    $parts = explode(',', $grades_param);
    foreach ($parts as $part) {
        $subparts = explode(':', $part);
        if (count($subparts) === 2) {
            $initial_grades[] = [
                'grade' => floatval($subparts[0]),
                'weight' => floatval($subparts[1])
            ];
        }
    }
}

// Fallback to defaults if no grades provided
if (empty($initial_grades)) {
    $initial_grades = [
        ['grade' => '', 'weight' => ''],
        ['grade' => '', 'weight' => ''],
        ['grade' => '', 'weight' => '']
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calculadora de Promedio Ponderado - CalcuNota</title>
    <meta name="description" content="Calcula tu promedio ponderado de notas y la calificación que necesitas en el examen final para aprobar el curso. Guarda tus asignaturas en la base de datos de manera organizada.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Modularized CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Modularized Script -->
    <script src="js/app.js" defer></script>
</head>
<body class="theme-light">
    <!-- SVG gradients declarations -->
    <svg style="width:0;height:0;position:absolute;" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="accent-grad-svg" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" stop-color="#6366f1" />
                <stop offset="100%" stop-color="#a855f7" />
            </linearGradient>
        </defs>
    </svg>

    <header>
        <a href="." class="logo">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            CalcuNota
        </a>
        <button class="theme-toggle-btn" id="themeToggle" aria-label="Cambiar tema">
            <!-- Sun Icon (shown in dark mode) -->
            <svg class="light-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
            <!-- Moon Icon (shown in light mode) -->
            <svg class="dark-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
        </button>
    </header>

    <main>
        <!-- Column 1: Subjects List & CRUD Panel -->
        <section class="card subjects-sidebar">
            <div class="sidebar-header">
                <h2>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5v-15z"/></svg>
                    Mis Asignaturas
                </h2>
                <span class="subject-badge" id="subjectsCount">0</span>
            </div>

            <!-- List rendered dynamically by API calls -->
            <div class="subjects-list" id="subjectsList">
                <div class="empty-subjects">Cargando asignaturas...</div>
            </div>

            <!-- Subject Database Actions -->
            <div style="display:flex; flex-direction:column; gap:0.5rem; margin-top:auto; border-top: 1px solid var(--border-color); padding-top: 1rem;">
                <button class="btn btn-primary" id="btnSaveSubject" style="width:100%;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Guardar
                </button>
                <button class="btn btn-secondary" id="btnSaveSubjectAs" style="width:100%; display:none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Guardar como nuevo...
                </button>
            </div>
        </section>

        <!-- Column 2: Grade inputs -->
        <section class="card">
            <h1>Calcular Promedio Ponderado</h1>
            <p class="subtitle">Calcula tus notas de forma simple y elegante. Determina la calificación exacta que necesitas en tu examen final para aprobar tu asignatura.</p>

            <h2 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.1a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                Configuración del Curso
            </h2>

            <div class="settings-grid">
                <div class="form-group">
                    <label for="scaleSelect">Escala de Notas</label>
                    <select id="scaleSelect">
                        <option value="chile" <?php echo $scale === 'chile' ? 'selected' : ''; ?>>Chile (1.0 - 7.0)</option>
                        <option value="spain" <?php echo $scale === 'spain' ? 'selected' : ''; ?>>España / México (0.0 - 10.0)</option>
                        <option value="colombia" <?php echo $scale === 'colombia' ? 'selected' : ''; ?>>Colombia (0.0 - 5.0)</option>
                        <option value="usa" <?php echo $scale === 'usa' ? 'selected' : ''; ?>>Porcentaje (0 - 100)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="passingGrade">Nota de Aprobación</label>
                    <input type="number" id="passingGrade" step="0.1" placeholder="Ej: 4.0" value="<?php echo !is_null($passing_grade) ? $passing_grade : ''; ?>">
                </div>
            </div>

            <h2 class="section-title">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                Calificaciones y Ponderaciones
            </h2>

            <div class="grades-container" id="gradesContainer">
                <?php foreach ($initial_grades as $index => $item): ?>
                    <div class="grade-row">
                        <span class="grade-label">Nota <?php echo $index + 1; ?></span>
                        <div class="input-wrapper">
                            <input type="number" class="grade-input" step="any" placeholder="Nota" value="<?php echo $item['grade']; ?>">
                        </div>
                        <div class="input-wrapper">
                            <input type="number" class="weight-input" min="0" max="100" placeholder="Peso" value="<?php echo $item['weight']; ?>">
                            <span class="input-suffix">%</span>
                        </div>
                        <button class="btn-delete" title="Eliminar nota" <?php echo ($index === 0) ? 'style="display:none;"' : ''; ?>>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2M10 11v6M14 11v6"/></svg>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="control-row">
                <label class="checkbox-label">
                    <input type="checkbox" id="equalWeights" <?php echo $equal_weights ? 'checked' : ''; ?>>
                    Ponderaciones iguales
                </label>
                <button class="btn btn-secondary" id="btnAddGrade">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                    Agregar Nota
                </button>
            </div>

            <!-- Optional Exam Section -->
            <div class="exam-section">
                <div class="switch-container">
                    <div class="switch-title-desc">
                        <span class="switch-title">Incluir Examen Final</span>
                        <span class="switch-desc">Determina la nota mínima requerida en el examen para pasar la materia.</span>
                    </div>
                    <label class="switch">
                        <input type="checkbox" id="examSwitch" <?php echo $exam_enabled ? 'checked' : ''; ?>>
                        <span class="slider"></span>
                    </label>
                </div>

                <div class="exam-inputs-wrapper <?php echo $exam_enabled ? 'expanded' : ''; ?>" id="examInputsWrapper">
                    <div class="form-group" style="max-width: 250px;">
                        <label for="examWeight">Ponderación Examen Final</label>
                        <div class="input-wrapper">
                            <input type="number" id="examWeight" min="1" max="99" value="<?php echo $exam_weight; ?>">
                            <span class="input-suffix">%</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Column 3: Results -->
        <aside class="sticky-aside">
            <div class="card result-box">
                <h2>Tu Resultado</h2>
                
                <div class="circle-progress-container">
                    <svg class="circle-svg">
                        <circle class="circle-bg" cx="90" cy="90" r="80" />
                        <circle class="circle-bar" id="circleBar" cx="90" cy="90" r="80" />
                    </svg>
                    <div class="circle-value">
                        <span class="circle-number" id="averageText">-</span>
                        <span class="circle-label">Promedio</span>
                    </div>
                </div>

                <div class="status-badge pending" id="statusBadge" style="display: none;">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <span id="statusLabel">Cursando</span>
                </div>

                <div class="exam-target-card" id="examTargetCard" style="display: none;">
                    <div class="exam-target-title">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5zM6 12v5c0 2 2.5 3 6 3s6-1 6-3v-5"/></svg>
                        Meta de Examen Final
                    </div>
                    <div class="exam-target-value" id="examTargetValue">-</div>
                    <div class="exam-target-detail" id="examTargetDetail"></div>
                </div>

                <div class="weight-progress-wrapper">
                    <div class="weight-progress-labels">
                        <span>Ponderación Acumulada</span>
                        <span id="weightSumText">0%</span>
                    </div>
                    <div class="weight-progress-track">
                        <div class="weight-progress-bar" id="weightProgressBar"></div>
                    </div>
                    <div class="warning-msg" id="weightWarning">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        El peso total no debe superar el 100%
                    </div>
                </div>
            </div>

            <div class="aside-actions">
                <button class="btn btn-secondary" id="btnClear" title="Limpiar todos los datos">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                    Limpiar
                </button>
                <button class="btn btn-primary" id="btnShare" title="Copiar enlace con tus notas">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8M16 6l-4-4-4 4M12 2v13"/></svg>
                    Compartir
                </button>
            </div>
        </aside>
    </main>

    <!-- Modal for Saving Subject Name -->
    <div class="modal-overlay" id="saveModal">
        <div class="modal-card">
            <h3>Guardar Asignatura</h3>
            <p>Ingresa un nombre para poder guardar tu asignatura y tus notas en la base de datos local.</p>
            <div class="form-group">
                <label for="modalSubjectName">Nombre de la Asignatura</label>
                <input type="text" id="modalSubjectName" placeholder="Ej: Matemáticas I" autocomplete="off">
            </div>
            <div class="modal-buttons">
                <button class="btn btn-secondary" id="btnModalCancel">Cancelar</button>
                <button class="btn btn-primary" id="btnModalSave">Guardar</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3"/></svg>
        <span class="toast-text" id="toastText">Enlace copiado al portapapeles</span>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> CalcuNota. Hecho con ❤️ para estudiantes. Enfocado exclusivamente en tus metas.</p>
    </footer>
</body>
</html>
