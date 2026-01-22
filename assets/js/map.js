let map = null;
let markers = [];
let geocoder = null;
let bounds = null;

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

		if (!address) {
			console.warn('No address for:', item.title);
			setTimeout(processNext, 50);
			return;
		}

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

window.zoomToMarker = function(distributorId) {
	if (!map || markers.length === 0) {
		return;
	}

	const marker = markers.find(m => m.distributorId === distributorId);
	
	if (marker && marker.getVisible()) {
		map.setCenter(marker.getPosition());
		map.setZoom(15);
		
		marker.setAnimation(google.maps.Animation.BOUNCE);
		setTimeout(() => {
			marker.setAnimation(null);
		}, 1500);
	}
};

window.resetMapZoom = function(visibleDistributorIds) {
	console.log("resetMapZoom called with:", visibleDistributorIds);
	
	if (!map || markers.length === 0) {
		console.log("No map or no markers");
		return;
	}

	const newBounds = new google.maps.LatLngBounds();
	let hasVisibleMarkers = false;

	if (visibleDistributorIds && visibleDistributorIds.length > 0) {
		markers.forEach((marker) => {
			if (visibleDistributorIds.includes(marker.distributorId)) {
				newBounds.extend(marker.getPosition());
				hasVisibleMarkers = true;
				console.log("Found marker for ID:", marker.distributorId);
			}
		});
	} else {
		markers.forEach((marker) => {
			if (marker.getVisible()) {
				newBounds.extend(marker.getPosition());
				hasVisibleMarkers = true;
			}
		});
	}

	console.log("hasVisibleMarkers:", hasVisibleMarkers);

	if (hasVisibleMarkers) {
		setTimeout(() => {
			map.fitBounds(newBounds);
			
			google.maps.event.addListenerOnce(map, "idle", function () {
				if (map.getZoom() > 15) {
					map.setZoom(15);
				}
			});
		}, 100);
	} else {
		console.log("No visible markers to fit bounds to");
	}
};