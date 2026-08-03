const filterButtons = document.querySelectorAll(".filter-btn");
const competitionCards = document.querySelectorAll(".competition-card");

filterButtons.forEach(button => {

    button.addEventListener("click", () => {

        filterButtons.forEach(btn => {
            btn.classList.remove("active");
        });

        button.classList.add("active");

        const filter = button.dataset.filter;

        competitionCards.forEach(card => {

            if (
                filter === "all" ||
                card.dataset.category === filter
            ) {
                card.style.display = "grid";
            } else {
                card.style.display = "none";
            }

        });

    });

});