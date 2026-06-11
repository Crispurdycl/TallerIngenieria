// Preset configurations for scales
const SCALES = {
    chile: { min: 1.0, max: 7.0, pass: 4.0, step: 0.1, placeholder: "Ej: 4.0" },
    spain: { min: 0.0, max: 10.0, pass: 5.0, step: 0.1, placeholder: "Ej: 5.0" },
    colombia: { min: 0.0, max: 5.0, pass: 3.0, step: 0.1, placeholder: "Ej: 3.0" },
    usa: { min: 0, max: 100, pass: 60, step: 1, placeholder: "Ej: 60" }
};

let currentScaleKey = "chile";
let currentScale = SCALES[currentScaleKey];
let currentSubjectId = null; // Track loaded database subject

// DOM Elements
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

// CRUD DOM Elements
const subjectsList = document.getElementById("subjectsList");
const subjectsCount = document.getElementById("subjectsCount");
const btnSaveSubject = document.getElementById("btnSaveSubject");
const btnSaveSubjectAs = document.getElementById("btnSaveSubjectAs");
const saveModal = document.getElementById("saveModal");
const btnModalCancel = document.getElementById("btnModalCancel");
const btnModalSave = document.getElementById("btnModalSave");
const modalSubjectName = document.getElementById("modalSubjectName");

// ----------------------------------------------------
// Theme Management
// ----------------------------------------------------
const body = document.body;
const themeToggle = document.getElementById("themeToggle");
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

// Initialize passing grade placeholder/value
if (passingGradeInput && !passingGradeInput.value) {
    passingGradeInput.value = currentScale.pass;
}

// ----------------------------------------------------
// Grade Calculator Logic
// ----------------------------------------------------

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
    resetCalculator();
});

