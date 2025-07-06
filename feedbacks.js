function loadFeedbacks() {
    fetch("get_feedback.php")
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("feedback-table-body");
            tbody.innerHTML = ""; // Clear previous data
            data.forEach(item => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${item.pid}</td>
                    <td>${item.cid}</td>
                    <td>${item.date}</td>
                    <td>${item.feedback}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error("Error fetching feedbacks:", error);
        });
}

document.addEventListener("DOMContentLoaded", () => {
    loadFeedbacks();
    setInterval(loadFeedbacks, 10000);
});
