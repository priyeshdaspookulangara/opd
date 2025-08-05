document.getElementById('login-form').addEventListener('submit', function(event) {
    event.preventDefault();

    const formData = {
        username: this.username.value,
        password: this.password.value
    };

    const responseDiv = document.getElementById('login-response');

    fetch('../api/v1/users/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(formData)
    })
    .then(response => {
        if (!response.ok) {
            // Get the error message from the response body
            return response.json().then(err => { throw new Error(err.message) });
        }
        return response.json();
    })
    .then(data => {
        if (data.message === 'Login successful.') {
            responseDiv.innerHTML = '<p style="color:green;">Login successful! Redirecting...</p>';

            // Redirect based on role
            if (data.role === 'Admin') {
                window.location.href = 'admin.html';
            } else {
                window.location.href = 'index.html';
            }
        }
    })
    .catch(error => {
        responseDiv.innerHTML = `<p style="color:red;">${error.message || 'An error occurred.'}</p>`;
        console.error('Login error:', error);
    });
});
