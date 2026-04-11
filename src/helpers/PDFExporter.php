<?php


class PDFExporter {
    
    
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

        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page {
    size: A4;
    margin: 15mm;
}
body {
    font-family: DejaVu Sans, Arial, sans-serif;
    margin: 0;
    padding: 0;
    color: #1e293b;
}
.header {
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: white;
    padding: 25px 30px;
    margin: -15mm -15mm 20px -15mm;
}
.header h1 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}
.header .date {
    margin-top: 8px;
    font-size: 12px;
    opacity: 0.9;
}
.summary-box {
    background: #f0f4f8;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 18px 22px;
    margin-bottom: 25px;
}
.summary-box h3 {
    margin: 0 0 12px 0;
    font-size: 15px;
    color: #1e40af;
    font-weight: 700;
}
.summary-box .stats {
    font-size: 11px;
    line-height: 1.8;
    color: #475569;
}
.summary-box .stat-item {
    display: inline-block;
    margin-right: 25px;
}
.summary-box .stat-value {
    font-weight: 700;
    color: #1e293b;
}
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 9px;
}
thead {
    background: #2563eb;
    color: white;
}
thead th {
    padding: 9px 6px;
    text-align: left;
    font-weight: 600;
    font-size: 9px;
}
tbody tr:nth-child(even) {
    background: #f8fafc;
}
tbody tr:nth-child(odd) {
    background: #ffffff;
}
tbody td {
    padding: 7px 6px;
    border-bottom: 1px solid #e2e8f0;
}
.status-passed {
    color: #16a34a;
    font-weight: 700;
}
.status-failed {
    color: #dc2626;
    font-weight: 700;
}
.cheat-high {
    color: #dc2626;
    font-weight: 600;
}
.cheat-medium {
    color: #d97706;
    font-weight: 600;
}
.cheat-low {
    color: #16a34a;
    font-weight: 600;
}
.footer {
    margin-top: 20px;
    padding-top: 10px;
    border-top: 1px solid #cbd5e1;
    font-size: 8px;
    color: #94a3b8;
    text-align: center;
}
.no-data {
    text-align: center;
    padding: 40px;
    color: #64748b;
    font-size: 13px;
}
</style>
</head>
<body>

<div class="header">
    <h1>🎓 Sapienta — Результаты тестирования</h1>
    <div class="date">📅 Дата формирования: ' . date('d.m.Y H:i:s') . '</div>
</div>';

        if ($total > 0) {
            $html .= '<div class="summary-box">
    <h3>📊 Сводная статистика</h3>
    <div class="stats">
        <div class="stat-item">Всего результатов: <span class="stat-value">' . $total . '</span></div>
        <div class="stat-item">✅ Сдано: <span class="stat-value">' . $passed . '</span></div>
        <div class="stat-item">❌ Не сдано: <span class="stat-value">' . $failed . '</span></div>
        <br>
        <div class="stat-item">📈 Средний балл: <span class="stat-value">' . number_format($avgPercentage, 1) . '%</span></div>
        <div class="stat-item">🛡️ Средний читинг: <span class="stat-value">' . number_format($avgCheat, 1) . '/100</span></div>
    </div>
</div>';

            $html .= '<table>
<thead>
<tr>
    <th style="width: 16%;">Пользователь</th>
    <th style="width: 20%;">Тест</th>
    <th style="width: 7%;">Попытка</th>
    <th style="width: 9%;">Балл</th>
    <th style="width: 9%;">Статус</th>
    <th style="width: 15%;">Честность</th>
    <th style="width: 10%;">Время</th>
    <th style="width: 14%;">Дата</th>
</tr>
</thead>
<tbody>
';

            foreach ($results as $r) {
                $cheat = intval($r['cheat_score']);
                $cheatLabel = $cheat >= 40 ? 'Низкая' : ($cheat >= 15 ? 'Средняя' : 'Высокая');
                $cheatClass = $cheat >= 40 ? 'cheat-high' : ($cheat >= 15 ? 'cheat-medium' : 'cheat-low');
                $statusClass = $r['passed'] == 1 ? 'status-passed' : 'status-failed';
                $statusText = $r['passed'] == 1 ? '✓ Сдан' : '✗ Не сдан';
                $timeMins = floor($r['time_spent'] / 60);
                $timeSecs = $r['time_spent'] % 60;
                $timeStr = $timeMins . 'м ' . $timeSecs . 'с';

                $html .= '<tr>
    <td><strong>' . htmlspecialchars($r['username']) . '</strong><br><span style="color: #64748b; font-size: 8px;">' . htmlspecialchars($r['email']) . '</span></td>
    <td>' . htmlspecialchars($r['test_title']) . '</td>
    <td style="text-align: center;">#' . $r['attempt_number'] . '</td>
    <td style="text-align: center; font-weight: 600;">' . number_format(floatval($r['percentage']), 1) . '%</td>
    <td class="' . $statusClass . '" style="text-align: center;">' . $statusText . '</td>
    <td class="' . $cheatClass . '" style="text-align: center;">' . $cheatLabel . ' (' . $cheat . ')</td>
    <td style="text-align: center; color: #64748b;">' . $timeStr . '</td>
    <td style="text-align: center; color: #64748b;">' . date('d.m.Y', strtotime($r['created_at'])) . '</td>
</tr>
';
            }

            $html .= '</tbody>
</table>';
        } else {
            $html .= '<div class="no-data">📭 Нет данных для отображения</div>';
        }

        $html .= '
