<?php

include 'includes/header.php';

require_once 'includes/utils.php';

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
// Fetch all suggestions
$pdo = getDbConnection();
$stmt = $pdo->prepare("
    SELECT s.suggestion_id, s.suggestion_type, s.status, s.admin_notes, 
           CASE 
               WHEN s.suggestion_type = 'author' THEN a.author_name
               WHEN s.suggestion_type = 'book' THEN b.title
               WHEN s.suggestion_type = 'chapter' THEN c.title
           END as title, u.username
    FROM suggestions s
    LEFT JOIN author_suggestions a ON s.suggestion_id = a.suggestion_id AND s.suggestion_type = 'author'
    LEFT JOIN book_suggestions b ON s.suggestion_id = b.suggestion_id AND s.suggestion_type = 'book'
    LEFT JOIN chapter_suggestions c ON s.suggestion_id = c.suggestion_id AND s.suggestion_type = 'chapter'
    JOIN users u ON s.user_id = u.user_id
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
$stmt->execute();
$suggestions = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$suggestions) {
    $suggestions = [];
}
$_SESSION['from'] = 'admin';
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
        <h1 class="card-title">Gestion des suggestions</h1>

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
                    <th>Créateur</th>
                    <th>Actions</th>
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
                            <?= htmlspecialchars($suggestion['username']) ?>
                        </td>
                        <td>
                            <a href="/suggestions/<?= $suggestion['suggestion_id'] ?>/view" class="btn btn-small">Détails</a>
                            <?php if ($suggestion['status'] === 'pending'): ?>
                                <button type="button" class="btn btn-small btn-success accept" id="approve-<?= $suggestion['suggestion_id'] ?>">Approuver</button>
                                <button type="button" class="btn btn-small btn-danger reject" id="reject-<?= $suggestion['suggestion_id'] ?>">Rejeter</button>
                                <button type="button" class="btn btn-small btn-secondary review" id="review-<?= $suggestion['suggestion_id'] ?>">Examiner</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="module">
    import { approveSuggestion, rejectSuggestion, saveAdminNotes } from '/assets/js/suggestion.js';
    document.querySelectorAll('.accept').forEach(button => {
        button.addEventListener('click', function () {
            const suggestionId = this.id.split('-')[1];
            approveSuggestion(suggestionId);
        });
    });

    document.querySelectorAll('.reject').forEach(button => {
        button.addEventListener('click', function () {
            const suggestionId = this.id.split('-')[1];
            rejectSuggestion(suggestionId);
        });
    });

    document.querySelectorAll('.review').forEach(button => {
        button.addEventListener('click', function () {
            const suggestionId = this.id.split('-')[1];

            // Créer la structure du modal si elle n'existe pas
            if (!document.getElementById('reviewModal')) {
                const modal = document.createElement('div');
                modal.id = 'reviewModal';
                modal.className = 'modal';
                modal.innerHTML = `
                <div class="modal-content">
                    <span class="modal-close">&times;</span>
                    <h2>Examiner la suggestion</h2>
                    <div id="suggestionDetails"></div>
                    <form id="reviewForm">
                        <div class="form-group">
                            <label for="adminNotes">Notes post-examen</label>
                            <textarea id="adminNotes" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="button" id="saveNotes" class="btn btn-primary">Enregistrer les notes</button>
                            <button type="button" id="approveSuggestion" class="btn btn-success">Approuver la suggestion</button>
                            <button type="button" id="rejectSuggestion" class="btn btn-danger">Rejeter la suggestion</button>
                        </div>
                    </form>
                </div>
            `;
                document.body.appendChild(modal);

                // Gérer la fermeture de la modal
                document.querySelector('.modal-close').addEventListener('click', () => {
                    document.getElementById('reviewModal').style.display = 'none';
                });
            }

            // Afficher la modal
            const modal = document.getElementById('reviewModal');
            modal.style.display = 'block';

            // Charger les détails de la suggestion
            fetch(`/api/suggestion-details?id=${suggestionId}`)
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.error) {
                        alert(data.error);
                        return;
                    }

                    const detailsContainer = document.getElementById('suggestionDetails');
                    let detailsHtml = '';

                    // Afficher les détails selon le type de suggestion
                    if (data.type === 'author') {
                        detailsHtml = `
                        <p><strong>Type :</strong> Auteur</p>
                        <p><strong>Nom :</strong> ${data.details.author_name}</p>
                        <p><strong>Dates :</strong> ${data.details.birth_year || ''} - ${data.details.death_year || ''}</p>
                        <p><strong>Biographie :</strong> ${data.details.biography || ''}</p>
                    `;
                    } else if (data.type === 'book') {
                        detailsHtml = `
                        <p><strong>Type :</strong> Livre</p>
                        <p><strong>Titre :</strong> ${data.details.title}</p>
                        <p><strong>Auteur :</strong> ${data.details.author_name || ''}</p>
                        <p><strong>Description :</strong> ${data.details.description || ''}</p>
                    `;
                    } else if (data.type === 'chapter') {
                        detailsHtml = `
                        <p><strong>Type :</strong> Chapitre</p>
                        <p><strong>Titre :</strong> ${data.details.title} (Chapitre/Partie n° ${data.details.chapter_number || 0})</p>
                        <p><strong>Livre :</strong> ${data.details.book_title || ''}</p>
                        <p><strong>Contenu :</strong> <pre>${data.details.content || ''}</pre></p>
                    `;
                    }

                    detailsContainer.innerHTML = detailsHtml;
                    document.getElementById('adminNotes').value = data.admin_notes || '';

                    // Configuration des boutons d'action
                    const currentSuggestionId = suggestionId;

                    document.getElementById('saveNotes').onclick = () => {
                        const notes = document.getElementById('adminNotes').value;
                        saveAdminNotes(currentSuggestionId, notes);
                    };

                    document.getElementById('approveSuggestion').onclick = () => {
                        approveSuggestion(currentSuggestionId);
                    };

                    document.getElementById('rejectSuggestion').onclick = () => {
                        rejectSuggestion(currentSuggestionId);
                    };
                })
                .catch(error => {
                    console.error('Erreur lors du chargement des détails:', error);
                    alert('Impossible de charger les détails de la suggestion.');
                });
        });
    });
</script>

<?php
include 'includes/footer.php';
?>