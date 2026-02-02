-- Tabela użytkowników
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Tabela grup budżetowych
CREATE TABLE groups (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    owner_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    description TEXT,
    budget NUMERIC(12, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Tabela kategorii (predefiniowane)
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela członków grupy (relacja n-n z rolami)
CREATE TABLE group_members (
    id SERIAL PRIMARY KEY,
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    role VARCHAR(20) NOT NULL CHECK (role IN ('owner', 'editor')), -- RBAC
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(group_id, user_id)  -- Jeden użytkownik max raz w grupie
);

-- Tabela transakcji (główna)
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    group_id INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE SET NULL,
    category_id INTEGER NOT NULL REFERENCES categories(id),
    name VARCHAR(255) NOT NULL,
    amount NUMERIC(10, 2) NOT NULL CHECK (amount > 0),
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_archived BOOLEAN DEFAULT FALSE,  -- Dla archiwizacji
    archive_date TIMESTAMP NULL
);

-- Indeksy dla wydajności
CREATE INDEX idx_transactions_group_date ON transactions(group_id, date DESC) WHERE is_archived = FALSE;
CREATE INDEX idx_transactions_user ON transactions(user_id);
CREATE INDEX idx_group_members_user ON group_members(user_id);
CREATE INDEX idx_group_members_group ON group_members(group_id);


-- Widok 1: Ostatnie transakcje grupy z pełnymi danymi
CREATE VIEW recent_group_transactions AS
SELECT 
    t.id,
    t.name,
    t.amount,
    t.date,
    u.username,
    c.name as category,
    g.id as group_id,
    g.name as group_name
FROM transactions t
JOIN users u ON t.user_id = u.id
JOIN categories c ON t.category_id = c.id
JOIN groups g ON t.group_id = g.id
WHERE t.is_archived = FALSE
ORDER BY t.date DESC;

-- Widok 2: Podsumowanie grupy z saldem członków
CREATE VIEW group_member_summary AS
SELECT 
    gm.group_id,
    g.name as group_name,
    u.id as user_id,
    u.username,
    COUNT(t.id) as transaction_count,
    SUM(CASE WHEN t.user_id = u.id THEN t.amount ELSE 0 END) as total_spent,
    AVG(CASE WHEN t.user_id IS NOT NULL THEN t.amount ELSE 0 END) as avg_transaction,
    gm.role
FROM group_members gm
JOIN groups g ON gm.group_id = g.id
JOIN users u ON gm.user_id = u.id
LEFT JOIN transactions t ON g.id = t.group_id AND t.is_archived = FALSE
GROUP BY gm.group_id, g.name, u.id, u.username, gm.role;



-- Funkcja: Obliczenie salda użytkownika w grupie
CREATE OR REPLACE FUNCTION calculate_user_balance_in_group(
    p_user_id INTEGER,
    p_group_id INTEGER
) RETURNS NUMERIC AS $$
DECLARE
    v_member_count INTEGER;
    v_total_spent NUMERIC;
    v_split_amount NUMERIC;
    v_user_spent NUMERIC;
    v_balance NUMERIC;
BEGIN
    -- Liczba członków grupy
    SELECT COUNT(*) INTO v_member_count
    FROM group_members
    WHERE group_id = p_group_id AND user_id != p_user_id;
    
    -- Łączna suma wydatków w grupie
    SELECT COALESCE(SUM(amount), 0) INTO v_total_spent
    FROM transactions
    WHERE group_id = p_group_id AND is_archived = FALSE;
    
    -- Średnia na osobę (dla każdego członka)
    v_split_amount := v_total_spent / NULLIF(v_member_count + 1, 0);
    
    -- Ile wydał dany użytkownik
    SELECT COALESCE(SUM(amount), 0) INTO v_user_spent
    FROM transactions
    WHERE group_id = p_group_id AND user_id = p_user_id AND is_archived = FALSE;
    
    -- Saldo: ile wydał - ile powinien wydać (dodatnie = wpłacił więcej)
    v_balance := v_user_spent - v_split_amount;
    
    RETURN v_balance;
END;
$$ LANGUAGE plpgsql;


-- Trigger: Archiwizowanie transakcji starszych niż 6 miesięcy
CREATE OR REPLACE FUNCTION archive_old_transactions()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE transactions
    SET is_archived = TRUE, archive_date = CURRENT_TIMESTAMP
    WHERE date < CURRENT_DATE - INTERVAL '6 months'
      AND is_archived = FALSE;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_archive_transactions
AFTER INSERT ON transactions
FOR EACH ROW
EXECUTE FUNCTION archive_old_transactions();

-- Trigger: Walidacja - transakcja musi mieć kategorię z tabeli categories
CREATE OR REPLACE FUNCTION validate_category_exists()
RETURNS TRIGGER AS $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM categories WHERE id = NEW.category_id) THEN
        RAISE EXCEPTION 'Kategoria o ID % nie istnieje', NEW.category_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER trigger_validate_category
