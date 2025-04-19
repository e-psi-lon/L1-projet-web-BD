<?php
include 'includes/header.php';

// Check if the user is logged in
error_log(json_encode($_SESSION));
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

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
    ORDER BY s.suggestion_id DESC
");
$stmt->execute(['user_id' => $user_id]);
$suggestions = $stmt->fetchAll();

?>

<div class="container">
    <div class="card">
        <h1 class="card-title">Mes suggestions</h1>
        
        <?php if (empty($suggestions)): ?>
            <p>Vous n'avez pas encore fait de suggestions. <a href="/suggestions/suggest">Proposer du contenu</a></p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Titre ou Nom</th>
                        <th>Statut</th>
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
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="/suggestions/suggest" class="btn">Proposer du nouveau contenu</a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
