document.addEventListener("DOMContentLoaded", function() {
        fetch('/api/get_player_data')
        .then(response => response.json())
        .then(data => {
            document.getElementById('player-name').textContent = data.name;
            document.getElementById('nic-number').value = data.nic_number;
            document.getElementById('email-address').value = data.email;
            
          
            const scheduleTableBody = document.getElementById('schedule-table-body');
            data.schedule.forEach(schedule => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${schedule.day}</td>
                    <td>${schedule.schID}</td>
                    <td>${schedule.coachID}</td>
                    <td>${schedule.time}</td>
                    <td>${schedule.date}</td>
                    <td>${schedule.details}</td>
                `;
                scheduleTableBody.appendChild(row);
            });
        })
        .catch(error => console.error('Error fetching player data:', error));

   
    window.doPayment = function() {
        const playerId = document.getElementById('player-name').textContent; // Get player name or fetch Player ID from session or data
        window.location.href = `payment_form.html?player_id=${playerId}`;
    }

   
    window.logoutUser = function() {
        fetch('/api/logout')
            .then(() => {
                window.location.href = 'login.html';  
            })
            .catch(error => console.error('Error logging out:', error));
    }
});
