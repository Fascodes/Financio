<?php
$pageTitle = 'Transactions';
$pageStyle = 'transactions';
$pageScript = 'transactions';
$activePage = 'transactions';
include 'public/views/partials/header.php';
?>
<body onload="initializeTransactions()">
    <div class="main-content">
        <?php include 'public/views/partials/topbar.php'; ?>

        <div class="page-container">
            <!-- Header -->
            <div class="page-header">
                <div class="header-left">
                    <h1>Transactions</h1>
                    <p class="subtitle">Complete transaction history</p>
                </div>
                <div class="header-right">
                    <button class="btn btn-primary" onclick="openAddModal()">
                        + Add Transaction
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters-container">
                <div class="search-box">
                    <span class="search-icon">S</span>
                    <input type="text" id="searchInput" placeholder="Search transactions..." onkeyup="handleSearchInput(event)">
                </div>
                <div class="filter-group">
                    <select id="categoryFilter" onchange="applyFilters()">
                        <option value="">All Categories</option>
                    </select>
                </div>
                <div class="filter-group">
                    <select id="memberFilter" onchange="applyFilters()">
                        <option value="">All Members</option>
                    </select>
                </div>
            </div>

            <!-- Transactions List -->
            <div class="transactions-container">
                <div class="transactions-header">
                    <span id="transactionCount">0 Transactions</span>
                </div>
                <div class="transactions-list" id="transactionsList">
                    <div class="loading">Loading transactions...</div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination" id="pagination"></div>
        </div>
    </div>

    <?php include 'public/views/partials/navbar.php'; ?>

    <!-- Add Transaction Modal -->
    <div class="modal-overlay" id="addModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Add Transaction</h2>
                <button class="modal-close" onclick="closeAddModal()">×</button>
            </div>
            <form id="addTransactionForm" onsubmit="handleAddTransaction(event)">
                <div class="form-group">
                    <label for="transactionName">Name</label>
                    <input type="text" id="transactionName" name="name" required placeholder="e.g. Grocery shopping">
                </div>
                <div class="form-group">
                    <label for="transactionAmount">Amount</label>
                    <input type="number" id="transactionAmount" name="amount" step="0.01" min="0.01" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="transactionCategory">Category</label>
                    <select id="transactionCategory" name="category_id" required>
                        <option value="">Select category...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="transactionDate">Date</label>
                    <input type="date" id="transactionDate" name="date" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
                    <button type="submit" class="btn-primary">Add Transaction</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Transaction Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-header">
                <h2>Edit Transaction</h2>
                <button class="modal-close" onclick="closeEditModal()">×</button>
            </div>
            <form id="editTransactionForm" onsubmit="handleEditTransaction(event)">
                <input type="hidden" id="editTransactionId" name="id">
                <div class="form-group">
                    <label for="editTransactionName">Name</label>
                    <input type="text" id="editTransactionName" name="name" required placeholder="e.g. Grocery shopping">
                </div>
                <div class="form-group">
                    <label for="editTransactionAmount">Amount</label>
                    <input type="number" id="editTransactionAmount" name="amount" step="0.01" min="0.01" required placeholder="0.00">
                </div>
                <div class="form-group">
                    <label for="editTransactionCategory">Category</label>
                    <select id="editTransactionCategory" name="category_id" required>
                        <option value="">Select category...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editTransactionDate">Date</label>
                    <input type="date" id="editTransactionDate" name="date" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal modal-small">
            <div class="modal-header">
                <h2>Delete Transaction</h2>
                <button class="modal-close" onclick="closeDeleteModal()">×</button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this transaction?</p>
                <p class="delete-item-name" id="deleteItemName"></p>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="deleteTransactionId">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>
</body>
</html>
