const searchInput = document.querySelector(".input-wrapper input");
const searchIcon = document.querySelector(".input-wrapper .search-icon");

searchInput.addEventListener("focus", () => {
	searchIcon.style.opacity = "0";
	searchInput.style.paddingLeft = "8px";
});

searchInput.addEventListener("blur", () => {
	searchIcon.style.opacity = "1";
	searchInput.style.paddingLeft = "44px";
});