$evalBO = "c:\Users\boujm\Desktop\boj web\eval\View\BackOffice"
$mainBO = "c:\xampp\htdocs\7ashwa\Esprit-PW-2A33-2526-e-lite-Gestion_Forum\View\Quiz\BackOffice"

$files = @("quiz_add.php", "quiz_update.php", "quiz_locks.php", "quiz_results_export.php", "questions_list.php", "question_add.php", "question_update.php")

foreach ($file in $files) {
    $src = Join-Path $evalBO $file
    $dst = Join-Path $mainBO $file
    $content = Get-Content -Path $src -Raw -Encoding UTF8
    
    # Fix require_once paths
    $content = $content -replace [regex]::Escape("require_once __DIR__ . '/../../Controller/QuizController.php';"), "require_once __DIR__ . '/../../../Controller/Quiz/QuizController.php';"
    $content = $content -replace [regex]::Escape("require_once __DIR__ . '/../../Controller/QuestionController.php';"), "require_once __DIR__ . '/../../../Controller/Quiz/QuestionController.php';"
    $content = $content -replace [regex]::Escape("require_once __DIR__ . '/../../Controller/Validator.php';"), "require_once __DIR__ . '/../../../Controller/Quiz/Validator.php';"
    
    # Fix asset paths - add basePath variable at top if not present
    if ($content -notmatch '\$__bp') {
        $bpCode = '<?php $__bp = rtrim(str_replace("\\", "/", substr(realpath(__DIR__ . "/../../.."), strlen(realpath($_SERVER["DOCUMENT_ROOT"])))), "/"); if ($__bp === "." || $__bp === "") $__bp = ""; ?>' + "`n"
        $content = $content -replace "(<\?php)", ('<?php' + "`n" + '$__bp = rtrim(str_replace("\\", "/", substr(realpath(__DIR__ . "/../../.."), strlen(realpath($_SERVER["DOCUMENT_ROOT"])))), "/"); if ($__bp === "." || $__bp === "") $__bp = "";')
    }
    
    # Fix asset paths
    $content = $content -replace [regex]::Escape('href="../assets/index.css"'), 'href="<?= $__bp ?>/View/assets/Quiz/index.css"'
    $content = $content -replace 'src="../assets/index\.js\?v=[^"]*"', 'src="<?= $__bp ?>/View/assets/Quiz/index.js?v=20260511"'
    $content = $content -replace [regex]::Escape('src="../assets/index.js"'), 'src="<?= $__bp ?>/View/assets/Quiz/index.js?v=20260511"'
    
    # Fix internal links (quizzes_list.php -> /quiz/admin, etc.)
    $content = $content -replace [regex]::Escape('href="quizzes_list.php'), 'href="<?= $__bp ?>/quiz/admin'
    $content = $content -replace [regex]::Escape('href="quiz_add.php'), 'href="<?= $__bp ?>/quiz/admin/ajouter'
    $content = $content -replace [regex]::Escape('href="quiz_update.php'), 'href="<?= $__bp ?>/quiz/admin/modifier'
    $content = $content -replace [regex]::Escape('href="quiz_locks.php'), 'href="<?= $__bp ?>/quiz/admin/verrous'
    $content = $content -replace [regex]::Escape('href="quiz_results_export.php'), 'href="<?= $__bp ?>/quiz/admin/export'
    $content = $content -replace [regex]::Escape('href="questions_list.php'), 'href="<?= $__bp ?>/quiz/admin/questions'
    $content = $content -replace [regex]::Escape('href="question_add.php'), 'href="<?= $__bp ?>/quiz/admin/question/ajouter'
    $content = $content -replace [regex]::Escape('href="question_update.php'), 'href="<?= $__bp ?>/quiz/admin/question/modifier'
    $content = $content -replace [regex]::Escape('href="quiz/generate.php'), 'href="<?= $__bp ?>/quiz/admin/generer'
    
    # Fix Location headers
    $content = $content -replace [regex]::Escape("header('Location: quizzes_list.php"), "header('Location: ' . \$__bp . '/quiz/admin"
    $content = $content -replace [regex]::Escape("header('Location: questions_list.php"), "header('Location: ' . \$__bp . '/quiz/admin/questions"
    
    Set-Content -Path $dst -Value $content -Encoding UTF8
    Write-Host "Copied: $file"
}

# Copy eval's generate view
$evalGenDir = "c:\Users\boujm\Desktop\boj web\eval\View\BackOffice\quiz"
$mainGenDir = "c:\xampp\htdocs\7ashwa\Esprit-PW-2A33-2526-e-lite-Gestion_Forum\View\Quiz\BackOffice\quiz"
if (-not (Test-Path $mainGenDir)) { New-Item -ItemType Directory -Path $mainGenDir | Out-Null }

$genSrc = Join-Path $evalGenDir "generate.php"
$genDst = Join-Path $mainGenDir "generate.php"
if (Test-Path $genSrc) {
    $content = Get-Content -Path $genSrc -Raw -Encoding UTF8
    $content = $content -replace [regex]::Escape("require_once __DIR__ . '/../../../Controller/QuizController.php';"), "require_once __DIR__ . '/../../../../Controller/Quiz/QuizController.php';"
    $content = $content -replace [regex]::Escape("require_once __DIR__ . '/../../../Controller/Validator.php';"), "require_once __DIR__ . '/../../../../Controller/Quiz/Validator.php';"
    $content = $content -replace [regex]::Escape('href="../../assets/index.css"'), 'href="<?= $__bp ?>/View/assets/Quiz/index.css"'
    $content = $content -replace 'src="../../assets/index\.js[^"]*"', 'src="<?= $__bp ?>/View/assets/Quiz/index.js?v=20260511"'
    Set-Content -Path $genDst -Value $content -Encoding UTF8
    Write-Host "Copied: quiz/generate.php"
}

# Copy eval's CSS and JS assets
$evalAssets = "c:\Users\boujm\Desktop\boj web\eval\View\assets"
$mainAssets = "c:\xampp\htdocs\7ashwa\Esprit-PW-2A33-2526-e-lite-Gestion_Forum\View\assets\Quiz"

Copy-Item (Join-Path $evalAssets "index.css") -Destination (Join-Path $mainAssets "index.css") -Force
Copy-Item (Join-Path $evalAssets "index.js") -Destination (Join-Path $mainAssets "index.js") -Force
Write-Host "Copied assets"

Write-Host "Integration complete!"
