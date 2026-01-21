let map = null;
let markers = [];
let geocoder = null;
let bounds = null;

let activeCategories = [];
let searchCompany = "";
let searchLocation = "";

function initMap() {
	map = new google.maps.Map(document.getElementById("distributor-map"), {
		zoom: 2,
		center: { lat: 46.603354, lng: 1.888334 },
	});

	geocoder = new google.maps.Geocoder();
	bounds = new google.maps.LatLngBounds();

	const cacheKey = "distributor_geocode_cache";
	const cache = JSON.parse(localStorage.getItem(cacheKey) || "{}");

	const queue = [...window.distributors];
	let index = 0;

	function processNext() {
		if (index >= queue.length) {
			if (!bounds.isEmpty()) map.fitBounds(bounds);
			localStorage.setItem(cacheKey, JSON.stringify(cache));
			return;
		}

		const item = queue[index++];
		const address = item.address;

		if (cache[address]) {
			addMarker(cache[address], item);
			setTimeout(processNext, 50);
			return;
		}

		geocoder.geocode({ address }, (results, status) => {
			if (status === "OK") {
				const loc = results[0].geometry.location;
				const position = { lat: loc.lat(), lng: loc.lng() };

				cache[address] = position;
				addMarker(position, item);
			} else {
				console.warn("Geocode failed:", address, status);
			}

			setTimeout(processNext, 400);
		});
	}

	function addMarker(position, distributorData) {
		const marker = new google.maps.Marker({
			map,
			position,
			title: distributorData.title,
			distributorId: distributorData.id,
		});

		marker.addListener("click", function () {
			const card = document.querySelector(`[data-distributor-id="${distributorData.id}"]`);
			if (card && card.style.display !== "none") {
				card.scrollIntoView({ behavior: "smooth", block: "center" });
				card.classList.add("highlighted");
				setTimeout(() => card.classList.remove("highlighted"), 2000);
			}
		});

		markers.push(marker);
		bounds.extend(position);
	}

	processNext();
}

function updateMapMarkers(visibleDistributorIds) {
	if (!map || markers.length === 0) {
		return;
	}

	const newBounds = new google.maps.LatLngBounds();
	let hasVisibleMarkers = false;

	markers.forEach((marker) => {
		const isVisible = visibleDistributorIds.includes(marker.distributorId);
		marker.setVisible(isVisible);

		if (isVisible) {
			newBounds.extend(marker.getPosition());
			hasVisibleMarkers = true;
		}
	});

	if (hasVisibleMarkers) {
		map.fitBounds(newBounds);

		google.maps.event.addListenerOnce(map, "bounds_changed", function () {
			if (map.getZoom() > 15) {
				map.setZoom(15);
			}
		});
	}
}

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

	console.log("Filters initialized");
	console.log("Filter pills found:", filterPills.length);

	filterPills.forEach((pill) => {
		pill.addEventListener("click", function (e) {
			e.preventDefault();
			const category = this.getAttribute("data-category");

			const index = activeCategories.indexOf(category);
			if (index > -1) {
				activeCategories.splice(index, 1);
			} else {
				activeCategories.push(category);
			}

			this.classList.toggle("active");
		});
	});

	if (searchBtn) {
		searchBtn.addEventListener("click", function (e) {
			e.preventDefault();
			if (companyInput) searchCompany = companyInput.value.toLowerCase().trim();
			if (locationInput) searchLocation = locationInput.value.toLowerCase().trim();
			filterResults();
		});
	}

	if (resetBtn) {
		resetBtn.addEventListener("click", function (e) {
			e.preventDefault();

			activeCategories = [];
			searchCompany = "";
			searchLocation = "";

			if (companyInput) companyInput.value = "";
			if (locationInput) locationInput.value = "";

			filterPills.forEach((pill) => pill.classList.remove("active"));
			filterResults();
		});
	}
	
	function filterResults() {
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

		updateMapMarkers(visibleDistributorIds);

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
					const index = activeCategories.indexOf(category);
					if (index > -1) {
						activeCategories.splice(index, 1);
					}
					pill.classList.remove("active");
					filterResults();
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
				filterResults();
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
				filterResults();
			});

			tag.appendChild(removeBtn);
			wrapper.appendChild(tag);
		}

		activeFiltersContainer.appendChild(wrapper);
	}
});

function toggleDetails(id) {
	const details = document.getElementById("details-" + id);
	if (!details) {
		return;
	}

	const btn = event.target.closest(".show-details-btn");
	if (!btn) {
		return;
	}

	const arrow = btn.querySelector(".btn-arrow");

	if (details.style.display === "none" || details.style.display === "") {
		details.style.display = "block";
		if (arrow) arrow.textContent = "↑";
	} else {
		details.style.display = "none";
		if (arrow) arrow.textContent = "→";
	}
}
