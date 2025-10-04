// Slider
let currentIndex = 0;
const slides = document.querySelectorAll('.slide');

function showSlide(index) {
  const container = document.querySelector('.slides');
  if(index >= slides.length) currentIndex = 0;
  else if(index < 0) currentIndex = slides.length - 1;
  else currentIndex = index;
  container.style.transform = `translateX(-${currentIndex * 100}%)`;
}

document.querySelector('.prev').addEventListener('click', () => showSlide(currentIndex-1));
document.querySelector('.next').addEventListener('click', () => showSlide(currentIndex+1));
setInterval(() => showSlide(currentIndex+1), 5000);

// FAQ Accordion
document.querySelectorAll('.faq-question').forEach(btn => {
  btn.addEventListener('click', () => {
    const answer = btn.nextElementSibling;
    answer.style.display = answer.style.display === 'block' ? 'none' : 'block';
  });
});

// Modal
const modal = document.getElementById("myModal");
document.getElementById("openModal").onclick = () => modal.style.display = "block";
document.querySelector(".close").onclick = () => modal.style.display = "none";
window.onclick = e => { if (e.target == modal) modal.style.display = "none"; };
