document.addEventListener("DOMContentLoaded", function () {
    const loadingScreen = document.getElementById("loading-screen");
    if (loadingScreen) {
        loadingScreen.classList.add("hidden");
    }
    // Show the main content if hidden
    if (document.getElementById("main-content").classList.contains("hidden")) {
        document.getElementById("main-content").classList.remove("hidden");
    }
});
