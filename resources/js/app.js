import Sortable from "sortablejs";

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-sortable]").forEach((el) => {
        Sortable.create(el, {
            handle: ".drag-handle",
            animation: 150,
            ghostClass: "bg-blue-50",
            draggable: ".draggable-item",
            onEnd: function (evt) {
                const item = evt.item;

                const taskId = item.dataset.taskId;
                const previousItem = item.previousElementSibling;
                const nextItem = item.nextElementSibling;

                const previousTaskId = previousItem?.dataset.taskId ?? null;
                const nextTaskId = nextItem?.dataset.taskId ?? null;

                fetch("/tasks/priority", {
                    method: "PUT",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                    body: JSON.stringify({
                        taskId,
                        previousTaskId,
                        nextTaskId,
                    }),
                })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error("Network response was not ok");
                        }
                        return response.json();
                    })
                    .catch((error) => {
                        console.error("Error updating priority:", error);
                    });
            },
        });
    });
});
