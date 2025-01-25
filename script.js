
// for login script//
const container = document.getElementById('container');
const registerBtn = document.getElementById('register');
const loginBtn = document.getElementById('login');

registerBtn.addEventListener('click', () => {
    container.classList.add("active");
});

loginBtn.addEventListener('click', () => {
    container.classList.remove("active");
});



// for home script//

const menuBtn = document.getElementById("menu-btn");
const navLinks = document.getElementById("navigation-links");
const menuBtnIcon = menuBtn.querySelector("i");

menuBtn.addEventListener("click", (e) => {
  navLinks.classList.toggle("open");

  const isOpen = navLinks.classList.contains("open");
  menuBtnIcon.setAttribute(
    "class",
    isOpen ? "ri-close-line" : "ri-menu-3-line"
  );
});

navLinks.addEventListener("click", (e) => {
  navLinks.classList.remove("open");
  menuBtnIcon.setAttribute("class", "ri-menu-3-line");
});

const scrollRevealOptions = {
  distance: "50px",
  origin: "bottom",
  duration: 1000,
};

// header container
ScrollReveal().reveal(".header__content h1", {
  ...scrollRevealOptions,
});

ScrollReveal().reveal(".header__btn", {
  ...scrollRevealOptions,
  delay: 500,
});

// service container
ScrollReveal().reveal(".service__card", {
  ...scrollRevealOptions,
  interval: 500,
});

// product container
ScrollReveal().reveal(".swiper-slide", {
  ...scrollRevealOptions,
  interval: 500,
});


// contact container
var swiper = new Swiper('.footer__socials_swiper', {
  slidesPerView: 'auto',
  spaceBetween: 25,
  pagination: {
    el: '.footer__socials',
    clickable: true,
  },
});

const swiper = new Swiper(".swiper", {
  loop: true,
  pagination: {
    el: ".swiper-pagination",
  },
});





