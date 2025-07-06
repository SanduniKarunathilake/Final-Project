function toggleReset() {
  const resetBox = document.getElementById('resetBox');
  resetBox.style.display = resetBox.style.display === 'block' ? 'none' : 'block';
}

function resetPassword() {
  const email = document.getElementById('resetEmail').value;
  if (email.trim() === '') {
    alert('Please enter your email.');
    return;
  }

  // Simulate sending reset request
  alert('Password reset link sent to: ' + email);

  // Clear field and hide box
  document.getElementById('resetEmail').value = '';
  document.getElementById('resetBox').style.display = 'none';
}
