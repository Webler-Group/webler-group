// JavaScript to toggle the navigation menu
document.querySelector('.nav-toggle').addEventListener('click', function() {
    var navList = document.querySelector('nav ul');
    if (navList.style.display === 'none' || navList.style.display === '') {
        navList.style.display = 'block';
    } else {
        navList.style.display = 'none';
    }
});