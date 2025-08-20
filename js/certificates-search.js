document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("certificateSearchForm");
    const queryInput = document.getElementById("query");

    form.addEventListener("submit", function (e) {
        if (queryInput.value.trim() === "") {
            e.preventDefault();
            queryInput.classList.add("is-invalid");

            if (!document.getElementById("error-feedback")) {
                const error = document.createElement("div");
                error.id = "error-feedback";
                error.classList.add("invalid-feedback");
                error.textContent = "Por favor ingresa un valor para buscar.";
                queryInput.parentNode.appendChild(error);
            }
        }
    });

    queryInput.addEventListener("input", function () {
        if (queryInput.value.trim() !== "") {
            queryInput.classList.remove("is-invalid");
            const feedback = document.getElementById("error-feedback");
            if (feedback) feedback.remove();
        }
    });
});
