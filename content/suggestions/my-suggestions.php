<?php
include 'includes/header.php';

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: /auth/login');
    exit;
}

unset($_SESSION['from']);

$user_id = $_SESSION['user']['id'];

// Get user suggestions
$pdo = getDbConnection();
$stmt = $pdo->prepare("
    SELECT s.suggestion_id, s.suggestion_type, s.status, s.admin_notes, 
           CASE 
               WHEN s.suggestion_type = 'author' THEN a.author_name
               WHEN s.suggestion_type = 'book' THEN b.title
               WHEN s.suggestion_type = 'chapter' THEN c.title
           END as title
    FROM suggestions s
    LEFT JOIN author_suggestions a ON s.suggestion_id = a.suggestion_id AND s.suggestion_type = 'author'
    LEFT JOIN book_suggestions b ON s.suggestion_id = b.suggestion_id AND s.suggestion_type = 'book'
    LEFT JOIN chapter_suggestions c ON s.suggestion_id = c.suggestion_id AND s.suggestion_type = 'chapter'
    WHERE s.user_id = :user_id 
    ORDER BY 
        CASE 
            WHEN s.status = 'pending' THEN 1
            WHEN s.status = 'reviewed' THEN 1
            WHEN s.status = 'approved' THEN 2
            WHEN s.status = 'rejected' THEN 3
            ELSE 4
        END,
        s.suggestion_id DESC
");
$stmt->execute(['user_id' => $user_id]);
$suggestions = $stmt->fetchAll();

?>
<script type="module">
    import { filterSuggestions } from '/assets/js/search.js';

    // For all checkboxes and input, when you keyup in the input, call the function filterSuggestions
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('onclick', filterSuggestions);
    });
    document.getElementById('suggestion-search').addEventListener('keyup', filterSuggestions);
</script>
<div class="container">
    <div class="card">
        <h1 class="card-title">Mes suggestions</h1>
        
        <?php if (empty($suggestions)): ?>
            <p>Vous n'avez pas encore fait de suggestions. <a href="/suggestions/suggest">Proposer du contenu</a></p>
        <?php else: ?>
            <div class="search-container">
                <div class="search-box">
                    <label for="suggestion-search"></label>
                    <input type="text" id="suggestion-search" placeholder="Rechercher dans les suggestions...">
                </div>
                <div class="filters-container">
                    <div class="filters" id="type-filter">
                        <div class="filter-item">
                            <input name="type" value="author" type="checkbox" id="type-author" checked/>
                            <label for="type-author">Auteur</label>
                        </div>
                        <div class="filter-item">
                            <input name="type" value="book" type="checkbox" id="type-book" checked/>
                            <label for="type-book">Livre</label>
                        </div>
                        <div class="filter-item">
                            <input name="type" value="chapter" type="checkbox" id="type-chapter" checked/>
                            <label for="type-chapter">Chapitre</label>
                        </div>
                    </div>
                    <div class="filters" id="status-filter">
                        <div class="filter-item">
                            <input name="status" value="pending" type="checkbox" id="status-pending" checked/>
                            <label for="status-pending">En attente</label>
                        </div>
                        <div class="filter-item">
                            <input name="status" value="reviewed" type="checkbox" id="status-reviewed" checked/>
                            <label for="status-reviewed">Examinée</label>
                        </div>
                        <div class="filter-item">
                            <input name="status" value="approved" type="checkbox" id="status-approved" checked/>
                            <label for="status-approved">Approuvée</label>
                        </div>
                        <div class="filter-item">
                            <input name="status" value="refused" type="checkbox" id="status-refused" checked/>
                            <label for="status-refused">Rejetée</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-container">
                <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Titre ou Nom</th>
                        <th>Statut</th>
                        <th>Actions</th>
                        <th>Notes Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suggestions as $suggestion): ?>
                        <tr>
                            <td>
                                <?php if ($suggestion['suggestion_type'] === 'author'): ?>
                                    Auteur
                                <?php elseif ($suggestion['suggestion_type'] === 'book'): ?>
                                    Livre
                                <?php elseif ($suggestion['suggestion_type'] === 'chapter'): ?>
                                    Chapitre
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($suggestion['title']) ?></td>
                            <td>
                                <?php if ($suggestion['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">En attente</span>
                                <?php elseif ($suggestion['status'] === 'reviewed'): ?>
                                    <span class="badge">Examinée</span>
                                <?php elseif ($suggestion['status'] === 'approved'): ?>
                                    <span class="badge badge-success">Approuvée</span>
                                <?php elseif ($suggestion['status'] === 'rejected'): ?>
                                    <span class="badge badge-danger">Rejetée</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/suggestions/<?= $suggestion['suggestion_id'] ?>/view" class="btn btn-small">Détails</a>
                                <?php if ($suggestion['status'] === 'pending'): ?>
                                    <a href="/suggestions/<?= $suggestion['suggestion_id'] ?>/edit" class="btn btn-small btn-secondary">Modifier</a>
                                <?php endif; ?>
                            </td>
                            <td>
                            <?php if ($suggestion['admin_notes']): ?>
                                    <button class="btn btn-small" data-toggle="tooltip" title="<?= htmlspecialchars($suggestion['admin_notes']) ?>">
                                        <i class="icon lucide-alert-triangle"></i>
                                    </button>
                            <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="/suggestions/suggest" class="btn">Proposer du nouveau contenu</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