function resetCalculator() {
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

    // Reset database active state
    currentSubjectId = null;
    document.querySelectorAll(".subject-item").forEach(el => el.classList.remove("active"));
    btnSaveSubject.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Guardar`;
    btnSaveSubjectAs.style.display = "none";

    calculate();
    saveToLocalStorage();
}

// Share Link button
btnShare.addEventListener("click", () => {
    const url = generateShareLink();
    navigator.clipboard.writeText(url).then(() => {
        showToast("Enlace de notas copiado al portapapeles");
    }).catch(err => {
        showToast("Error al copiar enlace.");
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

    gradesContainer.appendChild(newRow);
    
    requestAnimationFrame(() => {
        newRow.classList.remove("adding");
    });

    const firstRowDeleteBtn = gradesContainer.querySelector(".grade-row .btn-delete");
    if (firstRowDeleteBtn) {
        firstRowDeleteBtn.style.display = "flex";
    }

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
    
    rowElement.addEventListener("transitionend", function handler(e) {
        if (e.propertyName === 'opacity' || e.propertyName === 'transform') {
            rowElement.remove();
            
            const rows = gradesContainer.querySelectorAll(".grade-row");
            rows.forEach((row, index) => {
                row.querySelector(".grade-label").textContent = `Nota ${index + 1}`;
            });

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

// Register delete actions for server-rendered rows
document.querySelectorAll(".grade-row").forEach(row => {
    const deleteBtn = row.querySelector(".btn-delete");
    if (deleteBtn) {
        deleteBtn.addEventListener("click", () => {
            removeGradeRow(row);
        });
    }
});

// Toggle weights disabled state
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

// Distribute weights equally
function distributeEqualWeights() {
    const rows = gradesContainer.querySelectorAll(".grade-row");
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

    if (equalWeightsCheckbox.checked) {
        distributeEqualWeights();
    }

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

    // Weight indicators
    let accumulatedWeightForBar = examEnabled ? (totalWeightsAvailable + examWeight) : totalWeightsAvailable;
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

    // Average
    let average = 0;
    if (validGradesCount > 0 && totalWeight > 0) {
        average = sumGradesWeights / totalWeight;
        averageText.textContent = average.toFixed(2);
        
        const scaleRange = currentScale.max - currentScale.min;
        const normalizedVal = (average - currentScale.min) / scaleRange;
        const percent = Math.max(0, Math.min(1, normalizedVal));
        const offset = 502 - (502 * percent);
        circleBar.style.strokeDashoffset = offset;
    } else {
        averageText.textContent = "-";
        circleBar.style.strokeDashoffset = 502;
    }

    // Required exam grade
    if (examEnabled && validGradesCount > 0) {
        statusBadge.style.display = "inline-flex";
        examTargetCard.style.display = "block";
        
        const courseworkWeight = 1 - (examWeight / 100);
        const examWeightFraction = examWeight / 100;

        const neededExamGrade = (passingGrade - (average * courseworkWeight)) / examWeightFraction;
        const maxPossibleFinal = average * courseworkWeight + currentScale.max * examWeightFraction;
        const minPossibleFinal = average * courseworkWeight + currentScale.min * examWeightFraction;

        if (minPossibleFinal >= passingGrade) {
            statusBadge.className = "status-badge approved";
            statusLabel.textContent = "Aprobado";
            examTargetValue.textContent = "¡Aprobado!";
            examTargetDetail.innerHTML = `Tu nota acumulada actual es <strong>${(average * courseworkWeight).toFixed(2)}</strong>. Incluso con la nota mínima de <strong>${currentScale.min.toFixed(1)}</strong> en el examen final, tu promedio final será <strong>${minPossibleFinal.toFixed(2)}</strong>, superando la nota de aprobación de <strong>${passingGrade.toFixed(1)}</strong>.`;
        } else if (maxPossibleFinal < passingGrade) {
            statusBadge.className = "status-badge impossible";
            statusLabel.textContent = "Reprobado";
            examTargetValue.textContent = "No alcanzable";
            examTargetDetail.innerHTML = `Lamentablemente no es posible aprobar. Incluso con la nota máxima de <strong>${currentScale.max.toFixed(1)}</strong> en el examen final, tu promedio final máximo será de <strong>${maxPossibleFinal.toFixed(2)}</strong>, que es menor al mínimo para aprobar de <strong>${passingGrade.toFixed(1)}</strong>.`;
        } else {
            statusBadge.className = "status-badge pending";
            statusLabel.textContent = "En Progreso";
            examTargetValue.textContent = neededExamGrade.toFixed(2);
            examTargetDetail.innerHTML = `Necesitas obtener al menos un <strong>${neededExamGrade.toFixed(2)}</strong> en el examen final (ponderado al <strong>${examWeight}%</strong>) para aprobar la materia con la nota mínima de <strong>${passingGrade.toFixed(1)}</strong>.`;
        }
    } else {
        statusBadge.style.display = "none";
        examTargetCard.style.display = "none";
    }

    saveToLocalStorage();
    syncURLState();
}

// Generate share url
function generateShareLink() {
    const baseUrl = window.location.origin + window.location.pathname;
    const scale = currentScaleKey;
    const examEnabled = examSwitch.checked;
    const examWeight = examWeightInput.value;
    const passingGrade = passingGradeInput.value;
    const equal = equalWeightsCheckbox.checked;

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

// Sync URL bar silently
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

// LocalStorage caching
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

// Restore state
function loadFromLocalStorage() {
    const stateStr = localStorage.getItem("calcunota_state");
    if (!stateStr) return;

    try {
        const state = JSON.parse(stateStr);
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

// ----------------------------------------------------
// Database CRUD REST Client Logic
// ----------------------------------------------------

// Load subjects list in sidebar
function loadSubjectsList() {
    fetch('api.php')
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                subjectsCount.textContent = res.data.length;
                if (res.data.length === 0) {
                    subjectsList.innerHTML = `<div class="empty-subjects">No hay asignaturas guardadas.</div>`;
                    return;
                }

                subjectsList.innerHTML = "";
                res.data.forEach(subject => {
                    const item = document.createElement("div");
                    item.className = `subject-item ${currentSubjectId === subject.id ? 'active' : ''}`;
                    item.dataset.id = subject.id;
                    
                    // Format update time
                    const updateDate = new Date(subject.updated_at).toLocaleDateString('es-ES', {
                        day: '2-digit', month: '2-digit', year: 'numeric'
                    });

                    item.innerHTML = `
                        <div class="subject-info">
                            <span class="subject-title">${escapeHTML(subject.name)}</span>
                            <span class="subject-meta">${escapeHTML(SCALES[subject.scale]?.placeholder ? SCALES[subject.scale].placeholder.replace("Ej:", "Escala") : subject.scale)} • ${updateDate}</span>
                        </div>
                        <div class="subject-actions">
                            <button class="btn-item-action delete" title="Eliminar asignatura">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                            </button>
                        </div>
                    `;

                    // Register Load Click
                    item.addEventListener("click", (e) => {
                        // Avoid triggering load when delete is clicked
                        if (e.target.closest(".delete")) return;
                        loadSubjectDetails(subject.id);
                    });

                    // Register Delete Click
                    item.querySelector(".delete").addEventListener("click", () => {
                        if (confirm(`¿Estás seguro de que deseas eliminar "${subject.name}"?`)) {
                            deleteSubjectFromDatabase(subject.id);
                        }
                    });

                    subjectsList.appendChild(item);
                });
            }
        })
        .catch(err => console.error("Error loading subjects:", err));
}

// Load a specific subject detail into calculator
function loadSubjectDetails(id) {
    fetch(`api.php?id=${id}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const s = res.data;
                currentSubjectId = s.id;

                // Sync UI elements
                scaleSelect.value = s.scale;
                currentScaleKey = s.scale;
                currentScale = SCALES[currentScaleKey];
                passingGradeInput.value = s.passing_grade;
                equalWeightsCheckbox.checked = s.equal_weights == 1;

                examSwitch.checked = s.exam_enabled == 1;
                if (examSwitch.checked) {
                    examInputsWrapper.classList.add("expanded");
                } else {
                    examInputsWrapper.classList.remove("expanded");
                }
                examWeightInput.value = s.exam_weight;

                // Load grades list
                const grades = JSON.parse(s.grades_json);
                gradesContainer.innerHTML = "";
                if (grades.length > 0) {
                    grades.forEach((item, idx) => {
                        addGradeRow(item.grade, item.weight);
                    });
                } else {
                    addGradeRow();
                }

                // UI adjustments
                toggleWeightInputs();
                calculate();

                // Highlight active list item
                document.querySelectorAll(".subject-item").forEach(el => {
                    if (parseInt(el.dataset.id) === currentSubjectId) {
                        el.classList.add("active");
                    } else {
                        el.classList.remove("active");
                    }
                });

                // Change save button text to show update mode
                btnSaveSubject.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Guardar Cambios`;
                btnSaveSubjectAs.style.display = "inline-flex";

                showToast(`Asignatura "${s.name}" cargada.`);
            }
        })
        .catch(err => console.error("Error loading subject details:", err));
}

// Open Save Modal
btnSaveSubject.addEventListener("click", () => {
    if (currentSubjectId) {
        // Direct update if already loaded
        saveSubjectToDatabase();
    } else {
        // Open modal to choose subject name
        openSaveModal();
    }
});

btnSaveSubjectAs.addEventListener("click", () => {
    openSaveModal();
});

function openSaveModal() {
    modalSubjectName.value = "";
    saveModal.classList.add("show");
    modalSubjectName.focus();
}

btnModalCancel.addEventListener("click", () => {
    saveModal.classList.remove("show");
});

btnModalSave.addEventListener("click", () => {
    const name = modalSubjectName.value.trim();
    if (!name) {
        alert("Por favor ingresa un nombre para la asignatura.");
        return;
    }
    saveModal.classList.remove("show");
    
    // Save as new by temporarily clearing ID if clicking "Save As"
    if (document.activeElement === btnSaveSubjectAs || !currentSubjectId) {
        saveSubjectToDatabase(name, true);
    } else {
        saveSubjectToDatabase(name);
    }
});

// Post calculator values to database
function saveSubjectToDatabase(customName = null, forceNew = false) {
    // Gather grades
    const rows = gradesContainer.querySelectorAll(".grade-row");
    const grades = [];
    rows.forEach(row => {
        const g = row.querySelector(".grade-input").value;
        const w = row.querySelector(".weight-input").value;
        if (g !== "" || w !== "") {
            grades.push({
                grade: g !== "" ? parseFloat(g) : "",
                weight: w !== "" ? parseFloat(w) : ""
            });
        }
    });

    const payload = {
        id: (forceNew) ? null : currentSubjectId,
        name: customName || (currentSubjectId ? document.querySelector(`.subject-item[data-id="${currentSubjectId}"] .subject-title`).textContent : "Asignatura"),
        scale: currentScaleKey,
        passing_grade: parseFloat(passingGradeInput.value) || currentScale.pass,
        exam_enabled: examSwitch.checked ? 1 : 0,
        exam_weight: parseFloat(examWeightInput.value) || 30.0,
        equal_weights: equalWeightsCheckbox.checked ? 1 : 0,
        grades_json: JSON.stringify(grades)
    };

    fetch('api.php?action=save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(res => {
        if (res.success) {
            currentSubjectId = res.id;
            loadSubjectsList();
            showToast(res.message);
            
            // Adjust save buttons
            btnSaveSubject.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Guardar Cambios`;
            btnSaveSubjectAs.style.display = "inline-flex";
        } else {
            alert("Error: " + res.error);
        }
    })
    .catch(err => console.error("Error saving subject:", err));
}

// Delete subject
function deleteSubjectFromDatabase(id) {
    fetch('api.php?action=delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(res => {
        if (res.success) {
            showToast(res.message);
            if (currentSubjectId === id) {
                resetCalculator();
            }
            loadSubjectsList();
        } else {
            alert("Error: " + res.error);
        }
    })
    .catch(err => console.error("Error deleting subject:", err));
}

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

// ----------------------------------------------------
// App Initialization
// ----------------------------------------------------
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has("scale") || urlParams.has("grades")) {
    toggleWeightInputs();
    calculate();
} else {
    loadFromLocalStorage();
    toggleWeightInputs();
    calculate();
}

// Load database items on startup
loadSubjectsList();
