document.addEventListener('DOMContentLoaded', () => {
    // Mobile menu
    document.getElementById('navToggle')?.addEventListener('click', () => {
        document.getElementById('navLinks').classList.toggle('show');
    });
    // Auto-dismiss alerts
    setTimeout(() => document.querySelectorAll('.alert').forEach(a => {
        a.style.transition = 'all 0.5s ease'; a.style.opacity = '0'; a.style.transform = 'translateY(-10px)';
        setTimeout(() => a.remove(), 500);
    }), 3500);
    // Delete confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            if(!confirm('⚠️ Confirmer la suppression ? Cette action est irréversible.')) e.preventDefault();
        });
    });
});