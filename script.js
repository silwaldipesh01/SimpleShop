// Wait until the entire HTML document has loaded before running this script.
// This ensures that all elements (like the slider and cart link) exist in the DOM.
document.addEventListener("DOMContentLoaded", () => {

  // Create a new Swiper instance targeting the element with class "swiper".
  // Swiper is the library that powers the image slider/banner.
  new Swiper(".swiper", {
    loop: true, // Makes the slider loop back to the first slide after the last one.

    // Pagination settings (the clickable dots under the slider).
    pagination: {
      el: ".swiper-pagination", // Target the element with class "swiper-pagination".
      clickable: true,          // Allow users to click dots to jump to a slide.
    },

    // Navigation settings (the left/right arrows).
    navigation: {
      nextEl: ".swiper-button-next", // Target the "next" arrow element.
      prevEl: ".swiper-button-prev", // Target the "previous" arrow element.
    }
  });
});
