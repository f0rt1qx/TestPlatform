<?php

class PDFExporter {

    /**
     * Get logo absolute path
     */
    private static function getLogoPath(): string {
        $logoPath = realpath(__DIR__ . '/../../src/logogreen.png');
        if ($logoPath && file_exists($logoPath)) {
            return $logoPath;
        }
        $altPath = realpath(__DIR__ . '/../../src/logo.png');
        if ($altPath && file_exists($altPath)) {
            return $altPath;
        }
        return '';
    }

    /**
     * Get logo as base64 data URI for embedding in PDF
     */
    private static function getLogoDataUri(): string {
        $logoPath = self::getLogoPath();
        if ($logoPath && file_exists($logoPath)) {
            $data = file_get_contents($logoPath);
            if ($data !== false) {
                return 'data:image/png;base64,' . base64_encode($data);
            }
        }
        return '';
    }

    /**
     * Get logo image tag for HTML (base64 embedded)
     */
    private static function getLogoTag(string $width = '48', string $height = '48'): string {
        $dataUri = self::getLogoDataUri();
        if ($dataUri) {
            return '<img src="' . $dataUri . '" alt="Sapienta" style="width: ' . $width . 'px; height: ' . $height . 'px; border-radius: 10px;">';
        }
        return '';
    }

    public static function exportResults(array $results): void {
        $html = self::generateAdminHTML($results);
        self::outputPDF($html);
    }

    public static function exportSingleResult(array $result, array $questions = []): void {
        $html = self::generateStudentHTML($result, $questions);
        self::outputPDF($html);
    }

