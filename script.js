// script.js
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Confirm before deleting
    const deleteButtons = document.querySelectorAll('a[onclick*="confirm"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Una uhakika unataka kufuta hii?')) {
                e.preventDefault();
            }
        });
    });

    // File upload preview
    const fileInput = document.querySelector('input[type="file"]');
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
                // Show file name
                const label = this.nextElementSibling || this.parentElement.querySelector('.form-label');
                if (label) {
                    label.innerHTML = `<i class="fas fa-file me-2"></i>${fileName}`;
                }
            }
        });
    }

    // Character counter for textareas
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        const counter = document.createElement('small');
        counter.className = 'text-muted float-end';
        counter.textContent = `0/${textarea.maxLength || '∞'}`;
        textarea.parentNode.appendChild(counter);

        textarea.addEventListener('input', function() {
            counter.textContent = `${this.value.length}/${this.maxLength || '∞'}`;
            if (this.maxLength && this.value.length > this.maxLength * 0.9) {
                counter.style.color = '#e74c3c';
            } else {
                counter.style.color = '#7f8c8d';
            }
        });
    });
});