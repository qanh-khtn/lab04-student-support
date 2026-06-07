(function () {
    var saved      = localStorage.getItem('theme');
    var preferDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    document.documentElement.setAttribute('data-theme', saved || (preferDark ? 'dark' : 'light'));
}());

document.addEventListener('DOMContentLoaded', function () {
    var root = document.documentElement;
    var btn  = document.getElementById('theme-toggle');

    if (!btn) return;

    function updateTitle() {
        btn.title = root.getAttribute('data-theme') === 'dark'
            ? 'Chuyển sang chế độ sáng'
            : 'Chuyển sang chế độ tối';
    }

    updateTitle();

    btn.addEventListener('click', function () {
        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
        document.body.classList.add('theme-transitioning');
        root.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateTitle();
        setTimeout(function () { document.body.classList.remove('theme-transitioning'); }, 400);
    });
});