BEFORE INSERT OR UPDATE ON transactions
FOR EACH ROW
EXECUTE FUNCTION validate_category_exists();


INSERT INTO categories (name, description) VALUES
('Spożywcze', 'Zakupy spożywcze i artykuły'),
('Transport', 'Paliwo, komunikacja, przejazdy'),
('Gastronomia', 'Restauracje, kawiarnie, bary'),
('Elektronika', 'Sprzęt elektroniczny i akcesoria'),
('Odzież', 'Ubrania, obuwie, akcesoria'),
('Rozrywka', 'Kino, teatr, gry, książki'),
('Zdrowie', 'Leki, wizyty lekarskie, fitness');

-- Test User (password: admin123 - zahaszowane bcrypt)
INSERT INTO users (email, username, password_hash, is_active) VALUES
('admin@example.com', 'admin', '$2y$12$zWi9og5B3qezqQi3fDz6YeGPqIBMlyKYzSLyk65aoWkUGi4GVT28u', true);

-- Test Group
INSERT INTO groups (name, owner_id, description, budget, is_active) VALUES
('Budżet Domowy', 1, 'Wspólny budżet rodzinny', 5000.00, true);

-- Add user as member of group
INSERT INTO group_members (group_id, user_id, role) VALUES
(1, 1, 'owner');

-- Sample Transactions (mix of January and February 2026 for testing)
INSERT INTO transactions (group_id, user_id, category_id, name, amount, date) VALUES
-- February 2026 (current month)
(1, 1, 1, 'Zakupy Biedronka', 185.30, '2026-02-02'),
(1, 1, 3, 'Lunch w pracy', 45.00, '2026-02-01'),
(1, 1, 2, 'Uber do centrum', 32.50, '2026-02-01'),
(1, 1, 1, 'Pieczywo i nabiał', 28.90, '2026-01-31'),
(1, 1, 6, 'Netflix - subskrypcja', 49.00, '2026-01-30'),
-- January 2026 (last month)
(1, 1, 1, 'Zakupy Tesco', 150.50, '2026-01-28'),
(1, 1, 2, 'Benzyna 50L', 250.00, '2026-01-27'),
(1, 1, 3, 'Obiad w restauracji', 120.00, '2026-01-26'),
(1, 1, 1, 'Warzywa świeże', 45.75, '2026-01-26'),
(1, 1, 4, 'Laptop część - RAM', 299.99, '2026-01-25'),
(1, 1, 3, 'Kawa w kawiarni', 18.50, '2026-01-25'),
(1, 1, 6, 'Bilet do kina', 35.00, '2026-01-24'),
(1, 1, 5, 'Koszulka adidas', 89.99, '2026-01-23'),
(1, 1, 1, 'Mleko, chleb, masło', 32.40, '2026-01-23'),
(1, 1, 2, 'Karta parkingowa', 80.00, '2026-01-22'),
(1, 1, 7, 'Leki na przeziębienie', 25.00, '2026-01-22'),
(1, 1, 3, 'Pizza - zamówienie', 45.99, '2026-01-21'),
(1, 1, 4, 'Kabel HDMI', 29.50, '2026-01-21'),
(1, 1, 6, 'Książka - Ścieżka Wojny', 55.00, '2026-01-20'),
(1, 1, 1, 'Owoce - banany, jabłka', 28.70, '2026-01-20');