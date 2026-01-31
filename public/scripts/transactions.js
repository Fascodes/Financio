// Transactions page JavaScript

// State
let currentPage = 1;
let totalPages = 0;
let searchTimeout = null;

/**
 * Initialize transactions page
 */
function initializeTransactions() {
    loadCategories();
    loadGroupUsers();
    loadTransactions();
    setDefaultDate();
}

/**
 * Set default date in add form to today
 */
function setDefaultDate() {
    const dateInput = document.getElementById('transactionDate');
    if (dateInput) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
}

/**
 * Load categories for filter and form
 */
function loadCategories() {
    fetch('/api/categories')
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to load categories');
            return response.json();
        })
        .then(function(data) {
            renderCategoryOptions(data.categories || []);
        })
        .catch(function(error) {
            console.error('Error loading categories:', error);
        });
}

/**
 * Render category options in selects
 */
function renderCategoryOptions(categories) {
    const filterSelect = document.getElementById('categoryFilter');
    const formSelect = document.getElementById('transactionCategory');

    // Filter select
    if (filterSelect) {
        filterSelect.innerHTML = '<option value="">All Categories</option>';
        categories.forEach(function(cat) {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            filterSelect.appendChild(option);
        });
    }

    // Form select
    if (formSelect) {
        formSelect.innerHTML = '<option value="">Select category...</option>';
        categories.forEach(function(cat) {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            formSelect.appendChild(option);
        });
    }
}

/**
 * Load group users for filter
 */
function loadGroupUsers() {
    fetch('/api/group-users')
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to load users');
            return response.json();
        })
        .then(function(data) {
            renderUserOptions(data.users || []);
        })
        .catch(function(error) {
            console.error('Error loading users:', error);
        });
}

/**
 * Render user options in member filter
 */
function renderUserOptions(users) {
    const select = document.getElementById('memberFilter');
    if (!select) return;

    select.innerHTML = '<option value="">All Members</option>';
    users.forEach(function(user) {
        const option = document.createElement('option');
        option.value = user.id;
        option.textContent = user.name;
        select.appendChild(option);
    });
}

/**
 * Load transactions with current filters
 */
function loadTransactions() {
    const params = buildQueryParams();
    const url = '/api/transactions?' + params.toString();

    const listContainer = document.getElementById('transactionsList');
    listContainer.innerHTML = '<div class="loading">Loading transactions...</div>';

    fetch(url)
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to load transactions');
            return response.json();
        })
        .then(function(data) {
            totalPages = data.pages || 0;
            currentPage = data.current_page || 1;
            renderTransactions(data.transactions || [], data.total || 0);
            renderPagination();
        })
        .catch(function(error) {
            console.error('Error loading transactions:', error);
            listContainer.innerHTML = '<div class="empty-state">Error loading transactions</div>';
        });
}

/**
 * Build query parameters from current filters
 */
function buildQueryParams() {
    const params = new URLSearchParams();
    params.append('page', currentPage);
    params.append('limit', 10);

    const search = document.getElementById('searchInput').value.trim();
    if (search) {
        params.append('search', search);
    }

    const categoryId = document.getElementById('categoryFilter').value;
    if (categoryId) {
        params.append('category_id', categoryId);
    }

    const userId = document.getElementById('memberFilter').value;
    if (userId) {
        params.append('user_id', userId);
    }

    return params;
}

/**
 * Render transactions list
 */
function renderTransactions(transactions, total) {
    const container = document.getElementById('transactionsList');
    const countEl = document.getElementById('transactionCount');

    // Update count
    countEl.textContent = total + ' Transaction' + (total !== 1 ? 's' : '');

    // Empty state
    if (transactions.length === 0) {
        container.innerHTML = '<div class="empty-state">No transactions found</div>';
        return;
    }

    // Render list
    let html = '';
    transactions.forEach(function(tx) {
        html += renderTransactionItem(tx);
    });
    container.innerHTML = html;
}