<div class="footer">
    Sapienta © ' . date('Y') . ' | Документ сформирован автоматически | Страница 1
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
        $statusClass = $passed ? 'status-passed' : 'status-failed';
        $statusText = $passed ? '✓ ТЕСТ СДАН' : '✗ ТЕСТ НЕ СДАН';
        $statusBgColor = $passed ? '#f0fdf4' : '#fef2f2';

        $html = '<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
@page {
    size: A4;
    margin: 15mm;
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
    background: #ffffff;
}
.header {
    background: #2563eb;
    color: white;
    padding: 30px;
    margin: -15mm -15mm 25px -15mm;
    text-align: center;
}
.header h1 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
}
.header .subtitle {
    margin-top: 8px;
    font-size: 12px;
    opacity: 0.9;
}
.result-badge {
    text-align: center;
    padding: 25px;
    margin-bottom: 25px;
    background: ' . $statusBgColor . ';
    border-radius: 12px;
    border: 2px solid ' . ($passed ? '#16a34a' : '#dc2626') . ';
}
.result-badge .status {
    font-size: 20px;
    font-weight: 700;
    color: ' . ($passed ? '#16a34a' : '#dc2626') . ';
    margin-bottom: 8px;
}
.result-badge .percentage {
    font-size: 48px;
    font-weight: 900;
    color: #2563eb;
    margin: 15px 0;
}
.result-badge .percentage.failed {
    color: #dc2626;
}
.result-badge .label {
    font-size: 11px;
    color: #64748b;
}
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 25px;
}
.info-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
}
.info-item .label {
    font-size: 9px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 600;
    margin-bottom: 6px;
}
.info-item .value {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
}
.cheat-box {
    background: ' . $cheatBgColor . ';
    border: 1px solid ' . $cheatColor . ';
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 25px;
}
.cheat-box .title {
    font-size: 11px;
    font-weight: 700;
    color: ' . $cheatColor . ';
    margin-bottom: 8px;
}
.cheat-box .bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 8px;
}
.cheat-box .bar-fill {
    height: 100%;
    width: ' . $cheat . '%;
    background: ' . $cheatColor . ';
    border-radius: 4px;
}
.cheat-box .desc {
    font-size: 10px;
    color: #475569;
    line-height: 1.6;
}
.questions-section {
    margin-top: 30px;
}
.questions-section h3 {
    font-size: 15px;
    font-weight: 700;
    color: #1e40af;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e2e8f0;
}
.question-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 12px;
}
.question-item.correct {
    border-left: 4px solid #16a34a;
}
.question-item.incorrect {
    border-left: 4px solid #dc2626;
}
.question-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}
.question-num {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
}
.question-points {
    font-size: 10px;
    font-weight: 600;
    color: #7c3aed;
    background: #ede9fe;
    padding: 3px 10px;
    border-radius: 12px;
}
.question-text {
    font-size: 12px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 12px;
    line-height: 1.6;
}
.answers-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.answer-item {
    font-size: 11px;
    padding: 8px 12px;
    border-radius: 6px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
}
.answer-item.correct {
    background: #f0fdf4;
    border-color: #16a34a;
    color: #15803d;
}
.answer-item.incorrect {
    background: #fef2f2;
    border-color: #dc2626;
    color: #dc2626;
}
.answer-item .icon {
    margin-right: 6px;
}
.footer {
    margin-top: 30px;
    padding-top: 15px;
    border-top: 1px solid #cbd5e1;
    font-size: 9px;
    color: #94a3b8;
    text-align: center;
    line-height: 1.8;
}
</style>
</head>
<body>

