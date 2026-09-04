import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sortable]').forEach(el => {
        Sortable.create(el, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-blue-50',
            draggable: '.draggable-item',
            onEnd: function (evt) {
                const taskId = evt.item.dataset.taskId;
                const items = el.querySelectorAll('[data-task-id]');
                const newOrder = Array.from(items).map((item, index) => ({
                    id: item.dataset.taskId,
                    priority: index + 1
                }));

                fetch('/tasks/priority', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ taskId, order: newOrder })
                }).then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                }).catch(error => {
                    console.error('Error updating priority:', error);
                });
            }
        });
    });
});
