<?php
include 'includes/header.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: /auth/login');
    exit;
}
// Check if the user is an admin
if (!$_SESSION['user']['is_admin']) {
    header('Location: /index');
    exit;
}

$pdo = getDbConnection();
$pdo = getDbConnection();
$stmt = $pdo->prepare("
    SELECT u.user_id, u.username, u.email, u.is_admin,
    COUNT(s.suggestion_id) AS suggestions_count,
    SUM(CASE WHEN s.status = 'approved' THEN 1 ELSE 0 END) AS approved_count
    FROM users u
    LEFT JOIN suggestions s ON u.user_id = s.user_id
    GROUP BY u.user_id, u.username, u.email, u.is_admin
");
$stmt->execute();
$users = $stmt->fetchAll();

?>
    <script type="module">import { filterUserTable } from '/assets/js/search.js';

        const createChevron = (type) => lucide.createElement(type, {
            class: ['chevron'],
            width: 16,
            height: 16,
        });

        const updateChevron = (element, chevron) => {
            const existingChevron = element.querySelector('.chevron');
            if (existingChevron) {
                existingChevron.replaceWith(chevron);
            } else {
                element.prepend(chevron);
            }
        };

        const updateTableHeaders = () => {
            document.querySelectorAll('.sortable').forEach(header => {
                const chevronType = header.classList.contains('asc') ? lucide.ChevronUp :
                    header.classList.contains('desc') ? lucide.ChevronDown :
                        lucide.ChevronsUpDown;
                updateChevron(header, createChevron(chevronType));
            });
        };

        const sortTable = (header) => {
            const newSort = header.classList.toggle('asc') ? 'asc' : 'desc';
            header.classList.toggle('desc', newSort === 'desc');
            document.querySelectorAll('.sortable').forEach(h => h !== header && h.classList.remove('asc', 'desc'));

            const rows = Array.from(document.querySelectorAll('#user-table tbody tr'));
            rows.sort((a, b) => {
                const aValue = a.children[header.cellIndex].textContent;
                const bValue = b.children[header.cellIndex].textContent;
                return newSort === 'asc' ? aValue.localeCompare(bValue) : bValue.localeCompare(aValue);
            });

            const tbody = document.querySelector('#user-table tbody');
            tbody.replaceChildren(...rows);
            updateTableHeaders();
        };

        document.getElementById('user-search').addEventListener('keyup', filterUserTable);

        document.querySelectorAll('.sortable').forEach(header => {
            header.addEventListener('click', () => sortTable(header));
        });

        updateTableHeaders();
    </script>
    <div class="container">
        <div class="card">
            <h1 class="card-title">Gestion des utilisateurs</h1>

            <div class="search-container">
                <div class="search-box">
                    <label for="user-search"></label>
                    <input type="text" id="user-search" placeholder="Rechercher un utilisateur...">
                </div>
            </div>

            <div class="table-container">
                <table class="table" id="user-table">
                    <thead>
                    <tr>
                        <th class="sortable desc" id="sort-user_id">ID</th>
                        <th class="sortable" id="sort-username">Nom d'utilisateur</th>
                        <th class="sortable" id="sort-email">Email</th>
                        <th class="sortable" id="sort-suggestions_approved">Suggestions approuvées</th>
                        <th class="sortable" id="sort-is_admin">Admin</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= h($user['user_id']) ?></td>
                            <td><?= h($user['username']) ?></td>
                            <td><?= h($user['email']) ?></td>
                            <td><?= h($user['approved_count']) ?>/<?= h($user['suggestions_count']) ?></td>
                            <td><?= $user['is_admin'] ? 'Oui' : 'Non' ?></td>
                            <td>
                                <?php if ($user['user_id'] !== $_SESSION['user']['id']): ?>
                                    <button class="btn btn-small btn-danger">Supprimer</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>