<?php
/**
 * CalcuNota - Calculadora de Promedio Ponderado de Notas
 * Un diseño minimalista, moderno y enfocado exclusivamente en calificaciones.
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
    <meta name="description" content="Calcula tu promedio ponderado de notas y la calificación que necesitas en el examen final para aprobar el curso. Diseño moderno, minimalista y rápido.">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Theme Light Variables */
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --border-color: #e2e8f0;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            
            --primary-grad: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --primary-color: #6366f1;
            --accent-success: #10b981;
            --accent-warning: #f59e0b;
            --accent-danger: #ef4444;
            --bg-input: #f1f5f9;
            
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --inner-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.02);
            --transition-speed: 0.3s;
        }

        .theme-dark {
            /* Theme Dark Variables */
            --bg-body: #0b0f19;
            --bg-card: #151d30;
            --border-color: #242f49;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            
            --primary-grad: linear-gradient(135deg, #818cf8 0%, #a78bfa 100%);
            --primary-color: #818cf8;
            --accent-success: #34d399;
            --accent-warning: #fbbf24;
            --accent-danger: #f87171;
            --bg-input: #1e293b;
            
            --card-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.3), 0 10px 15px -6px rgba(0, 0, 0, 0.3);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: background-color var(--transition-speed), color var(--transition-speed);
        }

        header {
            padding: 2rem 1.5rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            background: var(--primary-grad);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .logo svg {
            stroke: #8b5cf6;
            width: 28px;
            height: 28px;
        }

        .theme-toggle-btn {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            padding: 0.6rem;
            border-radius: 50%;
            cursor: pointer;
            box-shadow: var(--card-shadow);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-toggle-btn:hover {
            transform: scale(1.05);
            border-color: var(--primary-color);
        }

        .theme-toggle-btn svg {
            width: 20px;
            height: 20px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        .dark-icon { display: none; }
        .theme-dark .light-icon { display: none; }
        .theme-dark .dark-icon { display: block; }

        main {
            max-width: 1250px;
            width: 100%;
            margin: 0 auto;
            padding: 0 1.5rem 4rem;
            flex-grow: 1;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
        }

        @media (max-width: 968px) {
            main {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1.25rem;
            padding: 2rem;
            box-shadow: var(--card-shadow);
            transition: background-color var(--transition-speed), border-color var(--transition-speed);
        }

        .sticky-aside {
            position: sticky;
            top: 2rem;
            align-self: start;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        h1, h2, h3 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        h1 {
            font-size: 2.2rem;
            line-height: 1.2;
        }

        .subtitle {
            color: var(--text-secondary);
            font-size: 1rem;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        /* Config Card Section */
        .section-title {
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .section-title svg {
            width: 18px;
            height: 18px;
            stroke: var(--primary-color);
            stroke-width: 2;
            fill: none;
        }

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        @media (max-width: 480px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .select-wrapper, .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        select, input[type="number"], input[type="text"] {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
            background-color: var(--bg-input);
            color: var(--text-primary);
            font-size: 0.95rem;
            font-weight: 500;
            outline: none;
            transition: all 0.2s;
        }

        select:focus, input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
        }

        /* Grades List Table-Like Layout */
        .grades-container {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .grade-row {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr 40px;
            gap: 0.75rem;
            align-items: center;
            opacity: 1;
            transform: translateY(0);
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .grade-row.removing {
            opacity: 0;
            transform: translateY(10px) scale(0.95);
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
        }

        .grade-row.adding {
            opacity: 0;
            transform: translateY(-15px);
        }

        .grade-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
            padding-left: 0.25rem;
        }

        .input-suffix {
            position: absolute;
            right: 1rem;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-muted);
            pointer-events: none;
        }

        .input-wrapper input {
            padding-right: 2.2rem;
        }

        .btn-delete {
            background: transparent;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-delete:hover {
            color: var(--accent-danger);
            background-color: rgba(239, 68, 68, 0.1);
        }

        .btn-delete svg {
            width: 18px;
            height: 18px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
        }

        /* Dynamic Options (Equal Weights, Add Grade) */
        .control-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1.5rem;
            margin-bottom: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-secondary);
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            accent-color: var(--primary-color);
            cursor: pointer;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-primary {
            background: var(--primary-grad);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35);
        }

        .btn-secondary {
            background-color: var(--bg-body);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background-color: var(--bg-input);
            border-color: var(--text-muted);
        }

        .btn-icon-only {
            padding: 0.6rem;
            border-radius: 0.5rem;
        }

        .btn svg {
            width: 16px;
            height: 16px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
        }

        /* Exam Config Slider/Toggle Block */
        .exam-section {
            border-top: 1px solid var(--border-color);
            padding-top: 1.5rem;
            margin-top: 1.5rem;
        }

        .switch-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
        }

        .switch-title-desc {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
        }

        .switch-title {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .switch-desc {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        /* Styled Switch toggle */
        .switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 26px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: var(--border-color);
            transition: .3s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .3s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background: var(--primary-grad);
        }

        input:focus + .slider {
            box-shadow: 0 0 1px var(--primary-color);
        }

        input:checked + .slider:before {
            transform: translateX(20px);
        }

        .exam-inputs-wrapper {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, opacity 0.3s;
            opacity: 0;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }

        .exam-inputs-wrapper.expanded {
            max-height: 200px;
            opacity: 1;
        }

        /* Right Panel / Results Section */
        .result-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 2.5rem 2rem;
            position: relative;
            overflow: hidden;
        }

        /* Circular Progress Styling */
        .circle-progress-container {
            position: relative;
            width: 180px;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .circle-svg {
            transform: rotate(-90deg);
            width: 180px;
            height: 180px;
        }

        .circle-bg {
            fill: none;
            stroke: var(--bg-body);
            stroke-width: 10;
        }

        .circle-bar {
            fill: none;
            stroke: url(#accent-grad-svg);
            stroke-width: 10;
            stroke-linecap: round;
            stroke-dasharray: 502; /* 2 * PI * r (r=80) = 502.6 */
            stroke-dashoffset: 502;
            transition: stroke-dashoffset 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .circle-value {
            position: absolute;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
        }

        .circle-number {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        .circle-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 0.25rem;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 1rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .status-badge.approved {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--accent-success);
        }

        .status-badge.pending {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--accent-warning);
        }

        .status-badge.impossible {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--accent-danger);
        }

        .status-badge svg {
            width: 14px;
            height: 14px;
            fill: none;
            stroke: currentColor;
            stroke-width: 2.5;
        }

        /* Exam Target Detail Info Card */
        .exam-target-card {
            width: 100%;
            background-color: var(--bg-body);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            padding: 1.25rem;
            margin-top: 1rem;
            text-align: left;
            box-shadow: var(--inner-shadow);
        }

        .exam-target-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .exam-target-title svg {
            width: 16px;
            height: 16px;
            stroke: var(--primary-color);
            stroke-width: 2.5;
            fill: none;
        }

        .exam-target-value {
            font-size: 1.4rem;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
            color: var(--primary-color);
            margin-bottom: 0.4rem;
        }

        .exam-target-detail {
            font-size: 0.85rem;
            color: var(--text-secondary);
            line-height: 1.4;
        }

        .exam-target-detail strong {
            color: var(--text-primary);
        }

        /* Progress Bar breakdown of weights */
        .weight-progress-wrapper {
            width: 100%;
            margin-top: 1.5rem;
        }

        .weight-progress-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.4rem;
        }

        .weight-progress-track {
            height: 8px;
            background-color: var(--bg-body);
            border-radius: 4px;
            width: 100%;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }

        .weight-progress-bar {
            height: 100%;
            background: var(--primary-grad);
            width: 0%;
            transition: width 0.3s ease;
        }

        .weight-progress-bar.excess {
            background: var(--accent-danger);
        }

        .warning-msg {
            color: var(--accent-danger);
            font-size: 0.8rem;
            font-weight: 500;
            margin-top: 0.4rem;
            display: none;
            align-items: center;
            gap: 0.3rem;
        }

        .warning-msg svg {
            width: 14px;
            height: 14px;
            stroke: currentColor;
            stroke-width: 2.5;
            fill: none;
        }

        /* Action Buttons Right Side */
        .aside-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            width: 100%;
        }

        /* Toast notification */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 1rem 1.5rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1000;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast svg {
            stroke: var(--accent-success);
            width: 20px;
            height: 20px;
            fill: none;
            stroke-width: 2.5;
        }

        .toast-text {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-primary);
        }

        footer {
            text-align: center;
            padding: 2rem;
            font-size: 0.85rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-color);
            max-width: 1200px;
            width: 100%;
            margin: 2rem auto 0;
            transition: border-color var(--transition-speed);
        }

        footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
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
        <!-- Left Column: Inputs -->
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
                <!-- Rows populated by Javascript or initially PHP fallback -->
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

        <!-- Right Column: Sticky Summary & Results -->
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

    <!-- Toast Notification -->
    <div class="toast" id="toast">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3"/></svg>
        <span class="toast-text" id="toastText">Enlace copiado al portapapeles</span>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> CalcuNota. Hecho con ❤️ para estudiantes. Enfocado exclusivamente en tus metas.</p>
    </footer>

    <script>
        // Preset configurations for scales
        const SCALES = {
            chile: { min: 1.0, max: 7.0, pass: 4.0, step: 0.1, placeholder: "Ej: 4.0" },
            spain: { min: 0.0, max: 10.0, pass: 5.0, step: 0.1, placeholder: "Ej: 5.0" },
            colombia: { min: 0.0, max: 5.0, pass: 3.0, step: 0.1, placeholder: "Ej: 3.0" },
            usa: { min: 0, max: 100, pass: 60, step: 1, placeholder: "Ej: 60" }
        };

        let currentScaleKey = "<?php echo $scale; ?>";
        let currentScale = SCALES[currentScaleKey];

        // Theme management
        const body = document.body;
        const themeToggle = document.getElementById("themeToggle");

        // Load saved theme or default to light
        const savedTheme = localStorage.getItem("theme") || "theme-light";
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

        // DOM elements
        const scaleSelect = document.getElementById("scaleSelect");
        const passingGradeInput = document.getElementById("passingGrade");
        const gradesContainer = document.getElementById("gradesContainer");
        const btnAddGrade = document.getElementById("btnAddGrade");
        const equalWeightsCheckbox = document.getElementById("equalWeights");
        const examSwitch = document.getElementById("examSwitch");
        const examInputsWrapper = document.getElementById("examInputsWrapper");
        const examWeightInput = document.getElementById("examWeight");
        
        const averageText = document.getElementById("averageText");
        const statusBadge = document.getElementById("statusBadge");
        const statusLabel = document.getElementById("statusLabel");
        const examTargetCard = document.getElementById("examTargetCard");
        const examTargetValue = document.getElementById("examTargetValue");
        const examTargetDetail = document.getElementById("examTargetDetail");
        const weightSumText = document.getElementById("weightSumText");
        const weightProgressBar = document.getElementById("weightProgressBar");
        const weightWarning = document.getElementById("weightWarning");
        const circleBar = document.getElementById("circleBar");
        
        const btnClear = document.getElementById("btnClear");
        const btnShare = document.getElementById("btnShare");
        const toast = document.getElementById("toast");

        // Initialize passing grade if empty
        if (!passingGradeInput.value) {
            passingGradeInput.value = currentScale.pass;
        }

        // Scale Select changes
        scaleSelect.addEventListener("change", (e) => {
            const oldScale = currentScale;
            currentScaleKey = e.target.value;
            currentScale = SCALES[currentScaleKey];
            
            // Adjust passing grade based on scale
            passingGradeInput.placeholder = currentScale.placeholder;
            
            // Map the previous passing grade proportionally or just assign default
            if (parseFloat(passingGradeInput.value) === oldScale.pass || !passingGradeInput.value) {
                passingGradeInput.value = currentScale.pass;
            } else {
                // If customized, cap it within the range
                let val = parseFloat(passingGradeInput.value);
                if (val < currentScale.min) val = currentScale.min;
                if (val > currentScale.max) val = currentScale.max;
                passingGradeInput.value = val;
            }

            // Adjust min/max/step on all grade inputs
            document.querySelectorAll(".grade-input").forEach(input => {
                input.min = currentScale.min;
                input.max = currentScale.max;
                input.step = currentScale.step;
                
                let val = parseFloat(input.value);
                if (!isNaN(val)) {
                    if (val < currentScale.min) input.value = currentScale.min;
                    if (val > currentScale.max) input.value = currentScale.max;
                }
            });

            calculate();
        });

        // Event listener for inputs change (event delegation)
        document.addEventListener("input", (e) => {
            if (e.target.classList.contains("grade-input") || 
                e.target.classList.contains("weight-input") || 
                e.target === passingGradeInput || 
                e.target === examWeightInput) {
                calculate();
            }
        });

        // Add Grade button clicked
        btnAddGrade.addEventListener("click", () => {
            addGradeRow();
        });

        // Equal Weights checkbox clicked
        equalWeightsCheckbox.addEventListener("change", () => {
            toggleWeightInputs();
            calculate();
        });

        // Exam toggle clicked
        examSwitch.addEventListener("change", (e) => {
            if (e.target.checked) {
                examInputsWrapper.classList.add("expanded");
            } else {
                examInputsWrapper.classList.remove("expanded");
            }
            calculate();
        });

        // Clear all button
        btnClear.addEventListener("click", () => {
            // Remove all rows except the first one
            const rows = gradesContainer.querySelectorAll(".grade-row");
            rows.forEach((row, idx) => {
                if (idx > 0) {
                    row.remove();
                } else {
                    row.querySelector(".grade-input").value = "";
                    row.querySelector(".weight-input").value = "";
                    row.querySelector(".btn-delete").style.display = "none";
                }
            });

            // Reset configurations
            equalWeightsCheckbox.checked = false;
            toggleWeightInputs();
            examSwitch.checked = false;
            examInputsWrapper.classList.remove("expanded");
            passingGradeInput.value = currentScale.pass;
            examWeightInput.value = 30;

            calculate();
            saveToLocalStorage();
        });

        // Share Link button
        btnShare.addEventListener("click", () => {
            const url = generateShareLink();
            navigator.clipboard.writeText(url).then(() => {
                showToast("Enlace de notas copiado al portapapeles");
            }).catch(err => {
                showToast("Error al copiar enlace, cópialo manualmente de la barra de dirección.");
            });
        });

        // Dynamic addition of a grade row
        function addGradeRow(gradeVal = "", weightVal = "") {
            const rowCount = gradesContainer.querySelectorAll(".grade-row").length;
            const newRow = document.createElement("div");
            newRow.className = "grade-row adding";
            newRow.innerHTML = `
                <span class="grade-label">Nota ${rowCount + 1}</span>
                <div class="input-wrapper">
                    <input type="number" class="grade-input" min="${currentScale.min}" max="${currentScale.max}" step="${currentScale.step}" placeholder="Nota" value="${gradeVal}">
                </div>
                <div class="input-wrapper">
                    <input type="number" class="weight-input" min="0" max="100" placeholder="Peso" value="${weightVal}" ${equalWeightsCheckbox.checked ? 'disabled' : ''}>
                    <span class="input-suffix">%</span>
                </div>
                <button class="btn-delete" title="Eliminar nota">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2M10 11v6M14 11v6"/></svg>
                </button>
            `;

            // Append row and animate
            gradesContainer.appendChild(newRow);
            
            // Trigger animation frame to ensure the transitions render correctly
            requestAnimationFrame(() => {
                newRow.classList.remove("adding");
            });

            // Ensure delete button is visible on first row if count > 1
            const firstRowDeleteBtn = gradesContainer.querySelector(".grade-row .btn-delete");
            if (firstRowDeleteBtn) {
                firstRowDeleteBtn.style.display = "flex";
            }

            // Register delete click handler
            newRow.querySelector(".btn-delete").addEventListener("click", () => {
                removeGradeRow(newRow);
            });

            if (equalWeightsCheckbox.checked) {
                distributeEqualWeights();
            }

            calculate();
        }

        // Dynamic removal of a grade row
        function removeGradeRow(rowElement) {
            rowElement.classList.add("removing");
            
            // Wait for transition before removal
            rowElement.addEventListener("transitionend", function handler(e) {
                if (e.propertyName === 'opacity' || e.propertyName === 'transform') {
                    rowElement.remove();
                    
                    // Re-index row labels
                    const rows = gradesContainer.querySelectorAll(".grade-row");
                    rows.forEach((row, index) => {
                        row.querySelector(".grade-label").textContent = `Nota ${index + 1}`;
                    });

                    // Hide first row delete if it's the only one left
                    if (rows.length === 1) {
                        rows[0].querySelector(".btn-delete").style.display = "none";
                    }

                    if (equalWeightsCheckbox.checked) {
                        distributeEqualWeights();
                    }

                    calculate();
                }
            });
        }

        // Register initial delete actions
        document.querySelectorAll(".grade-row").forEach(row => {
            const deleteBtn = row.querySelector(".btn-delete");
            if (deleteBtn) {
                deleteBtn.addEventListener("click", () => {
                    removeGradeRow(row);
                });
            }
        });

        // Toggle disabled property on weight inputs based on checkbox state
        function toggleWeightInputs() {
            const isChecked = equalWeightsCheckbox.checked;
            const weightInputs = gradesContainer.querySelectorAll(".weight-input");
            weightInputs.forEach(input => {
                input.disabled = isChecked;
            });
            if (isChecked) {
                distributeEqualWeights();
            }
        }

        // Automate equal weight distribution
        function distributeEqualWeights() {
            const rows = gradesContainer.querySelectorAll(".grade-row");
            const examEnabled = examSwitch.checked;
            const examW = examEnabled ? (parseFloat(examWeightInput.value) || 0) : 0;
            
            // Weights for coursework total 100% of the coursework portion.
            // When calculating, coursework will be normalized to represent (100 - examW)%
            const weightVal = (100 / rows.length).toFixed(1);
            
            rows.forEach(row => {
                row.querySelector(".weight-input").value = weightVal;
            });
        }

        // Core calculation engine
        function calculate() {
            const rows = gradesContainer.querySelectorAll(".grade-row");
            const examEnabled = examSwitch.checked;
            const examWeight = examEnabled ? (parseFloat(examWeightInput.value) || 0) : 0;
            const passingGrade = parseFloat(passingGradeInput.value) || currentScale.pass;

            let totalWeight = 0;
            let sumGradesWeights = 0;
            let validGradesCount = 0;
            let totalWeightsAvailable = 0;

            // First pass: distribute equal weights if checked
            if (equalWeightsCheckbox.checked) {
                distributeEqualWeights();
            }

            // Collect active values
            rows.forEach(row => {
                const gradeVal = parseFloat(row.querySelector(".grade-input").value);
                const weightVal = parseFloat(row.querySelector(".weight-input").value) || 0;

                totalWeightsAvailable += weightVal;

                if (!isNaN(gradeVal)) {
                    validGradesCount++;
                    totalWeight += weightVal;
                    sumGradesWeights += (gradeVal * weightVal);
                }
            });

            // Update weight progression bar
            let accumulatedWeightForBar = examEnabled ? (totalWeightsAvailable + examWeight) : totalWeightsAvailable;
            // Cap visual display at 100%
            const visualPercent = Math.min(accumulatedWeightForBar, 100);
            weightSumText.textContent = `${accumulatedWeightForBar.toFixed(1)}%`;
            weightProgressBar.style.width = `${visualPercent}%`;

            if (accumulatedWeightForBar > 100.1) {
                weightProgressBar.classList.add("excess");
                weightWarning.style.display = "flex";
            } else {
                weightProgressBar.classList.remove("excess");
                weightWarning.style.display = "none";
            }

            // Recalculate Coursework Weighted Average
            let average = 0;
            if (validGradesCount > 0 && totalWeight > 0) {
                average = sumGradesWeights / totalWeight;
                averageText.textContent = average.toFixed(2);
                
                // Map the average value to the progress circle
                const scaleRange = currentScale.max - currentScale.min;
                const normalizedVal = (average - currentScale.min) / scaleRange;
                const percent = Math.max(0, Math.min(1, normalizedVal));
                
                // 502 is the maximum dasharray. 0 offset is fully colored, 502 is empty.
                const offset = 502 - (502 * percent);
                circleBar.style.strokeDashoffset = offset;
            } else {
                averageText.textContent = "-";
                circleBar.style.strokeDashoffset = 502;
            }

            // Required exam grade calculation
            if (examEnabled && validGradesCount > 0) {
                statusBadge.style.display = "inline-flex";
                examTargetCard.style.display = "block";
                
                const courseworkWeight = 1 - (examWeight / 100);
                const examWeightFraction = examWeight / 100;

                // Required Grade: R = (Pass - (Avg * CourseworkWeight)) / ExamWeight
                const neededExamGrade = (passingGrade - (average * courseworkWeight)) / examWeightFraction;

                // Max/Min limits
                const maxPossibleFinal = average * courseworkWeight + currentScale.max * examWeightFraction;
                const minPossibleFinal = average * courseworkWeight + currentScale.min * examWeightFraction;

                if (minPossibleFinal >= passingGrade) {
                    // Already passed
                    statusBadge.className = "status-badge approved";
                    statusLabel.textContent = "Aprobado";
                    examTargetValue.textContent = "¡Aprobado!";
                    examTargetDetail.innerHTML = `Tu nota acumulada actual es <strong>${(average * courseworkWeight).toFixed(2)}</strong>. Incluso obteniendo la nota mínima de <strong>${currentScale.min.toFixed(1)}</strong> en el examen final, tu promedio del curso será <strong>${minPossibleFinal.toFixed(2)}</strong>, superando la nota de aprobación de <strong>${passingGrade.toFixed(1)}</strong>.`;
                } else if (maxPossibleFinal < passingGrade) {
                    // Reprobado
                    statusBadge.className = "status-badge impossible";
                    statusLabel.textContent = "Reprobado";
                    examTargetValue.textContent = "No alcanzable";
                    examTargetDetail.innerHTML = `Lamentablemente no es posible aprobar. Incluso obteniendo la nota máxima de <strong>${currentScale.max.toFixed(1)}</strong> en el examen final, tu promedio final máximo será de <strong>${maxPossibleFinal.toFixed(2)}</strong>, que es menor al mínimo para aprobar de <strong>${passingGrade.toFixed(1)}</strong>.`;
                } else {
                    // Needs a grade between min and max
                    statusBadge.className = "status-badge pending";
                    statusLabel.textContent = "En Progreso";
                    examTargetValue.textContent = neededExamGrade.toFixed(2);
                    examTargetDetail.innerHTML = `Necesitas obtener al menos un <strong>${neededExamGrade.toFixed(2)}</strong> en el examen final (ponderado al <strong>${examWeight}%</strong>) para aprobar la materia con la nota mínima de <strong>${passingGrade.toFixed(1)}</strong>.`;
                }
            } else {
                // Exam disabled or no grades
                statusBadge.style.display = "none";
                examTargetCard.style.display = "none";
            }

            saveToLocalStorage();
            syncURLState();
        }

        // Generate sharing URL
        function generateShareLink() {
            const baseUrl = window.location.origin + window.location.pathname;
            const scale = currentScaleKey;
            const examEnabled = examSwitch.checked;
            const examWeight = examWeightInput.value;
            const passingGrade = passingGradeInput.value;
            const equal = equalWeightsCheckbox.checked;

            // Pack grades
            const rows = gradesContainer.querySelectorAll(".grade-row");
            const gradeStrings = [];
            rows.forEach(row => {
                const g = row.querySelector(".grade-input").value;
                const w = row.querySelector(".weight-input").value;
                if (g !== "" || w !== "") {
                    gradeStrings.push(`${g || 0}:${w || 0}`);
                }
            });

            const params = new URLSearchParams();
            params.set("scale", scale);
            if (gradeStrings.length > 0) {
                params.set("grades", gradeStrings.join(","));
            }
            if (examEnabled) {
                params.set("exam_enabled", "true");
                params.set("exam_weight", examWeight);
            }
            params.set("pass", passingGrade);
            if (equal) {
                params.set("equal", "true");
            }

            return `${baseUrl}?${params.toString()}`;
        }

        // Synchronize browser address bar with current state silently
        function syncURLState() {
            const shareUrl = generateShareLink();
            window.history.replaceState({ path: shareUrl }, '', shareUrl);
        }

        // Show toaster notification
        function showToast(message) {
            toast.querySelector("#toastText").textContent = message;
            toast.classList.add("show");
            setTimeout(() => {
                toast.classList.remove("show");
            }, 3000);
        }

        // Save active configuration to LocalStorage
        function saveToLocalStorage() {
            const rows = gradesContainer.querySelectorAll(".grade-row");
            const savedGrades = [];
            rows.forEach(row => {
                savedGrades.push({
                    grade: row.querySelector(".grade-input").value,
                    weight: row.querySelector(".weight-input").value
                });
            });

            const state = {
                scale: currentScaleKey,
                passingGrade: passingGradeInput.value,
                equalWeights: equalWeightsCheckbox.checked,
                examEnabled: examSwitch.checked,
                examWeight: examWeightInput.value,
                grades: savedGrades
            };

            localStorage.setItem("calcunota_state", JSON.stringify(state));
        }

        // Restore state from LocalStorage on load if query parameters are empty
        function loadFromLocalStorage() {
            const stateStr = localStorage.getItem("calcunota_state");
            if (!stateStr) return;

            try {
                const state = JSON.parse(stateStr);
                
                // Set scale
                scaleSelect.value = state.scale || "chile";
                currentScaleKey = scaleSelect.value;
                currentScale = SCALES[currentScaleKey];
                
                passingGradeInput.value = state.passingGrade || currentScale.pass;
                equalWeightsCheckbox.checked = !!state.equalWeights;
                
                examSwitch.checked = !!state.examEnabled;
                if (examSwitch.checked) {
                    examInputsWrapper.classList.add("expanded");
                }
                examWeightInput.value = state.examWeight || 30;

                // Repopulate grade rows
                if (state.grades && state.grades.length > 0) {
                    gradesContainer.innerHTML = "";
                    state.grades.forEach((item, idx) => {
                        addGradeRow(item.grade, item.weight);
                    });
                }
            } catch (e) {
                console.error("Error restoring local state: ", e);
            }
        }

        // Startup routing
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has("scale") || urlParams.has("grades")) {
            // Load completed via PHP initialization
            toggleWeightInputs();
            calculate();
        } else {
            // Load state from LocalStorage
            loadFromLocalStorage();
            toggleWeightInputs();
            calculate();
        }
    </script>
</body>
</html>
