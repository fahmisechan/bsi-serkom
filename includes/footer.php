<?php if (isLoggedIn()): ?>
            </main>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Fungsi untuk menampilkan notifikasi
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg text-white z-50 ${bgColor}`;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
        
        // Konfirmasi delete
        function confirmDelete(message = 'Apakah Anda yakin ingin menghapus item ini?') {
            return confirm(message);
        }
        
        // Auto hide alerts
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert-auto-hide');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 3000);
        
        // Update task status
        function updateTaskStatus(taskId, status) {
            fetch('ajax/update-task-status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    task_id: taskId,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Status task berhasil diupdate!');
                    location.reload();
                } else {
                    showNotification('Gagal mengupdate status task!', 'error');
                }
            })
            .catch(error => {
                showNotification('Terjadi kesalahan!', 'error');
            });
        }
    </script>
</body>
</html> 