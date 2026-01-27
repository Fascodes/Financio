-- Tworzenie tabeli transactions
CREATE TABLE transactions (
    id SERIAL PRIMARY KEY,
    amount NUMERIC(10, 2) NOT NULL,
    month VARCHAR(7) NOT NULL,  -- Format: YYYY-MM
    description VARCHAR(255),
    date DATE NOT NULL
);

-- Wstawianie przykładowych danych
INSERT INTO transactions (amount, month, description, date) VALUES
(100.50, '2023-01', 'Zakupy spożywcze', '2023-01-15'),
(250.00, '2023-01', 'Paliwo', '2023-01-20'),
(75.25, '2023-02', 'Restauracja', '2023-02-10'),
(300.00, '2023-02', 'Elektronika', '2023-02-25'),
(150.75, '2023-03', 'Ubrania', '2023-03-05'),
(200.00, '2023-03', 'Transport', '2023-03-18');