/**
 * Render single transaction item
 */
function renderTransactionItem(tx) {
    const categoryClass = getCategoryClass(tx.category);
    const formattedDate = formatDate(tx.date);
    const formattedAmount = formatAmount(tx.amount);

    return '<div class="transaction-item" data-id="' + tx.id + '">' +
        '<div class="transaction-info">' +
            '<div class="transaction-name">' +
                escapeHtml(tx.name) +
                ' <span class="transaction-category ' + categoryClass + '">' + escapeHtml(tx.category) + '</span>' +
            '</div>' +
            '<div class="transaction-meta">' + escapeHtml(tx.user_name || 'Unknown') + ' • ' + formattedDate + '</div>' +
        '</div>' +
        '<div class="transaction-amount">$' + formattedAmount + '</div>' +
    '</div>';
}

/**
 * Get CSS class for category
 */
function getCategoryClass(category) {
    if (!category) return 'category-default';
    
    const name = category.toLowerCase();
    if (name.includes('food') || name.includes('jedzenie')) return 'category-food';
    if (name.includes('entertainment') || name.includes('rozrywka')) return 'category-entertainment';
    if (name.includes('transport')) return 'category-transport';
    if (name.includes('shopping') || name.includes('zakupy')) return 'category-shopping';
    if (name.includes('utilities') || name.includes('media')) return 'category-utilities';
    if (name.includes('health') || name.includes('zdrowie')) return 'category-healthcare';
    return 'category-default';
}

/**
 * Format date to readable format
 */
function formatDate(dateStr) {
    if (!dateStr) return '';
    const date = new Date(dateStr);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

/**
 * Format amount with 2 decimal places
 */
function formatAmount(amount) {
    return parseFloat(amount).toFixed(2);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Render pagination buttons
 */
function renderPagination() {
    const container = document.getElementById('pagination');
    if (!container || totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // Previous button
    html += '<button onclick="goToPage(' + (currentPage - 1) + ')"' + 
            (currentPage === 1 ? ' disabled' : '') + '>← Prev</button>';

    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        if (shouldShowPage(i)) {
            html += '<button onclick="goToPage(' + i + ')"' +
                    (i === currentPage ? ' class="active"' : '') + '>' + i + '</button>';
        } else if (shouldShowEllipsis(i)) {
            html += '<span class="ellipsis">...</span>';
        }
    }

    // Next button
    html += '<button onclick="goToPage(' + (currentPage + 1) + ')"' +
            (currentPage === totalPages ? ' disabled' : '') + '>Next →</button>';

    container.innerHTML = html;
}

/**
 * Determine if page number should be shown
 */
function shouldShowPage(page) {
    return page === 1 || 
           page === totalPages || 
           Math.abs(page - currentPage) <= 1;
}

/**
 * Determine if ellipsis should be shown
 */
function shouldShowEllipsis(page) {
    return (page === 2 && currentPage > 3) ||
           (page === totalPages - 1 && currentPage < totalPages - 2);
}

/**
 * Navigate to specific page
 */
function goToPage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    loadTransactions();
}

/**
 * Handle search input with debounce
 */
function handleSearchInput(event) {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(function() {
        currentPage = 1;
        loadTransactions();
    }, 300);
}

/**
 * Apply filters and reload
 */
function applyFilters() {
    currentPage = 1;
    loadTransactions();
}

/**
 * Open add transaction modal
 */
function openAddModal() {
    document.getElementById('addModal').classList.add('open');
    document.getElementById('addTransactionForm').reset();
    setDefaultDate();
}

/**
 * Close add transaction modal
 */
function closeAddModal() {
    document.getElementById('addModal').classList.remove('open');
}

/**
 * Handle add transaction form submit
 */
function handleAddTransaction(event) {
    event.preventDefault();
    // TODO: Implement in Commit 5
    alert('Add transaction will be implemented in next commit');
    closeAddModal();
}
