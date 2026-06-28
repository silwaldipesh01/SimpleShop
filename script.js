document.addEventListener("DOMContentLoaded", () => {
  // Swiper
  if (typeof Swiper !== "undefined" && document.querySelector(".swiper")) {
    new Swiper(".swiper", {
      loop: true,
      pagination: {
        el: ".swiper-pagination",
        clickable: true,
      },
      navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
      }
    });
  }

  // Orders countdown timer
  const countdownEls = document.querySelectorAll(".delivery-countdown");

  countdownEls.forEach((el) => {
    const deliveryDate = new Date(el.dataset.delivery).getTime();

    const updateCountdown = () => {
      const now = new Date().getTime();
      const distance = deliveryDate - now;

      if (distance <= 0) {
        el.textContent = "Delivered";
        return;
      }

      const days = Math.floor(distance / (1000 * 60 * 60 * 24));
      const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
      const seconds = Math.floor((distance % (1000 * 60)) / 1000);

      el.textContent = `Delivery in ${days}d ${hours}h ${minutes}m ${seconds}s`;
    };

    updateCountdown();
    setInterval(updateCountdown, 1000);
  });
});