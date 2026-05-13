document.addEventListener('DOMContentLoaded', () => {
    
    // 1. Recherche instantanée sur la page Missions
    const searchInput = document.querySelector('input[name="search"]');
    const categorySelect = document.querySelector('select[name="category"]');
    
    if (searchInput || categorySelect) {
        const cards = document.querySelectorAll('.mission-card');
        
        const filterMissions = () => {
            const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            const category = categorySelect ? categorySelect.value.toLowerCase() : '';
            
            cards.forEach(card => {
                const title = card.querySelector('h3').innerText.toLowerCase();
                const desc = card.querySelector('p').innerText.toLowerCase();
                const cat = card.querySelector('.mission-category').innerText.toLowerCase();
                
                const matchesSearch = title.includes(searchTerm) || desc.includes(searchTerm);
                const matchesCategory = !category || cat.includes(category);
                
                if (matchesSearch && matchesCategory) {
                    card.style.display = 'block';
                    card.style.animation = 'fadeIn 0.3s ease';
                } else {
                    card.style.display = 'none';
                }
            });
        };

        if(searchInput) searchInput.addEventListener('input', filterMissions);
        if(categorySelect) categorySelect.addEventListener('change', filterMissions);
    }

    // 2. Menu Mobile Toggle
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    const nav = document.querySelector('.nav');
    
    if (mobileBtn && nav) {
        mobileBtn.addEventListener('click', () => {
            if (nav.style.display === 'flex') {
                nav.style.display = 'none';
            } else {
                nav.style.display = 'flex';
                nav.style.flexDirection = 'column';
                nav.style.position = 'absolute';
                nav.style.top = '72px';
                nav.style.left = '0';
                nav.style.right = '0';
                nav.style.background = 'var(--bg-primary)';
                nav.style.padding = '1rem';
                nav.style.borderBottom = '1px solid var(--border)';
                nav.style.zIndex = '99';
            }
        });
    }

    // 3. Animation de disparition des alertes
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        }, 3000);
    });
});