<?php

require_once 'src/utility/DatabaseUtility.php';

class DashboardRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = DatabaseUtility::getConnection();
    }

   
    public function getMonthlyTrendData() {
        $stmt = $this->pdo->prepare(
            "SELECT month, SUM(amount) as total 
             FROM transactions 
             GROUP BY month 
             ORDER BY month"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategorySpendingData() {
        $stmt = $this->pdo->prepare(
            "SELECT category, SUM(amount) as total 
             FROM transactions 
             GROUP BY category 
             ORDER BY total DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * TODO: Implementacja rzeczywistego zapytania gdy tabela group_transactions będzie dostępna
     */
    public function getRecentTransactions() {
        // Mockowane dane - zastąpić rzeczywistym zapytaniem
        return [
            [
                'id' => 1,
                'description' => 'Zakupy spożywcze',
                'amount' => 100.50,
                'date' => '2023-03-18',
                'category' => 'Spożywcze'
            ],
            [
                'id' => 2,
                'description' => 'Transport',
                'amount' => 200.00,
                'date' => '2023-03-17',
                'category' => 'Transport'
            ],
            [
                'id' => 3,
                'description' => 'Restauracja',
                'amount' => 75.25,
                'date' => '2023-03-16',
                'category' => 'Gastronomia'
            ],
            [
                'id' => 4,
                'description' => 'Elektronika',
                'amount' => 300.00,
                'date' => '2023-03-15',
                'category' => 'Elektronika'
            ],
            [
                'id' => 5,
                'description' => 'Ubrania',
                'amount' => 150.75,
                'date' => '2023-03-14',
                'category' => 'Odzież'
            ]
        ];
    }

    /**
     * TODO: Implementacja rzeczywistego zapytania gdy tabela group_members będzie dostępna
     */
    public function getGroupMembers() {
        // Saldo: dodatnie = wpłacił więcej niż mu przypadało, ujemne = nie wpłacił dość
        return [
            [
                'id' => 1,
                'name' => 'Jan Kowalski',
                'email' => 'jan@example.com',
                'balance' => 150.00
            ],
            [
                'id' => 2,
                'name' => 'Maria Nowak',
                'email' => 'maria@example.com',
                'balance' => -75.50
            ],
            [
                'id' => 3,
                'name' => 'Piotr Lewandowski',
                'email' => 'piotr@example.com',
                'balance' => 50.25
            ],
            [
                'id' => 4,
                'name' => 'Ewa Wiśniewska',
                'email' => 'ewa@example.com',
                'balance' => -120.00
            ]
        ];
    }
}