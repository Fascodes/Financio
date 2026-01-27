<?php

require_once 'src/controllers/AppController.php';
require_once 'src/utility/DatabaseUtility.php';

class DashboardController extends AppController {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseUtility::getConnection();
    }

    public function dashboard() {
        // Przykład zapytania dla danych wykresu (np. transakcje miesięczne)
        $stmt = $this->pdo->prepare("SELECT month, SUM(amount) as total FROM transactions GROUP BY month ORDER BY month");
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Przekaż dane do widoku (np. jako JSON dla JavaScript wykresu)
        $chartData = json_encode($data);

        // Buforuj wyjście HTML
        ob_start();
        include 'public/views/dashboard.html';
        $html = ob_get_clean();

        // Wstaw dane JS do HTML (przed </body>)
        $html = str_replace('</body>', '<script>var chartData = ' . $chartData . ';</script></body>', $html);

        echo $html;
    }
}