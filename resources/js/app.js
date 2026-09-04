import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sortable]').forEach(el => {
        Sortable.create(el, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-blue-50',
            onEnd: function () {
                const items = el.querySelectorAll('[data-task-id]');
                const order = Array.from(items).map((item, index) => ({
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
                    body: JSON.stringify({ order })
                }).then(response => {
                    if (response.ok) {
                        items.forEach((item, index) => {
                            const badge = item.querySelector('.inline-flex');
                            if (badge) {
                                badge.textContent = `#${index + 1}`;
                            }
                        });
                    }
                });
            }
        });
    });
});
