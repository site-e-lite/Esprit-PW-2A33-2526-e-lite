document.addEventListener('DOMContentLoaded', function() {
    var toggleBtn = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('closed');
            var icon = toggleBtn.querySelector('i');
            if (sidebar.classList.contains('closed')) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
        });
    }
});
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle (if you have a sidebar)
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('closed');
            const icon = toggleBtn.querySelector('i');
            if (sidebar.classList.contains('closed')) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            } else {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
            }
        });
    }
});
// Avatar preview and upload
document.addEventListener('DOMContentLoaded', function() {
    const avatarInput = document.getElementById('avatar-upload');
    const avatarForm = document.getElementById('avatar-form');
    const avatarImg = document.getElementById('avatar-img');
    const loadingDiv = document.getElementById('upload-loading');
    if (avatarInput && avatarForm) {
        avatarInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    if (avatarImg) avatarImg.src = event.target.result;
                    if (loadingDiv) loadingDiv.style.display = 'block';
                    avatarForm.submit();
                };
                reader.readAsDataURL(file);
            }
        });
    }
    const changeBtn = document.querySelector('.change-avatar-btn');
    if (changeBtn) {
        changeBtn.addEventListener('click', function() {
            avatarInput.click();
        });
    }
});
