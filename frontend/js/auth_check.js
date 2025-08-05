(function() {
    fetch('../api/v1/users/check_session.php', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
        },
    })
    .then(response => {
        if (!response.ok) {
            // If response is not ok (e.g., 401 Unauthorized), redirect to login
            window.location.href = 'login.html';
            throw new Error('Redirecting to login.'); // Stop further execution
        }
        return response.json();
    })
    .then(data => {
        // You can optionally use the user data here, for example, to display their name
        console.log('Session valid for user:', data.username);

        // Add a logout button if it doesn't exist
        if (!document.getElementById('logout-btn')) {
            const logoutButton = document.createElement('button');
            logoutButton.textContent = `Logout (${data.username})`;
            logoutButton.id = 'logout-btn';
            logoutButton.style.position = 'fixed';
            logoutButton.style.top = '20px';
            logoutButton.style.right = '20px';
            logoutButton.style.zIndex = '1000';
            logoutButton.onclick = function() {
                fetch('../api/v1/users/logout.php', { method: 'POST' })
                    .then(() => window.location.href = 'login.html');
            };
            document.body.appendChild(logoutButton);
        }
    })
    .catch(error => {
        // Catch any other errors (e.g., network errors) and redirect
        console.error('Auth check failed:', error.message);
        if (error.message !== 'Redirecting to login.') {
            window.location.href = 'login.html';
        }
    });
})();
