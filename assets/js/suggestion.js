export function saveAdminNotes(suggestionId, notes) {
    const formData = new FormData();
    formData.append('suggestion_id', suggestionId);
    formData.append('admin_notes', notes);
    fetch('/api/suggestions/update-notes', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Notes enregistrées avec succès.');
                try {
                    document.getElementById('reviewModal').style.display = 'none';
                } catch {}
                location.reload();
            } else {
                alert('Erreur lors de l\'enregistrement des notes.');
            }
        });
}

export function approveSuggestion(suggestionId) {
    const formData = new FormData();
    formData.append('suggestion_id', suggestionId);
    fetch('/api/suggestions/approve', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Suggestion approuvée avec succès.');
                try {
                    document.getElementById('reviewModal').style.display = 'none';
                } catch {}
                location.reload();
            } else {
                alert('Erreur lors de l\'approbation de la suggestion.');
            }
        });
}

export function rejectSuggestion(suggestionId) {
    const formData = new FormData();
    formData.append('suggestion_id', suggestionId);
    fetch('/api/suggestions/reject', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Suggestion rejetée avec succès.');
                try {
                    document.getElementById('reviewModal').style.display = 'none';
                } catch {}
                location.reload();
            } else {
                alert('Erreur lors du rejet de la suggestion.');
            }
        });
}