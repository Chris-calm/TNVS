 const openModalBtn = document.getElementById("openModal");
    const closeModalBtn = document.getElementById("closeModal");
    const modal = document.getElementById("facilityModal");

    openModalBtn.addEventListener("click", () => {
      modal.classList.remove("hidden");
    });

    closeModalBtn.addEventListener("click", () => {
      modal.classList.add("hidden");
    });

    // Close modal on outside click
    modal.addEventListener("click", (e) => {
      if (e.target === modal) {
        modal.classList.add("hidden");
      }
    });