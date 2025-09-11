function toggleEditorSelection(productId, isChecked) {
    fetch('action-php/add_editor_selection.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `product_id=${productId}&selected=${isChecked ? 1 : 0}`
    })
    .then(response => response.text())
    .then(result => {
        console.log('Editor selection updated:', result);
    })
    .catch(error => {
        console.error('Error updating selection:', error);
    });
}
