window.addEventListener("DOMContentLoaded", () => {
	const themeBtn = document.getElementById("toggle-theme-btn");

    const lightIcon = `
    	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
        	stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         	class="lucide lucide-sparkle-icon lucide-sparkle">
			<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
		</svg>
  	`;

  	const darkIcon = `
    	<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none"
        	stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
         	class="lucide lucide-moon-icon lucide-moon">
      		<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
    	</svg>
  	`;
        
	function setThemeIcon() {
    	const isLight = document.body.classList.contains("light");
    	themeBtn.innerHTML = isLight ? darkIcon : lightIcon;
  	}

  	function toggleTheme() {
    	document.body.classList.toggle("light");
    	const currentTheme = document.body.classList.contains("light") ? "light" : "dark";
    	localStorage.setItem("theme", currentTheme);
    	setThemeIcon();
  	}

    const savedTheme = localStorage.getItem("theme");

	if (savedTheme === "light") {
    	document.body.classList.add("light");
    }

    setThemeIcon();

    themeBtn.addEventListener("click", toggleTheme);
});