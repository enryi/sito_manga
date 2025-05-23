function toggleUserMenu() {
    const dropdown = document.getElementById('user-dropdown');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}
function logout() {
    fetch('./php/logout.php', { method: 'POST' }).then(() => {
        window.location.href = 'index';
    });
}
document.addEventListener('click', (event) => {
    const dropdown = document.getElementById('user-dropdown');
    const userIcon = document.querySelector('.user-icon');
    if (dropdown && dropdown.style.display === 'block' && !dropdown.contains(event.target) && event.target !== userIcon) {
        dropdown.style.display = 'none';
    }
});
function toggleDescription(button) {
    const mangaItem = button.parentElement;
    const shortDescription = mangaItem.querySelector('.short-description');
    const fullDescription = mangaItem.querySelector('.full-description');
    const dots = mangaItem.querySelector('.dots');
    if (fullDescription.style.display === 'none') {
        fullDescription.style.display = 'inline';
        shortDescription.style.display = 'none';
        dots.style.display = 'none';
        button.textContent = 'Read less';
    } else {
        fullDescription.style.display = 'none';
        shortDescription.style.display = 'inline';
        dots.style.display = 'inline';
        button.textContent = 'Read more';
    }
}