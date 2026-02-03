// Transactions page JavaScript

// State
let currentPage = 1;
let totalPages = 0;
let searchTimeout = null;

/**
 * Initialize transactions page
 */
function initializeTransactions() {
    loadCurrentUser();
    loadUserGroups();
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
        '<div class="transaction-right">' +
            '<div class="transaction-actions">' +
                '<button class="btn-action edit" onclick="openEditModal(' + tx.id + ')">Edit</button>' +
                '<button class="btn-action delete" onclick="openDeleteModal(' + tx.id + ', \'' + escapeHtml(tx.name).replace(/'/g, "\\'") + '\')">Delete</button>' +
            '</div>' +
            '<div class="transaction-amount">$' + formattedAmount + '</div>' +
        '</div>' +
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

// Funkcje formatDate, formatAmount, escapeHtml są teraz w main.js

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

    const form = document.getElementById('addTransactionForm');
    const submitBtn = form.querySelector('button[type="submit"]');

    // Disable button during submission
    submitBtn.disabled = true;
    submitBtn.textContent = 'Adding...';

    // Gather form data
    const data = {
        name: document.getElementById('transactionName').value.trim(),
        amount: parseFloat(document.getElementById('transactionAmount').value),
        category_id: parseInt(document.getElementById('transactionCategory').value),
        date: document.getElementById('transactionDate').value
    };

    // Validate
    if (!data.name || !data.amount || !data.category_id || !data.date) {
        alert('Please fill all required fields');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add Transaction';
        return;
    }

    // Send request
    fetch('/api/transactions/add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(response) {
        return response.json().then(function(json) {
            return { ok: response.ok, data: json };
        });
    })
    .then(function(result) {
        if (result.ok && result.data.success) {
            closeAddModal();
            form.reset();
            setDefaultDate();
            // Reload transactions to show the new one
            currentPage = 1;
            loadTransactions();
        } else {
            alert('Error: ' + (result.data.error || 'Failed to add transaction'));
        }
    })
    .catch(function(error) {
        console.error('Error adding transaction:', error);
        alert('Failed to add transaction. Please try again.');
    })
    .finally(function() {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Add Transaction';
    });
}

// ==================== EDIT FUNCTIONS ====================

/**
 * Open edit modal and load transaction data
 */
function openEditModal(transactionId) {
    fetch('/api/transactions/get?id=' + transactionId)
        .then(function(response) {
            if (!response.ok) throw new Error('Failed to load transaction');
            return response.json();
        })
        .then(function(data) {
            if (data.success && data.transaction) {
                populateEditForm(data.transaction);
                document.getElementById('editModal').classList.add('open');
            } else {
                alert('Transaction not found');
            }
        })
        .catch(function(error) {
            console.error('Error loading transaction:', error);
            alert('Failed to load transaction');
        });
}

/**
 * Populate edit form with transaction data
 */
function populateEditForm(tx) {
    document.getElementById('editTransactionId').value = tx.id;
    document.getElementById('editTransactionName').value = tx.name;
    document.getElementById('editTransactionAmount').value = tx.amount;
    document.getElementById('editTransactionDate').value = tx.date;
    
    // Populate categories in edit form
    const editSelect = document.getElementById('editTransactionCategory');
    const addSelect = document.getElementById('transactionCategory');
    
    // Copy options from add form to edit form
    editSelect.innerHTML = addSelect.innerHTML;
    editSelect.value = tx.category_id;
}

/**
 * Close edit modal
 */
function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}

/**
 * Handle edit transaction form submit
 */
function handleEditTransaction(event) {
    event.preventDefault();

    const form = document.getElementById('editTransactionForm');
    const submitBtn = form.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';

    const data = {
        id: parseInt(document.getElementById('editTransactionId').value),
        name: document.getElementById('editTransactionName').value.trim(),
        amount: parseFloat(document.getElementById('editTransactionAmount').value),
        category_id: parseInt(document.getElementById('editTransactionCategory').value),
        date: document.getElementById('editTransactionDate').value
    };

    if (!data.name || !data.amount || !data.category_id || !data.date) {
        alert('Please fill all required fields');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Changes';
        return;
    }

    fetch('/api/transactions/update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(function(response) {
        return response.json().then(function(json) {
            return { ok: response.ok, data: json };
        });
    })
    .then(function(result) {
        if (result.ok && result.data.success) {
            closeEditModal();
            loadTransactions();
        } else {
            alert('Error: ' + (result.data.error || 'Failed to update transaction'));
        }
    })
    .catch(function(error) {
        console.error('Error updating transaction:', error);
        alert('Failed to update transaction. Please try again.');
    })
    .finally(function() {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Changes';
    });
}

// ==================== DELETE FUNCTIONS ====================

/**
 * Open delete confirmation modal
 */
function openDeleteModal(transactionId, transactionName) {
    document.getElementById('deleteTransactionId').value = transactionId;
    document.getElementById('deleteItemName').textContent = transactionName;
    document.getElementById('deleteModal').classList.add('open');
}

/**
 * Close delete modal
 */
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('open');
}

/**
 * Confirm and execute delete
 */
function confirmDelete() {
    const transactionId = document.getElementById('deleteTransactionId').value;
    const deleteBtn = document.querySelector('#deleteModal .btn-danger');

    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';

    fetch('/api/transactions/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: parseInt(transactionId) })
    })
    .then(function(response) {
        return response.json().then(function(json) {
            return { ok: response.ok, data: json };
        });
    })
    .then(function(result) {
        if (result.ok && result.data.success) {
            closeDeleteModal();
            loadTransactions();
        } else {
            alert('Error: ' + (result.data.error || 'Failed to delete transaction'));
        }
    })
    .catch(function(error) {
        console.error('Error deleting transaction:', error);
        alert('Failed to delete transaction. Please try again.');
    })
    .finally(function() {
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
}