    private static function generateAdminHTML(array $results): string {
        $total = count($results);
        $passed = $total > 0 ? count(array_filter($results, fn($r) => $r['passed'] == 1)) : 0;
        $failed = $total - $passed;
        $avgPercentage = $total > 0 ? array_sum(array_map(fn($r) => floatval($r['percentage']), $results)) / $total : 0;
        $avgCheat = $total > 0 ? array_sum(array_map(fn($r) => intval($r['cheat_score']), $results)) / $total : 0;

        $logo = self::getLogoTag();

        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page {
    size: A4;
    margin: 0;
}
* {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
}
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    margin: 0;
    padding: 0;
    color: #1e293b;
    background: #f8fafc;
}
.page-wrapper {
    padding: 20mm;
}
.header {
    background: linear-gradient(135deg, #00c853 0%, #00e676 100%);
    color: white;
    padding: 24px 30px;
    border-radius: 16px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.header-left img {
    width: 48px;
    height: 48px;
    border-radius: 10px;
}
.header-left h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    letter-spacing: -0.5px;
}
.header-left .date {
    margin-top: 4px;
    font-size: 11px;
    opacity: 0.9;
}
.header-right {
    text-align: right;
    font-size: 10px;
    opacity: 0.85;
}
.summary-grid {
    display: table;
    width: 100%;
    border-spacing: 12px 0;
    margin-bottom: 24px;
}
.summary-row {
    display: table-row;
}
.summary-card {
    display: table-cell;
    width: 20%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    vertical-align: middle;
}
.summary-card .card-value {
    font-size: 26px;
    font-weight: 800;
    color: #00c853;
    margin-bottom: 4px;
}
.summary-card .card-value.red {
    color: #dc2626;
}
.summary-card .card-value.blue {
    color: #3b82f6;
}
.summary-card .card-label {
    font-size: 9px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
}
.results-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}
.results-table thead {
    background: #f1f5f9;
}
.results-table thead th {
    padding: 12px 10px;
    text-align: left;
    font-weight: 700;
    font-size: 9px;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e2e8f0;
}
.results-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
}
.results-table tbody tr:last-child {
    border-bottom: none;
}
.results-table tbody td {
    padding: 12px 10px;
    font-size: 10px;
}
.badge-pass {
    display: inline-block;
    background: #f0fdf4;
    color: #16a34a;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 9px;
}
.badge-fail {
    display: inline-block;
    background: #fef2f2;
    color: #dc2626;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 9px;
}
.cheat-high { color: #dc2626; font-weight: 600; }
.cheat-medium { color: #d97706; font-weight: 600; }
.cheat-low { color: #16a34a; font-weight: 600; }
.footer {
    margin-top: 24px;
    padding: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    text-align: center;
    font-size: 9px;
    color: #94a3b8;
}
.no-data {
    text-align: center;
    padding: 50px 20px;
    background: #ffffff;
    border: 2px dashed #e2e8f0;
    border-radius: 12px;
    color: #64748b;
    font-size: 13px;
}
</style>
</head>
<body>
<div class="page-wrapper">

<div class="header">
    <div class="header-left">
        ' . $logo . '
        <div>
            <h1>Sapienta — Результаты</h1>
            <div class="date">Сформировано: ' . date('d.m.Y H:i:s') . '</div>
        </div>
    </div>
    <div class="header-right">
        Официальный отчёт<br>
        sapienta.edu
    </div>
</div>';

        if ($total > 0) {
            $html .= '<div class="summary-grid">
    <div class="summary-row">
        <div class="summary-card">
            <div class="card-value">' . $total . '</div>
            <div class="card-label">Всего</div>
        </div>
        <div class="summary-card">
            <div class="card-value">' . $passed . '</div>
            <div class="card-label">Сдано</div>
        </div>
        <div class="summary-card">
            <div class="card-value red">' . $failed . '</div>
            <div class="card-label">Не сдано</div>
        </div>
        <div class="summary-card">
            <div class="card-value blue">' . number_format($avgPercentage, 1) . '%</div>
            <div class="card-label">Средний балл</div>
        </div>
        <div class="summary-card">
            <div class="card-value">' . number_format($avgCheat, 0) . '</div>
            <div class="card-label">Честность</div>
        </div>
    </div>
</div>';

            $html .= '<table class="results-table">
<thead>
<tr>
    <th style="width: 18%;">Пользователь</th>
    <th style="width: 20%;">Тест</th>
    <th style="width: 8%;">Попытка</th>
    <th style="width: 10%;">Балл</th>
    <th style="width: 10%;">Статус</th>
    <th style="width: 14%;">Честность</th>
    <th style="width: 10%;">Время</th>
    <th style="width: 10%;">Дата</th>
</tr>
</thead>
<tbody>
';

            foreach ($results as $r) {
                $cheat = intval($r['cheat_score']);
                $cheatLabel = $cheat >= 40 ? 'Низкая' : ($cheat >= 15 ? 'Средняя' : 'Высокая');
                $cheatClass = $cheat >= 40 ? 'cheat-high' : ($cheat >= 15 ? 'cheat-medium' : 'cheat-low');
                $statusClass = $r['passed'] == 1 ? 'badge-pass' : 'badge-fail';
                $statusText = $r['passed'] == 1 ? '✓ Сдан' : '✗ Не сдан';
                $timeMins = floor($r['time_spent'] / 60);
                $timeSecs = $r['time_spent'] % 60;
                $timeStr = $timeMins . 'м ' . $timeSecs . 'с';

                $html .= '<tr>
    <td><strong style="color: #1e293b;">' . htmlspecialchars($r['username']) . '</strong><br><span style="color: #94a3b8; font-size: 8px;">' . htmlspecialchars($r['email']) . '</span></td>
    <td>' . htmlspecialchars($r['test_title']) . '</td>
    <td style="text-align: center; color: #64748b;">#' . $r['attempt_number'] . '</td>
    <td style="text-align: center; font-weight: 700; color: #1e293b;">' . number_format(floatval($r['percentage']), 1) . '%</td>
    <td style="text-align: center;"><span class="' . $statusClass . '">' . $statusText . '</span></td>
    <td style="text-align: center;" class="' . $cheatClass . '">' . $cheatLabel . ' (' . $cheat . ')</td>
    <td style="text-align: center; color: #64748b;">' . $timeStr . '</td>
    <td style="text-align: center; color: #64748b;">' . date('d.m.Y', strtotime($r['created_at'])) . '</td>
</tr>
';
            }

            $html .= '</tbody>
</table>';
        } else {
            $html .= '<div class="no-data">Нет данных для отображения</div>';
        }

        $html .= '
<div class="footer">
    <strong>Sapienta</strong> © ' . date('Y') . ' | Документ сформирован автоматически
</div>

</div>
</body>
</html>';

        return $html;
    }

    private static function generateStudentHTML(array $result, array $questions = []): string {

        $percentage = isset($result['percentage']) ? floatval($result['percentage']) : 0;
        $passed = isset($result['passed']) && $result['passed'] == 1;
        $cheat = isset($result['cheat_score']) ? intval($result['cheat_score']) : 0;
        $timeSpent = isset($result['time_spent']) ? intval($result['time_spent']) : 0;
        $timeMins = floor($timeSpent / 60);
        $timeSecs = $timeSpent % 60;

        $username = htmlspecialchars($result['username'] ?? 'Пользователь');
        $email = htmlspecialchars($result['email'] ?? '');
        $testTitle = htmlspecialchars($result['test_title'] ?? 'Тест');
        $score = $result['score'] ?? 0;
        $maxScore = $result['max_score'] ?? 0;
        $attemptNumber = $result['attempt_number'] ?? 1;
        $passScore = $result['pass_score'] ?? 60;
        $createdAt = isset($result['created_at']) ? date('d.m.Y H:i', strtotime($result['created_at'])) : date('d.m.Y H:i');

        $cheatLabel = $cheat >= 40 ? 'Низкий' : ($cheat >= 15 ? 'Средний' : 'Высокий');
        $cheatColor = $cheat >= 40 ? '#dc2626' : ($cheat >= 15 ? '#d97706' : '#16a34a');
        $cheatBgColor = $cheat >= 40 ? '#fef2f2' : ($cheat >= 15 ? '#fffbeb' : '#f0fdf4');
        $statusText = $passed ? 'ТЕСТ СДАН' : 'ТЕСТ НЕ СДАН';
        $statusIcon = $passed ? '✓' : '✗';
        $statusBg = $passed ? '#f0fdf4' : '#fef2f2';
        $statusBorder = $passed ? '#16a34a' : '#dc2626';
        $statusTextColor = $passed ? '#16a34a' : '#dc2626';
        $percentageColor = $passed ? '#00c853' : '#dc2626';

        $logo = self::getLogoTag();

        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page {
    size: A4;
    margin: 0;
}
* {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    color-adjust: exact !important;
}
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    margin: 0;
    padding: 0;
    color: #1e293b;
    background: #f8fafc;
}
.page-wrapper {
    padding: 20mm;
}
.header {
    background: linear-gradient(135deg, #00c853 0%, #00e676 100%);
    color: white;
    padding: 24px 30px;
    border-radius: 16px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.header-left img {
    width: 48px;
    height: 48px;
    border-radius: 10px;
}
.header-left h1 {
    margin: 0;
    font-size: 20px;
    font-weight: 800;
}
.header-left .subtitle {
    margin-top: 4px;
    font-size: 11px;
    opacity: 0.9;
}
.header-right {
    text-align: right;
    font-size: 10px;
    opacity: 0.85;
}
.result-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 24px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.result-status {
    font-size: 14px;
    font-weight: 700;
    color: ' . $statusTextColor . ';
    letter-spacing: 1px;
    margin-bottom: 8px;
}
.result-percentage {
    font-size: 56px;
    font-weight: 900;
    color: ' . $percentageColor . ';
    line-height: 1;
    margin: 12px 0;
}
.result-test-name {
    font-size: 12px;
    color: #64748b;
    margin-top: 8px;
}
.info-grid {
    display: table;
    width: 100%;
    border-spacing: 12px 0;
    margin-bottom: 24px;
}
.info-row {
    display: table-row;
}
.info-card {
    display: table-cell;
    width: 25%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    vertical-align: middle;
}
.info-card .ic-label {
    font-size: 9px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-bottom: 6px;
}
.info-card .ic-value {
    font-size: 16px;
    font-weight: 800;
    color: #1e293b;
}
.info-card .ic-sub {
    font-size: 10px;
    color: #94a3b8;
    margin-top: 4px;
}
.cheat-card {
    background: ' . $cheatBgColor . ';
    border: 1px solid ' . $cheatColor . ';
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 24px;
}
.cheat-title {
    font-size: 11px;
    font-weight: 700;
    color: ' . $cheatColor . ';
    margin-bottom: 10px;
}
.cheat-bar {
    height: 10px;
    background: #e2e8f0;
    border-radius: 5px;
    overflow: hidden;
    margin-bottom: 8px;
}
.cheat-bar-fill {
    height: 100%;
    width: ' . (100 - $cheat) . '%;
    background: ' . $cheatColor . ';
    border-radius: 5px;
}
.cheat-desc {
    font-size: 10px;
    color: #475569;
    line-height: 1.6;
}
.questions-section {
    margin-top: 28px;
}
.questions-section h3 {
    font-size: 15px;
    font-weight: 800;
    color: #1e293b;
    margin-bottom: 16px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}
.question-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
}
.question-card.correct {
    border-left: 4px solid #16a34a;
}
.question-card.incorrect {
    border-left: 4px solid #dc2626;
}
.q-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.q-num {
    font-size: 10px;
    font-weight: 700;
    color: #64748b;
}
.q-points {
    font-size: 9px;
    font-weight: 700;
    color: #7c3aed;
    background: #ede9fe;
    padding: 3px 10px;
    border-radius: 20px;
}
.q-text {
    font-size: 12px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 12px;
    line-height: 1.6;
}
.answers {
    display: table;
    width: 100%;
}
.answer-item {
    display: table-row;
}
.answer-item span {
    display: table-cell;
    font-size: 11px;
    padding: 8px 12px;
    border-radius: 8px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    margin-bottom: 6px;
}
.answer-item span.correct {
    background: #f0fdf4;
    border-color: #16a34a;
    color: #15803d;
}
.answer-item span.incorrect {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #dc2626;
}
.footer {
    margin-top: 24px;
    padding: 16px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    text-align: center;
    font-size: 9px;
    color: #94a3b8;
    line-height: 1.8;
}
</style>
</head>
<body>
<div class="page-wrapper">

<div class="header">
    <div class="header-left">
        ' . $logo . '
        <div>
            <h1>Sapienta — Результат</h1>
            <div class="subtitle">Официальный документ | ' . date('d.m.Y H:i:s') . '</div>
        </div>
    </div>
    <div class="header-right">
        sapienta.edu
    </div>
</div>

<div class="result-card">
    <div class="result-status">' . $statusIcon . ' ' . $statusText . '</div>
    <div class="result-percentage">' . number_format($percentage, 1) . '%</div>
    <div class="result-test-name">' . $testTitle . '</div>
</div>

<div class="info-grid">
    <div class="info-row">
        <div class="info-card">
            <div class="ic-label">Пользователь</div>
            <div class="ic-value">' . $username . '</div>
            <div class="ic-sub">' . $email . '</div>
        </div>
        <div class="info-card">
            <div class="ic-label">Тест</div>
            <div class="ic-value" style="font-size: 12px;">' . $testTitle . '</div>
            <div class="ic-sub">Попытка #' . $attemptNumber . '</div>
        </div>
        <div class="info-card">
            <div class="ic-label">Балл</div>
            <div class="ic-value">' . $score . ' / ' . $maxScore . '</div>
            <div class="ic-sub">Проходной: ' . $passScore . '%</div>
        </div>
        <div class="info-card">
            <div class="ic-label">Время</div>
            <div class="ic-value">' . $timeMins . 'м ' . $timeSecs . 'с</div>
            <div class="ic-sub">' . $createdAt . '</div>
        </div>
    </div>
</div>

<div class="cheat-card">
    <div class="cheat-title">Уровень честности: ' . $cheatLabel . ' (' . $cheat . '/100)</div>
    <div class="cheat-bar">
        <div class="cheat-bar-fill"></div>
    </div>
    <div class="cheat-desc">
        ' . ($cheat >= 40 ? 'Обнаружены подозрения в нечестном поведении. Рекомендуется проверка.' : ($cheat >= 15 ? 'Незначительные отклонения. В целом допустимо.' : 'Тест пройден с высоким уровнем честности.')) . '
    </div>
</div>';

        if (!empty($result['answers_json'])) {
            $answers = is_string($result['answers_json']) ? json_decode($result['answers_json'], true) : $result['answers_json'];

            if (is_array($answers) && !empty($answers)) {
                $html .= '<div class="questions-section">
    <h3>Детализация ответов</h3>';

                $qNum = 0;
                foreach ($answers as $qId => $answerData) {
                    $qNum++;
                    $isCorrect = $answerData['is_correct'] ?? false;
                    $points = $answerData['points'] ?? 0;
                    $maxPoints = 0;
                    $questionText = 'Вопрос ' . $qNum;

                    foreach ($questions as $q) {
                        if ($q['id'] == $qId) {
                            $questionText = $q['question_text'];
                            $maxPoints = $q['points'];
                            break;
                        }
                    }

                    $html .= '<div class="question-card ' . ($isCorrect ? 'correct' : 'incorrect') . '">
        <div class="q-header">
            <span class="q-num">Вопрос ' . $qNum . '</span>
            <span class="q-points">' . $points . ' / ' . $maxPoints . ' баллов</span>
        </div>
        <div class="q-text">' . htmlspecialchars($questionText) . '</div>';

                    if (isset($answerData['given']) && is_array($answerData['given'])) {
                        $html .= '<div class="answers">';
                        foreach ($answerData['given'] as $ansId) {
                            $isGivenCorrect = in_array($ansId, $answerData['correct'] ?? []);
                            $html .= '<div class="answer-item"><span class="' . ($isGivenCorrect ? 'correct' : 'incorrect') . '">' . ($isGivenCorrect ? '✓' : '✗') . ' Ответ #' . $ansId . ($isGivenCorrect ? ' — правильный' : ' — неправильный') . '</span></div>';
                        }
                        $html .= '</div>';
                    }

                    $html .= '</div>';
                }

                $html .= '</div>';
            }
        }

        $html .= '
<div class="footer">
    <strong>Sapienta</strong> © ' . date('Y') . ' | Документ сформирован автоматически<br>
    Официальное подтверждение результатов тестирования
</div>

</div>
</body>
</html>';

        return $html;
    }

    private static function outputPDF(string $html): void {
        if (class_exists('\\Dompdf\\Dompdf')) {
            self::outputWithDompdf($html);
        } else {
            self::outputAsHTMLForPrint($html);
        }
    }

    private static function outputWithDompdf(string $html): void {
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="sapienta_results_' . date('Y-m-d_H-i-s') . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: no-cache, must-revalidate');

        echo $pdfContent;
        exit;
    }

    private static function outputAsHTMLForPrint(string $html): void {
        $printButton = '<div id="printButton" style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: white; padding: 14px; border-radius: 14px; box-shadow: 0 4px 24px rgba(0,0,0,0.12);">
    <button onclick="window.print()" style="
        background: linear-gradient(135deg, #00c853 0%, #00e676 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: block;
        margin-bottom: 8px;
        box-shadow: 0 2px 8px rgba(0,200,83,0.3);
    ">Сохранить PDF</button>
    <p style="font-size: 10px; color: #64748b; margin: 0; text-align: center; max-width: 140px; line-height: 1.4;">
        Нажмите и выберите «Сохранить как PDF»
    </p>
</div>
<style>
@media print {
    #printButton { display: none !important; }
    body { margin: 0; padding: 0; }
}
</style>';

        $html = str_replace('<body>', '<body>' . $printButton, $html);

        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-cache, must-revalidate');

        echo $html;
        exit;
    }
}