<div class="header">
    <h1>🎓 Sapienta — Результат теста</h1>
    <div class="subtitle">📄 Официальный результат | Сформирован: ' . date('d.m.Y H:i:s') . '</div>
</div>

<div class="result-badge">
    <div class="status">' . $statusText . '</div>
    <div class="percentage ' . ($passed ? '' : 'failed') . '">' . number_format($percentage, 1) . '%</div>
    <div class="label">Тест: ' . $testTitle . '</div>
</div>

<div class="info-grid">
    <div class="info-item">
        <div class="label">👤 Пользователь</div>
        <div class="value">' . $username . '</div>
        <div style="font-size: 10px; color: #64748b; margin-top: 4px;">' . $email . '</div>
    </div>
    <div class="info-item">
        <div class="label">📝 Тест</div>
        <div class="value">' . $testTitle . '</div>
        <div style="font-size: 10px; color: #64748b; margin-top: 4px;">Попытка #' . $attemptNumber . '</div>
    </div>
    <div class="info-item">
        <div class="label">📊 Балл</div>
        <div class="value">' . $score . ' / ' . $maxScore . '</div>
        <div style="font-size: 10px; color: #64748b; margin-top: 4px;">Проходной: ' . $passScore . '%</div>
    </div>
    <div class="info-item">
        <div class="label">⏱️ Время</div>
        <div class="value">' . $timeMins . ' мин ' . $timeSecs . ' сек</div>
        <div style="font-size: 10px; color: #64748b; margin-top: 4px;">' . $createdAt . '</div>
    </div>
</div>

<div class="cheat-box">
    <div class="title">🛡️ Уровень честности: ' . $cheatLabel . ' (' . $cheat . '/100)</div>
    <div class="bar">
        <div class="bar-fill"></div>
    </div>
    <div class="desc">
        ' . ($cheat >= 40 ? '⚠️ Обнаружены подозрения в нечестном поведении. Рекомендуется проверка.' : ($cheat >= 15 ? '⚡ Незначительные отклонения. В целом допустимо.' : '✅ Тест пройден с высоким уровнем честности.')) . '
    </div>
</div>';

        
        if (!empty($result['answers_json'])) {
            $answers = is_string($result['answers_json']) ? json_decode($result['answers_json'], true) : $result['answers_json'];
            
            if (is_array($answers) && !empty($answers)) {
                $html .= '<div class="questions-section">
    <h3>📋 Детализация ответов</h3>';

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

                    $html .= '<div class="question-item ' . ($isCorrect ? 'correct' : 'incorrect') . '">
        <div class="question-header">
            <span class="question-num">Вопрос ' . $qNum . '</span>
            <span class="question-points">' . $points . '/' . $maxPoints . ' баллов</span>
        </div>
        <div class="question-text">' . htmlspecialchars($questionText) . '</div>';

                    
                    if (isset($answerData['given']) && is_array($answerData['given'])) {
                        $html .= '<div class="answers-list">';
                        foreach ($answerData['given'] as $ansId) {
                            $isGivenCorrect = in_array($ansId, $answerData['correct'] ?? []);
                            $html .= '<div class="answer-item ' . ($isGivenCorrect ? 'correct' : 'incorrect') . '">
                <span class="icon">' . ($isGivenCorrect ? '✓' : '✗') . '</span>
                Ответ #' . $ansId . ($isGivenCorrect ? ' (правильный)' : ' (неправильный)') . '
            </div>';
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
    Sapienta © ' . date('Y') . ' | Документ сформирован автоматически<br>
    Данный документ является официальным подтверждением результатов тестирования
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
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();
        
        $pdfContent = $dompdf->output();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="results_' . date('Y-m-d_H-i-s') . '.pdf"');
        header('Content-Length: ' . strlen($pdfContent));
        header('Cache-Control: no-cache, must-revalidate');
        
        echo $pdfContent;
        exit;
    }

    
    private static function outputAsHTMLForPrint(string $html): void {
        
        $printButton = '<div id="printButton" style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: white; padding: 10px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);">
    <button onclick="window.print()" style="
        background: #2563eb;
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: block;
        margin-bottom: 8px;
    ">🖨️ Сохранить как PDF</button>
    <p style="font-size: 11px; color: #64748b; margin: 0; text-align: center; max-width: 150px; line-height: 1.4;">
        Нажмите и выберите "Сохранить как PDF" в диалоге печати
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
