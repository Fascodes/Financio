-- Tworzenie tabeli transactions
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    amount NUMERIC(10, 2) NOT NULL,
    month VARCHAR(7) NOT NULL,  -- Format: YYYY-MM
    category VARCHAR(50) NOT NULL,  -- Kategoria wydatku
    description VARCHAR(255),
    date DATE NOT NULL
);

-- Wstawianie przykładowych danych
INSERT INTO transactions (amount, month, category, description, date) VALUES
(100.50, '2023-01', 'Spożywcze', 'Zakupy spożywcze', '2023-01-15'),
(250.00, '2023-01', 'Transport', 'Paliwo', '2023-01-20'),
(75.25, '2023-02', 'Gastronomia', 'Restauracja', '2023-02-10'),
(300.00, '2023-02', 'Elektronika', 'Elektronika', '2023-02-25'),
(150.75, '2023-03', 'Odzież', 'Ubrania', '2023-03-05'),
(200.00, '2023-03', 'Transport', 'Transport', '2023-03-18'),
(120.00, '2023-01', 'Rozrywka', 'Kino', '2023-01-10'),
(450.00, '2023-02', 'Spożywcze', 'Zakupy wielkie', '2023-02-05');