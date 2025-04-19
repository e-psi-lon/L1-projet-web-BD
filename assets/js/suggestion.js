export function saveAdminNotes(suggestionId, notes) {
    fetch('/api/suggestions/update-notes', {
        method: 'POST',
        body: JSON.stringify({
            suggestion_id: suggestionId,
            admin_notes: notes
        }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Notes enregistrées avec succès.');
            } else {
                alert('Erreur lors de l\'enregistrement des notes.');
            }
        });
}

export function approveSuggestion(suggestionId) {
    fetch('/api/suggestions/approve', {
        method: 'POST',
        body: JSON.stringify({ suggestion_id: suggestionId }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Suggestion approuvée avec succès.');
                document.getElementById('reviewModal').style.display = 'none';
                location.reload();
            } else {
                alert('Erreur lors de l\'approbation de la suggestion.');
            }
        });
}

export function rejectSuggestion(suggestionId) {
    fetch('/api/suggestions/reject', {
        method: 'POST',
        body: JSON.stringify({ suggestion_id: suggestionId }),
        headers: {
            'Content-Type': 'application/json'
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Suggestion rejetée avec succès.');
                document.getElementById('reviewModal').style.display = 'none';
                location.reload();
            } else {
                alert('Erreur lors du rejet de la suggestion.');
            }
        });
}