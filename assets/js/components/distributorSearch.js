(function () {
	"use strict";

	let activeCategories = [];
	let searchCompany = "";
	let searchLocation = "";

	const resultsContainer = document.getElementById("results-container");
	const resultsCount = document.getElementById("results-count");
	const noResults = document.getElementById("no-results");
	const filterPills = document.querySelectorAll(".filter-pill");
	const searchBtn = document.getElementById("search-btn");
	const resetBtn = document.getElementById("reset-btn");
	const companyInput = document.getElementById("company-search");
	const locationInput = document.getElementById("location-search");

	function init() {
		filterPills.forEach((pill) => {
			pill.addEventListener("click", function (e) {
				e.preventDefault();
				this.classList.toggle("active");
			});
		});

		if (searchBtn) {
			searchBtn.addEventListener("click", function (e) {
				e.preventDefault();
				applyFilters();
				filterResults();
			});
		}

		if (resetBtn) {
			resetBtn.addEventListener("click", function (e) {
				e.preventDefault();
				resetFilters();
			});
		}
	}

	function applyFilters() {
		activeCategories = [];
		filterPills.forEach((pill) => {
			if (pill.classList.contains("active")) {
				activeCategories.push(pill.getAttribute("data-category"));
			}
		});

		searchCompany = companyInput.value.toLowerCase().trim();
		searchLocation = locationInput.value.toLowerCase().trim();
	}

	function filterResults() {
		const cards = document.querySelectorAll(".result-card");
		let visibleCount = 0;
		const visibleDistributorIds = [];

		cards.forEach((card) => {
			let showCard = true;

			if (activeCategories.length > 0) {
				const cardCategories = card
					.getAttribute("data-categories")
					.split(",")
					.filter((c) => c);
				const hasAllCategories = activeCategories.every((cat) => cardCategories.includes(cat));

				if (!hasAllCategories) {
					showCard = false;
				}
			}

			if (showCard && searchCompany) {
				const title = card.getAttribute("data-title");
				if (!title.includes(searchCompany)) {
					showCard = false;
				}
			}

			if (showCard && searchLocation) {
				const address = card.getAttribute("data-address") || "";
				if (!address.includes(searchLocation)) {
					showCard = false;
				}
			}

			if (showCard) {
				card.style.display = "block";
				visibleCount++;
				const distributorId = parseInt(card.getAttribute("data-distributor-id"));
				visibleDistributorIds.push(distributorId);
			} else {
				card.style.display = "none";
			}
		});

		if (typeof updateMapMarkers === "function") {
			updateMapMarkers(visibleDistributorIds);
		}

		resultsCount.textContent = visibleCount;

		if (visibleCount === 0) {
			resultsContainer.style.display = "none";
			noResults.style.display = "block";
		} else {
			resultsContainer.style.display = "grid";
			noResults.style.display = "none";
		}
	}

	function resetFilters() {
		activeCategories = [];
		searchCompany = "";
		searchLocation = "";

		companyInput.value = "";
		locationInput.value = "";

		filterPills.forEach((pill) => pill.classList.remove("active"));

		filterResults();
	}

	window.removeFilter = function (category) {
		const pill = document.querySelector(`[data-category="${category}"]`);
		if (pill) {
			pill.classList.remove("active");
		}

		applyFilters();
	};

	window.removeSearchCompany = function () {
		searchCompany = "";
		companyInput.value = "";
		applyFilters();
	};

	window.removeSearchLocation = function () {
		searchLocation = "";
		locationInput.value = "";
		applyFilters();
	};

	window.toggleDetails = function (id) {
		const details = document.getElementById("details-" + id);
		const btn = event.target.closest(".show-details-btn");
		const arrow = btn.querySelector(".btn-arrow");

		if (details.style.display === "none" || details.style.display === "") {
			details.style.display = "block";
			arrow.textContent = "↑";
		} else {
			details.style.display = "none";
			arrow.textContent = "→";
		}
	};

	if (document.readyState === "loading") {
		document.addEventListener("DOMContentLoaded", init);
	} else {
		init();
	}
})();
