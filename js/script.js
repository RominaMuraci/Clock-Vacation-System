document.addEventListener('DOMContentLoaded', function() {
    const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');
    
    allSideMenu.forEach(item => {
        const li = item.parentElement;

        item.addEventListener('click', function () {
            allSideMenu.forEach(i => {
                i.parentElement.classList.remove('active');
            });
            li.classList.add('active');
        });
    });

    // Ensure these elements are only accessed after DOM is loaded
    const menuBar = document.querySelector('#content nav .bx-menu');
    const sidebar = document.getElementById('sidebar');

    if (menuBar && sidebar) {
        menuBar.addEventListener('click', function () {
            sidebar.classList.toggle('hide');
        });
    } else {
        console.error('menuBar or sidebar element not found.');
    }

    // If you need to use these elements later, declare them here
    const switchMode = document.getElementById('switch-mode');

    if (switchMode) {
        switchMode.addEventListener('change', function () {
            if (this.checked) {
                document.body.classList.add('dark');
            } else {
                document.body.classList.remove('dark');
            }
        });
    }
});
