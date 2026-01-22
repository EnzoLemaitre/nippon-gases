(function () {
	"use strict";

	let activeCategories = [];
	let searchCompany = "";
	let searchLocation = "";

	document.addEventListener("DOMContentLoaded", function () {
		const resultsContainer = document.getElementById("results-container");
		const resultsCount = document.getElementById("results-count");
		const noResults = document.getElementById("no-results");
		const activeFiltersContainer = document.getElementById("active-filters");
		const filterPills = document.querySelectorAll(".filter-pill");
		const searchBtn = document.getElementById("search-btn");
		const resetBtn = document.getElementById("reset-btn");
		const companyInput = document.getElementById("company-search");
		const locationInput = document.getElementById("location-search");

		if (!resultsContainer) {
			return;
		}

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
			});
		}

		if (resetBtn) {
			resetBtn.addEventListener("click", function (e) {
				e.preventDefault();
				resetFilters();
			});
		}

		[companyInput, locationInput].forEach((input) => {
			if (input) {
				input.addEventListener("keypress", function (e) {
					if (e.key === "Enter") {
						e.preventDefault();
						applyFilters();
					}
				});
			}
		});

		function applyFilters() {
			activeCategories = [];
			filterPills.forEach((pill) => {
				if (pill.classList.contains("active")) {
					activeCategories.push(pill.getAttribute("data-category"));
				}
			});

			if (companyInput) searchCompany = companyInput.value.toLowerCase().trim();
			if (locationInput) searchLocation = locationInput.value.toLowerCase().trim();

			filterResults();
		}

		function geocodeLocation(location) {
			return new Promise((resolve, reject) => {
				if (typeof google === "undefined" || !google.maps) {
					reject("Google Maps not loaded");
					return;
				}

				const geocoder = new google.maps.Geocoder();
				geocoder.geocode({ address: location }, (results, status) => {
					if (status === "OK" && results[0]) {
						const loc = results[0].geometry.location;
						resolve({ lat: loc.lat(), lng: loc.lng() });
					} else {
						reject(status);
					}
				});
			});
		}

		function calculateDistance(lat1, lon1, lat2, lon2) {
			const R = 6371;
			const dLat = ((lat2 - lat1) * Math.PI) / 180;
			const dLon = ((lon2 - lon1) * Math.PI) / 180;
			const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) + Math.cos((lat1 * Math.PI) / 180) * Math.cos((lat2 * Math.PI) / 180) * Math.sin(dLon / 2) * Math.sin(dLon / 2);
			const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
			const distance = R * c;
			return distance;
		}

		async function findNearestDistributor(searchedLocation) {
			try {
				const searchCoords = await geocodeLocation(searchedLocation);
				const cards = document.querySelectorAll(".result-card");
				let nearestCard = null;
				let nearestDistance = Infinity;
				let nearestId = null;

				for (const card of cards) {
					if (activeCategories.length > 0) {
						const cardCategoriesAttr = card.getAttribute("data-categories") || "";
						const cardCategories = cardCategoriesAttr.split(",").filter((c) => c);
						const hasAllCategories = activeCategories.every((cat) => cardCategories.includes(cat));
						if (!hasAllCategories) continue;
					}

					if (searchCompany) {
						const title = card.getAttribute("data-title") || "";
						if (!title.includes(searchCompany)) continue;
					}

					const address = card.getAttribute("data-address");
					if (!address) continue;

					try {
						const distributorCoords = await geocodeLocation(address);

						const distance = calculateDistance(searchCoords.lat, searchCoords.lng, distributorCoords.lat, distributorCoords.lng);

						if (distance < nearestDistance) {
							nearestDistance = distance;
							nearestCard = card;
							nearestId = parseInt(card.getAttribute("data-distributor-id"));
						}
					} catch (error) {
						console.warn("Failed to geocode:", address, error);
					}
				}

				if (nearestCard) {
					cards.forEach((card) => (card.style.display = "none"));
					nearestCard.style.display = "block";

					const infoMessage = document.createElement("div");
					infoMessage.className = "nearest-distributor-info";
					infoMessage.innerHTML = `
						<p>
							${nearestDistance.toFixed(1)} km away from ${searchedLocation}
						</p>
					`;
					resultsContainer.insertBefore(infoMessage, resultsContainer.firstChild);

					if (typeof updateMapMarkers === "function") {
						updateMapMarkers([nearestId]);
					}

					if (resultsCount) {
						resultsCount.textContent = "1";
					}

					resultsContainer.style.display = "grid";
					if (noResults) noResults.style.display = "none";

					return true;
				}

				return false;
			} catch (error) {
				return false;
			}
		}

		async function filterResults() {
			const previousInfo = document.querySelector(".nearest-distributor-info");
			if (previousInfo) {
				previousInfo.remove();
			}

			const cards = document.querySelectorAll(".result-card");
			let visibleCount = 0;
			const visibleDistributorIds = [];

			cards.forEach((card) => {
				let showCard = true;

				if (activeCategories.length > 0) {
					const cardCategoriesAttr = card.getAttribute("data-categories") || "";
					const cardCategories = cardCategoriesAttr.split(",").filter((c) => c);
					const hasAllCategories = activeCategories.every((cat) => cardCategories.includes(cat));

					if (!hasAllCategories) {
						showCard = false;
					}
				}

				if (showCard && searchCompany) {
					const title = card.getAttribute("data-title") || "";
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

			if (visibleCount === 0 && searchLocation) {
				const foundNearest = await findNearestDistributor(searchLocation);

				if (foundNearest) {
					updateActiveFilters();
					return;
				}
			}

			if (typeof updateMapMarkers === "function") {
				updateMapMarkers(visibleDistributorIds);
			} else {
				console.warn("updateMapMarkers function not found");
			}

			if (resultsCount) {
				resultsCount.textContent = visibleCount;
			}

			if (visibleCount === 0) {
				if (resultsContainer) resultsContainer.style.display = "none";
				if (noResults) noResults.style.display = "block";
			} else {
				if (resultsContainer) resultsContainer.style.display = "grid";
				if (noResults) noResults.style.display = "none";
			}

			updateActiveFilters();
		}

		function resetFilters() {
			const previousInfo = document.querySelector(".nearest-distributor-info");
			if (previousInfo) {
				previousInfo.remove();
			}

			activeCategories = [];
			searchCompany = "";
			searchLocation = "";

			if (companyInput) companyInput.value = "";
			if (locationInput) locationInput.value = "";

			filterPills.forEach((pill) => pill.classList.remove("active"));

			filterResults();
		}

		function updateActiveFilters() {
			if (!activeFiltersContainer) return;

			activeFiltersContainer.innerHTML = "";

			if (activeCategories.length === 0 && !searchCompany && !searchLocation) {
				return;
			}

			const wrapper = document.createElement("div");
			wrapper.className = "active-filters-wrapper";

			const label = document.createElement("span");
			label.className = "active-filters-label";
			label.textContent = "Filtres actifs: ";
			wrapper.appendChild(label);

			activeCategories.forEach((category) => {
				const pill = document.querySelector(`[data-category="${category}"]`);
				if (pill) {
					const tag = document.createElement("span");
					tag.className = "active-filter-tag";
					tag.textContent = pill.textContent.trim() + " ";

					const removeBtn = document.createElement("button");
					removeBtn.className = "remove-filter";
					removeBtn.textContent = "×";
					removeBtn.type = "button";
					removeBtn.addEventListener("click", function (e) {
						e.preventDefault();
						pill.classList.remove("active");
						applyFilters();
					});

					tag.appendChild(removeBtn);
					wrapper.appendChild(tag);
				}
			});

			if (searchCompany) {
				const tag = document.createElement("span");
				tag.className = "active-filter-tag";
				tag.textContent = "Société: " + searchCompany + " ";

				const removeBtn = document.createElement("button");
				removeBtn.className = "remove-filter";
				removeBtn.textContent = "×";
				removeBtn.type = "button";
				removeBtn.addEventListener("click", function (e) {
					e.preventDefault();
					searchCompany = "";
					if (companyInput) companyInput.value = "";
					applyFilters();
				});

				tag.appendChild(removeBtn);
				wrapper.appendChild(tag);
			}

			if (searchLocation) {
				const tag = document.createElement("span");
				tag.className = "active-filter-tag";
				tag.textContent = "Lieu: " + searchLocation + " ";

				const removeBtn = document.createElement("button");
				removeBtn.className = "remove-filter";
				removeBtn.textContent = "×";
				removeBtn.type = "button";
				removeBtn.addEventListener("click", function (e) {
					e.preventDefault();
					searchLocation = "";
					if (locationInput) locationInput.value = "";
					applyFilters();
				});

				tag.appendChild(removeBtn);
				wrapper.appendChild(tag);
			}

			activeFiltersContainer.appendChild(wrapper);
		}
	});

	let currentDistributorIndex = 0;
	let visibleDistributors = [];

	window.showDistributorModal = function (distributorId) {
		const visibleCards = Array.from(document.querySelectorAll(".result-card")).filter((card) => card.style.display !== "none");

		visibleDistributors = visibleCards.map((card) => ({
			id: parseInt(card.getAttribute("data-distributor-id")),
			title: card.getAttribute("data-title"),
			address: card.getAttribute("data-address"),
			element: card,
		}));

		currentDistributorIndex = visibleDistributors.findIndex((d) => d.id === distributorId);

		if (currentDistributorIndex === -1) {
			return;
		}

		if (typeof zoomToMarker === "function") {
			zoomToMarker(distributorId);
		}

		const resultsContainer = document.getElementById("results-container");
		if (resultsContainer) {
			resultsContainer.style.display = "none";
		}

		const resultsHeader = document.querySelector(".results-header");
		if (resultsHeader) {
			resultsHeader.classList.add("show-modal");
		}

		const modal = document.getElementById("distributor-modal");
		if (modal) {
			modal.style.display = "block";

			setTimeout(() => {
				google.maps.event.trigger(map, "resize");
			}, 50);

			updateModalContent();
		}
	};

	function updateModalContent() {
		const distributor = visibleDistributors[currentDistributorIndex];

		const distributorId = distributor.id;

		const fullData = window.distributors.find((d) => d.id === distributorId);

		if (!fullData) {
			return;
		}

		document.getElementById("modal-title").textContent = fullData.title;

		const addressSection = document.getElementById("modal-address-section");
		if (fullData.address) {
			document.getElementById("modal-address").innerHTML = fullData.address.replace(/\n/g, "<br>");
			addressSection.style.display = "flex";
		} else {
			addressSection.style.display = "none";
		}

		const phoneSection = document.getElementById("modal-phone-section");
		if (fullData.phone) {
			const phoneLink = document.getElementById("modal-phone");
			phoneLink.textContent = fullData.phone;
			phoneSection.style.display = "flex";
		} else {
			phoneSection.style.display = "none";
		}

		const emailSection = document.getElementById("modal-email-section");
		if (fullData.email) {
			const emailLink = document.getElementById("modal-email");
			emailLink.textContent = fullData.email;
			emailSection.style.display = "flex";
		} else {
			emailSection.style.display = "none";
		}

		const faxSection = document.getElementById("modal-fax-section");
		if (fullData.fax) {
			document.getElementById("modal-fax").textContent = fullData.fax;
			faxSection.style.display = "flex";
		} else {
			faxSection.style.display = "none";
		}

		const categoriesContainer = document.getElementById("modal-categories");

		Array.from(categoriesContainer.querySelectorAll(".icon")).forEach((el) => el.remove());

		fullData.categories.forEach((cat) => {
			let icon = fullData.category_icon?.[cat.slug];

			if (!icon || !icon.url) return;

			const badge = document.createElement("div");
			badge.className = "icon";

			const img = document.createElement("img");
			img.src = icon.url;
			img.alt = icon.alt || cat.name;

			badge.appendChild(img);
			categoriesContainer.appendChild(badge);
		});

		categoriesContainer.style.display = categoriesContainer.children.length > 0 ? "flex" : "flex";

		const prevBtn = document.getElementById("modal-prev-btn");
		const nextBtn = document.getElementById("modal-next-btn");

		if (prevBtn) prevBtn.disabled = currentDistributorIndex === 0;
		if (nextBtn) nextBtn.disabled = currentDistributorIndex === visibleDistributors.length - 1;
	}

	window.navigateDistributor = function (direction) {
		const newIndex = currentDistributorIndex + direction;

		if (newIndex >= 0 && newIndex < visibleDistributors.length) {
			currentDistributorIndex = newIndex;
			updateModalContent();

			const newDistributorId = visibleDistributors[currentDistributorIndex].id;
			if (typeof zoomToMarker === "function") {
				zoomToMarker(newDistributorId);
			}
		}
	};

	window.closeDistributorModal = function () {
		const modal = document.getElementById("distributor-modal");
		if (modal) {
			modal.style.display = "none";
		}

		const resultsContainer = document.getElementById("results-container");
		if (resultsContainer) {
			resultsContainer.style.display = "grid";
		}
		const resultsHeader = document.querySelector(".results-header");
		if (resultsHeader) {
			resultsHeader.classList.remove("show-modal");
		}

		if (typeof resetMapZoom === "function") {
			const visibleIds = visibleDistributors.map((d) => d.id);
			resetMapZoom(visibleIds);
		}
	};

	window.toggleDetails = function (id) {
		showDistributorModal(parseInt(id));
	};
})();
